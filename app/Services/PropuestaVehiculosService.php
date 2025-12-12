<?php

namespace App\Services;

use App\Models\Envio;
use App\Models\Vehiculo;
use App\Models\TipoTransporte;
use App\Models\TamanoVehiculo;
use Illuminate\Support\Facades\Log;

class PropuestaVehiculosService
{
    /**
     * Calcular qué vehículos se necesitan para un envío
     * basado en las especificaciones de productos y empaquetado
     */
    public function calcularPropuestaVehiculos(Envio $envio): array
    {
        // Asegurar que las relaciones estén cargadas
        $envio->load(['productos.producto', 'productos.tipoEmpaque', 'almacenDestino']);
        $productos = $envio->productos;
        
        // Calcular totales
        $pesoTotal = $productos->sum('total_peso') ?: $productos->sum(function($p) {
            return $p->cantidad * ($p->peso_unitario ?? 0);
        });
        
        $volumenTotal = $productos->sum(function($p) {
            $alto = ($p->alto_producto_cm ?? 0) / 100; // convertir a metros
            $ancho = ($p->ancho_producto_cm ?? 0) / 100;
            $largo = ($p->largo_producto_cm ?? 0) / 100;
            return ($alto * $ancho * $largo) * $p->cantidad;
        });
        
        // Si no hay dimensiones, estimar volumen basado en peso (densidad promedio: 200 kg/m³)
        if ($volumenTotal == 0 && $pesoTotal > 0) {
            $volumenTotal = $pesoTotal / 200; // metros cúbicos
        }
        
        // Determinar tipo de transporte requerido según productos
        $tipoTransporteRequerido = $this->determinarTipoTransporte($productos);
        
        // Obtener vehículos disponibles que cumplan los requisitos
        $vehiculosDisponibles = $this->obtenerVehiculosDisponibles($pesoTotal, $volumenTotal, $tipoTransporteRequerido);
        
        // Calcular cuántos vehículos se necesitan
        $propuesta = $this->calcularCantidadVehiculos($vehiculosDisponibles, $pesoTotal, $volumenTotal);
        
        return [
            'envio' => $envio,
            'productos' => $productos,
            'totales' => [
                'peso_kg' => round($pesoTotal, 2),
                'volumen_m3' => round($volumenTotal, 2),
                'cantidad_productos' => $productos->sum('cantidad'),
            ],
            'tipo_transporte_requerido' => $tipoTransporteRequerido,
            'vehiculos_propuestos' => $propuesta,
            'fecha_generacion' => now(),
        ];
    }
    
    /**
     * Determinar el tipo de transporte requerido según los productos
     */
    private function determinarTipoTransporte($productos)
    {
        // Analizar productos para determinar si requieren transporte especial
        $requiereRefrigerado = false;
        $requiereCongelado = false;
        $requiereHermetico = false;
        
        foreach ($productos as $producto) {
            $productoModel = $producto->producto;
            $tipoEmpaque = $producto->tipoEmpaque;
            
            // Verificar si el producto requiere condiciones especiales
            if ($productoModel) {
                $nombre = strtolower($productoModel->nombre);
                $descripcion = strtolower($productoModel->descripcion ?? '');
                
                // Productos que requieren refrigeración
                if (preg_match('/\b(fresco|perecible|lácteo|leche|yogur|queso|carne|pescado|fruta|verdura)\b/', $nombre . ' ' . $descripcion)) {
                    $requiereRefrigerado = true;
                }
                
                // Productos que requieren congelación
                if (preg_match('/\b(congelado|helado|nieve|frozen)\b/', $nombre . ' ' . $descripcion)) {
                    $requiereCongelado = true;
                }
            }
            
            // Verificar tipo de empaque
            if ($tipoEmpaque) {
                $nombreEmpaque = strtolower($tipoEmpaque->nombre ?? '');
                if (preg_match('/\b(hermético|sellado|vacío)\b/', $nombreEmpaque)) {
                    $requiereHermetico = true;
                }
            }
        }
        
        // Determinar tipo de transporte
        if ($requiereCongelado) {
            return TipoTransporte::where('nombre', 'Congelado')->first();
        } elseif ($requiereRefrigerado) {
            return TipoTransporte::where('nombre', 'Refrigerado')->first();
        } elseif ($requiereHermetico) {
            return TipoTransporte::where('nombre', 'Hermético')->first();
        }
        
        // Por defecto, estándar
        return TipoTransporte::where('nombre', 'Estándar')->first() 
            ?? TipoTransporte::first();
    }
    
    /**
     * Obtener vehículos disponibles que cumplan los requisitos
     */
    private function obtenerVehiculosDisponibles($pesoTotal, $volumenTotal, $tipoTransporte)
    {
        $query = Vehiculo::with(['tipoTransporte', 'tamanoVehiculo', 'transportista'])
            ->where('disponible', true)
            ->where('estado', 'activo');
        
        // Filtrar por tipo de transporte si es requerido
        if ($tipoTransporte) {
            $query->where('tipo_transporte_id', $tipoTransporte->id);
        }
        
        // Filtrar por capacidad mínima
        $query->where('capacidad_carga', '>=', $pesoTotal * 0.5); // Al menos 50% de la carga
        
        $vehiculos = $query->get();
        
        // Ordenar por capacidad (más eficiente primero)
        return $vehiculos->sortBy(function($v) use ($pesoTotal, $volumenTotal) {
            // Priorizar vehículos que mejor se ajusten a la carga
            $excesoPeso = $v->capacidad_carga - $pesoTotal;
            $excesoVolumen = ($v->capacidad_volumen ?? 0) - $volumenTotal;
            
            // Penalizar exceso excesivo
            if ($excesoPeso < 0 || $excesoVolumen < 0) {
                return 999999; // No puede transportar
            }
            
            // Priorizar menor exceso (mejor ajuste)
            return $excesoPeso + ($excesoVolumen * 200); // 1 m³ ≈ 200 kg
        })->values();
    }
    
    /**
     * Calcular cuántos vehículos se necesitan
     */
    private function calcularCantidadVehiculos($vehiculos, $pesoTotal, $volumenTotal)
    {
        if ($vehiculos->isEmpty()) {
            return [];
        }
        
        $propuesta = [];
        $pesoRestante = $pesoTotal;
        $volumenRestante = $volumenTotal;
        
        foreach ($vehiculos as $vehiculo) {
            if ($pesoRestante <= 0 && $volumenRestante <= 0) {
                break;
            }
            
            $capacidadPeso = $vehiculo->capacidad_carga ?? 0;
            $capacidadVolumen = $vehiculo->capacidad_volumen ?? 999999;
            
            // Calcular cuánto puede llevar este vehículo
            $pesoAsignado = min($pesoRestante, $capacidadPeso);
            $volumenAsignado = min($volumenRestante, $capacidadVolumen);
            
            // Verificar que el vehículo puede llevar al menos algo
            if ($pesoAsignado > 0 || $volumenAsignado > 0) {
                $propuesta[] = [
                    'vehiculo' => $vehiculo,
                    'peso_asignado_kg' => round($pesoAsignado, 2),
                    'volumen_asignado_m3' => round($volumenAsignado, 2),
                    'porcentaje_uso' => round(($pesoAsignado / $capacidadPeso) * 100, 1),
                    'tipo_transporte' => $vehiculo->tipoTransporte,
                    'tamano' => $vehiculo->tamanoVehiculo,
                ];
                
                $pesoRestante -= $pesoAsignado;
                $volumenRestante -= $volumenAsignado;
            }
        }
        
        // Si aún queda carga sin asignar, agregar un vehículo adicional del mismo tipo
        if ($pesoRestante > 0 || $volumenRestante > 0) {
            $ultimoVehiculo = $vehiculos->first();
            if ($ultimoVehiculo) {
                $propuesta[] = [
                    'vehiculo' => $ultimoVehiculo,
                    'peso_asignado_kg' => round($pesoRestante, 2),
                    'volumen_asignado_m3' => round($volumenRestante, 2),
                    'porcentaje_uso' => round(($pesoRestante / ($ultimoVehiculo->capacidad_carga ?? 1)) * 100, 1),
                    'tipo_transporte' => $ultimoVehiculo->tipoTransporte,
                    'tamano' => $ultimoVehiculo->tamanoVehiculo,
                ];
            }
        }
        
        return $propuesta;
    }
}

