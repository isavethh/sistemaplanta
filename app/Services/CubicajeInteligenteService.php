<?php

namespace App\Services;

use App\Models\PedidoAlmacen;
use App\Models\TipoEmpaque;
use App\Models\TipoTransporte;
use App\Models\TamanoVehiculo;
use App\Models\Vehiculo;
use App\Services\PropuestaVehiculosService;
use Illuminate\Support\Facades\Log;

class CubicajeInteligenteService
{
    protected $propuestaVehiculosService;

    public function __construct(PropuestaVehiculosService $propuestaVehiculosService)
    {
        $this->propuestaVehiculosService = $propuestaVehiculosService;
    }

    /**
     * Calcular cubicaje completo para un pedido de almacén
     */
    public function calcularCubicaje(PedidoAlmacen $pedido): array
    {
        $pedido->load('productos');
        $productos = $pedido->productos;

        // Calcular totales
        $pesoTotal = $productos->sum('total_peso') ?: $productos->sum(function($p) {
            return $productos->sum('cantidad') * ($p->peso_unitario ?? 0);
        });

        $volumenTotal = $this->calcularVolumenTotal($productos);

        // Determinar tipo de transporte
        $tipoTransporte = $this->determinarTipoTransporte($productos);

        // Recomendar tipo de empaque
        $recomendacionEmpaque = $this->recomendarEmpaque($productos, $pesoTotal, $volumenTotal);

        // Calcular capacidad de vehículo requerida
        $capacidadRequerida = $this->calcularCapacidadRequerida($pesoTotal, $volumenTotal);

        // Seleccionar el mejor vehículo disponible
        $vehiculoRecomendado = $this->seleccionarMejorVehiculo($pesoTotal, $volumenTotal, $tipoTransporte);

        // Generar visualización de distribución
        $distribucion = $this->generarDistribucion($productos, $recomendacionEmpaque, $capacidadRequerida);

        // Calcular velocidad recomendada
        $velocidadRecomendada = $this->calcularVelocidadRecomendada($productos, $tipoTransporte);

        return [
            'pedido' => $pedido,
            'productos' => $productos,
            'totales' => [
                'peso_kg' => round($pesoTotal, 2),
                'volumen_m3' => round($volumenTotal, 2),
                'cantidad_productos' => $productos->sum('cantidad'),
            ],
            'tipo_transporte' => $tipoTransporte,
            'recomendacion_empaque' => $recomendacionEmpaque,
            'capacidad_requerida' => $capacidadRequerida,
            'vehiculo_recomendado' => $vehiculoRecomendado,
            'distribucion' => $distribucion,
            'velocidad_recomendada' => $velocidadRecomendada,
            'recomendaciones' => $this->generarRecomendaciones($productos, $tipoTransporte, $recomendacionEmpaque),
        ];
    }

    /**
     * Calcular volumen total de productos
     */
    private function calcularVolumenTotal($productos): float
    {
        $volumenTotal = 0;

        foreach ($productos as $producto) {
            // Si tenemos dimensiones del producto, usarlas
            // Si no, estimar basado en peso (densidad promedio: 200 kg/m³ para productos agrícolas)
            $pesoProducto = ($producto->peso_unitario ?? 0) * $producto->cantidad;
            
            // Estimar dimensiones promedio por tipo de producto
            $dimensiones = $this->estimarDimensiones($producto->producto_nombre, $producto->peso_unitario ?? 0);
            
            $volumenUnitario = ($dimensiones['largo'] * $dimensiones['ancho'] * $dimensiones['alto']) / 1000000; // cm³ a m³
            $volumenTotal += $volumenUnitario * $producto->cantidad;
        }

        // Si no hay volumen calculado, estimar desde peso
        if ($volumenTotal == 0) {
            $pesoTotal = $productos->sum(function($p) {
                return ($p->peso_unitario ?? 0) * $p->cantidad;
            });
            $volumenTotal = $pesoTotal / 200; // 200 kg/m³ densidad promedio
        }

        return $volumenTotal;
    }

    /**
     * Estimar dimensiones de un producto basado en su nombre y peso
     */
    private function estimarDimensiones($nombreProducto, $pesoUnitario): array
    {
        $nombre = strtolower($nombreProducto);
        
        // Dimensiones promedio por tipo de producto (en cm)
        $dimensiones = [
            'largo' => 20,
            'ancho' => 15,
            'alto' => 10,
        ];

        // Ajustar según tipo de producto
        if (preg_match('/\b(tomate|cebolla|papa|zanahoria)\b/', $nombre)) {
            $dimensiones = ['largo' => 8, 'ancho' => 8, 'alto' => 8]; // Productos pequeños redondos
        } elseif (preg_match('/\b(lechuga|repollo|col)\b/', $nombre)) {
            $dimensiones = ['largo' => 25, 'ancho' => 25, 'alto' => 15]; // Productos grandes
        } elseif (preg_match('/\b(plátano|banana)\b/', $nombre)) {
            $dimensiones = ['largo' => 20, 'ancho' => 5, 'alto' => 5]; // Productos alargados
        }

        // Ajustar según peso
        if ($pesoUnitario > 1) {
            $factor = sqrt($pesoUnitario);
            $dimensiones['largo'] *= $factor;
            $dimensiones['ancho'] *= $factor;
            $dimensiones['alto'] *= $factor;
        }

        return $dimensiones;
    }

    /**
     * Determinar tipo de transporte requerido
     */
    private function determinarTipoTransporte($productos)
    {
        $requiereRefrigerado = false;
        $requiereCongelado = false;
        $requiereHermetico = false;

        foreach ($productos as $producto) {
            $nombre = strtolower($producto->producto_nombre ?? '');

            // Productos que requieren refrigeración
            if (preg_match('/\b(fresco|perecible|lácteo|leche|yogur|queso|carne|pescado|fruta|verdura|hortaliza)\b/', $nombre)) {
                $requiereRefrigerado = true;
            }

            // Productos que requieren congelación
            if (preg_match('/\b(congelado|helado|nieve|frozen|hielo)\b/', $nombre)) {
                $requiereCongelado = true;
            }
        }

        // Determinar tipo de transporte
        if ($requiereCongelado) {
            return TipoTransporte::where('nombre', 'like', '%Congelado%')->first() 
                ?? TipoTransporte::where('nombre', 'like', '%Frío%')->first();
        } elseif ($requiereRefrigerado) {
            return TipoTransporte::where('nombre', 'like', '%Refrigerado%')->first()
                ?? TipoTransporte::where('nombre', 'like', '%Frío%')->first();
        }

        // Por defecto, estándar
        return TipoTransporte::where('nombre', 'like', '%Estándar%')->first()
            ?? TipoTransporte::where('nombre', 'like', '%Normal%')->first()
            ?? TipoTransporte::first();
    }

    /**
     * Recomendar tipo de empaque de forma inteligente
     */
    private function recomendarEmpaque($productos, $pesoTotal, $volumenTotal): array
    {
        $tiposEmpaque = TipoEmpaque::orderBy('peso_maximo_kg', 'asc')->get();
        $recomendaciones = [];

        foreach ($productos as $producto) {
            $pesoUnitario = $producto->peso_unitario ?? 0;
            $cantidad = $producto->cantidad;
            $pesoProducto = $pesoUnitario * $cantidad;
            
            // Calcular dimensiones del producto
            $dimensiones = $this->estimarDimensiones($producto->producto_nombre, $pesoUnitario);
            $volumenUnitario = ($dimensiones['largo'] * $dimensiones['ancho'] * $dimensiones['alto']) / 1000000; // m³
            $volumenProducto = $volumenUnitario * $cantidad;

            // Buscar el mejor empaque que pueda contener el producto
            $tipoRecomendado = null;
            $cantidadCajas = 1;
            $itemsPorCaja = 1;

            // Calcular cuántos items caben en cada tipo de empaque
            foreach ($tiposEmpaque as $tipo) {
                $capacidadPeso = $tipo->peso_maximo_kg ?? 999999;
                $capacidadVolumen = ($tipo->largo_cm ?? 0) * ($tipo->ancho_cm ?? 0) * ($tipo->alto_cm ?? 0) / 1000000; // m³
                
                // Calcular cuántos items caben por peso
                $itemsPorPeso = $capacidadPeso > 0 ? floor($capacidadPeso / max($pesoUnitario, 0.001)) : 0;
                
                // Calcular cuántos items caben por volumen
                $itemsPorVolumen = $capacidadVolumen > 0 ? floor($capacidadVolumen / max($volumenUnitario, 0.000001)) : 0;
                
                // El límite es el menor de los dos
                $itemsMaximos = min($itemsPorPeso, $itemsPorVolumen);
                
                // Si este empaque puede contener al menos 1 item y es mejor que el anterior
                if ($itemsMaximos >= 1) {
                    // Preferir empaques que mejor aprovechen el espacio (no demasiado grandes)
                    $eficiencia = $itemsMaximos / max($capacidadVolumen, 0.0001);
                    
                    if (!$tipoRecomendado || $eficiencia > ($itemsPorCaja / max(($tipoRecomendado->largo_cm ?? 1) * ($tipoRecomendado->ancho_cm ?? 1) * ($tipoRecomendado->alto_cm ?? 1) / 1000000, 0.0001))) {
                        $tipoRecomendado = $tipo;
                        $itemsPorCaja = min($itemsMaximos, $cantidad); // No más de la cantidad disponible
                    }
                }
            }

            // Si no se encontró empaque, usar el más grande disponible
            if (!$tipoRecomendado && $tiposEmpaque->isNotEmpty()) {
                $tipoRecomendado = $tiposEmpaque->last(); // El más grande
                $itemsPorCaja = $cantidad;
            }

            // Calcular cantidad de cajas necesarias
            if ($tipoRecomendado && $itemsPorCaja > 0) {
                $cantidadCajas = ceil($cantidad / $itemsPorCaja);
            } else {
                $cantidadCajas = 1;
                $itemsPorCaja = $cantidad;
            }

            $recomendaciones[] = [
                'producto' => $producto->producto_nombre,
                'producto_id' => $producto->id ?? null,
                'cantidad_producto' => $cantidad,
                'peso_unitario' => $pesoUnitario,
                'tipo_empaque' => $tipoRecomendado,
                'cantidad_cajas' => $cantidadCajas,
                'items_por_caja' => $itemsPorCaja,
                'dimensiones_caja' => $tipoRecomendado ? [
                    'largo_cm' => $tipoRecomendado->largo_cm ?? 0,
                    'ancho_cm' => $tipoRecomendado->ancho_cm ?? 0,
                    'alto_cm' => $tipoRecomendado->alto_cm ?? 0,
                    'volumen_m3' => (($tipoRecomendado->largo_cm ?? 0) * ($tipoRecomendado->ancho_cm ?? 0) * ($tipoRecomendado->alto_cm ?? 0)) / 1000000,
                ] : null,
                'material' => $tipoRecomendado->icono ?? '📦',
            ];
        }

        return $recomendaciones;
    }

    /**
     * Calcular capacidad requerida del vehículo
     */
    private function calcularCapacidadRequerida($pesoTotal, $volumenTotal): array
    {
        // Agregar margen de seguridad (20%)
        $pesoRequerido = $pesoTotal * 1.2;
        $volumenRequerido = $volumenTotal * 1.2;

        return [
            'peso_kg' => round($pesoRequerido, 2),
            'volumen_m3' => round($volumenRequerido, 2),
            'peso_minimo_kg' => round($pesoTotal, 2),
            'volumen_minimo_m3' => round($volumenTotal, 2),
        ];
    }

    /**
     * Generar visualización de distribución en camión
     */
    private function generarDistribucion($productos, $recomendacionEmpaque, $capacidadRequerida): array
    {
        // Dimensiones estándar de camión (en metros)
        $dimensionesCamion = [
            'largo' => 6.0,  // 6 metros
            'ancho' => 2.4,  // 2.4 metros
            'alto' => 2.5,   // 2.5 metros
        ];

        $volumenCamion = $dimensionesCamion['largo'] * $dimensionesCamion['ancho'] * $dimensionesCamion['alto'];
        $porcentajeUso = ($capacidadRequerida['volumen_m3'] / $volumenCamion) * 100;

        // Distribuir cajas en el camión
        $distribucion = [];
        $posicionX = 0;
        $posicionY = 0;
        $posicionZ = 0;

        foreach ($recomendacionEmpaque as $index => $recomendacion) {
            if ($recomendacion['tipo_empaque']) {
                $largo = ($recomendacion['dimensiones_caja']['largo_cm'] ?? 50) / 100; // convertir a metros
                $ancho = ($recomendacion['dimensiones_caja']['ancho_cm'] ?? 40) / 100;
                $alto = ($recomendacion['dimensiones_caja']['alto_cm'] ?? 30) / 100;

                for ($i = 0; $i < $recomendacion['cantidad_cajas']; $i++) {
                    // Verificar si cabe en la posición actual
                    if ($posicionX + $largo > $dimensionesCamion['largo']) {
                        $posicionX = 0;
                        $posicionY += $ancho;
                    }

                    if ($posicionY + $ancho > $dimensionesCamion['ancho']) {
                        $posicionY = 0;
                        $posicionZ += $alto;
                    }

                    if ($posicionZ + $alto > $dimensionesCamion['alto']) {
                        break; // No cabe más
                    }

                    $distribucion[] = [
                        'caja_numero' => $i + 1,
                        'producto' => $recomendacion['producto'],
                        'posicion' => [
                            'x' => round($posicionX, 2),
                            'y' => round($posicionY, 2),
                            'z' => round($posicionZ, 2),
                        ],
                        'dimensiones' => [
                            'largo' => round($largo, 2),
                            'ancho' => round($ancho, 2),
                            'alto' => round($alto, 2),
                        ],
                    ];

                    $posicionX += $largo;
                }
            }
        }

        return [
            'dimensiones_camion' => $dimensionesCamion,
            'volumen_camion_m3' => round($volumenCamion, 2),
            'porcentaje_uso' => round($porcentajeUso, 1),
            'cajas' => $distribucion,
            'total_cajas' => count($distribucion),
        ];
    }

    /**
     * Calcular velocidad recomendada según tipo de producto
     */
    private function calcularVelocidadRecomendada($productos, $tipoTransporte): array
    {
        $velocidadBase = 60; // km/h base
        $velocidadMaxima = 80; // km/h máxima
        $velocidadMinima = 40; // km/h mínima

        // Ajustar según tipo de transporte
        if ($tipoTransporte) {
            $nombre = strtolower($tipoTransporte->nombre ?? '');
            if (preg_match('/\b(refrigerado|congelado|frío)\b/', $nombre)) {
                $velocidadBase = 50; // Más lento para productos fríos
                $velocidadMaxima = 70;
            }
        }

        // Ajustar según productos frágiles
        foreach ($productos as $producto) {
            $nombre = strtolower($producto->producto_nombre ?? '');
            if (preg_match('/\b(huevo|tomate|fruta|fragil|delicado)\b/', $nombre)) {
                $velocidadBase = max($velocidadBase - 10, $velocidadMinima);
                $velocidadMaxima = max($velocidadMaxima - 10, $velocidadMinima + 10);
            }
        }

        return [
            'velocidad_recomendada_kmh' => $velocidadBase,
            'velocidad_maxima_kmh' => $velocidadMaxima,
            'velocidad_minima_kmh' => $velocidadMinima,
            'razon' => $this->obtenerRazonVelocidad($productos, $tipoTransporte),
        ];
    }

    /**
     * Obtener razón de la velocidad recomendada
     */
    private function obtenerRazonVelocidad($productos, $tipoTransporte): string
    {
        if ($tipoTransporte && preg_match('/\b(refrigerado|congelado)\b/', strtolower($tipoTransporte->nombre ?? ''))) {
            return 'Productos requieren temperatura controlada, velocidad reducida para mantener cadena de frío';
        }

        foreach ($productos as $producto) {
            $nombre = strtolower($producto->producto_nombre ?? '');
            if (preg_match('/\b(huevo|tomate|fruta|fragil)\b/', $nombre)) {
                return 'Productos frágiles detectados, velocidad reducida para evitar daños';
            }
        }

        return 'Velocidad estándar para transporte seguro';
    }

    /**
     * Seleccionar el mejor vehículo disponible para el pedido
     */
    private function seleccionarMejorVehiculo($pesoTotal, $volumenTotal, $tipoTransporte): ?array
    {
        
        // Agregar margen de seguridad (20%)
        $pesoRequerido = $pesoTotal * 1.2;
        $volumenRequerido = $volumenTotal * 1.2;
        
        $query = Vehiculo::with(['tipoTransporte', 'tamanoVehiculo', 'transportista'])
            ->where('disponible', true)
            ->where('estado', 'activo');
        
        // Filtrar por tipo de transporte si es requerido
        if ($tipoTransporte) {
            $query->where('tipo_transporte_id', $tipoTransporte->id);
        }
        
        // Filtrar por capacidad mínima
        $query->where('capacidad_carga', '>=', $pesoRequerido)
              ->where(function($q) use ($volumenRequerido) {
                  $q->where('capacidad_volumen', '>=', $volumenRequerido)
                    ->orWhereNull('capacidad_volumen'); // Si no tiene volumen, solo considerar peso
              });
        
        $vehiculos = $query->get();
        
        if ($vehiculos->isEmpty()) {
            return null;
        }
        
        // Seleccionar el vehículo que mejor se ajuste (menor exceso de capacidad)
        $mejorVehiculo = $vehiculos->sortBy(function($v) use ($pesoRequerido, $volumenRequerido) {
            $excesoPeso = $v->capacidad_carga - $pesoRequerido;
            $excesoVolumen = ($v->capacidad_volumen ?? 999999) - $volumenRequerido;
            
            // Priorizar menor exceso (mejor ajuste)
            return $excesoPeso + ($excesoVolumen * 200); // 1 m³ ≈ 200 kg
        })->first();
        
        if (!$mejorVehiculo) {
            return null;
        }
        
        // Calcular porcentaje de uso
        $porcentajeUsoPeso = ($pesoTotal / $mejorVehiculo->capacidad_carga) * 100;
        $porcentajeUsoVolumen = $mejorVehiculo->capacidad_volumen 
            ? ($volumenTotal / $mejorVehiculo->capacidad_volumen) * 100 
            : 0;
        
        return [
            'vehiculo' => $mejorVehiculo,
            'tipo_transporte' => $mejorVehiculo->tipoTransporte,
            'tamano' => $mejorVehiculo->tamanoVehiculo,
            'capacidad_carga_kg' => round($mejorVehiculo->capacidad_carga, 2),
            'capacidad_volumen_m3' => round($mejorVehiculo->capacidad_volumen ?? 0, 2),
            'peso_asignado_kg' => round($pesoTotal, 2),
            'volumen_asignado_m3' => round($volumenTotal, 2),
            'porcentaje_uso_peso' => round($porcentajeUsoPeso, 1),
            'porcentaje_uso_volumen' => round($porcentajeUsoVolumen, 1),
            'porcentaje_uso' => round(max($porcentajeUsoPeso, $porcentajeUsoVolumen), 1),
            'transportista' => $mejorVehiculo->transportista,
        ];
    }

    /**
     * Generar recomendaciones generales
     */
    private function generarRecomendaciones($productos, $tipoTransporte, $recomendacionEmpaque): array
    {
        $recomendaciones = [];

        // Recomendación de temperatura
        if ($tipoTransporte && preg_match('/\b(refrigerado|congelado)\b/', strtolower($tipoTransporte->nombre ?? ''))) {
            $recomendaciones[] = 'Mantener temperatura controlada durante todo el transporte';
            if (preg_match('/congelado/', strtolower($tipoTransporte->nombre ?? ''))) {
                $recomendaciones[] = 'Temperatura recomendada: -18°C a -20°C';
            } else {
                $recomendaciones[] = 'Temperatura recomendada: 2°C a 8°C';
            }
        }

        // Recomendación de empaque
        $recomendaciones[] = 'Usar empaques recomendados para proteger los productos';
        $recomendaciones[] = 'Verificar que todas las cajas estén bien selladas antes del transporte';

        // Recomendación de manipulación
        foreach ($productos as $producto) {
            $nombre = strtolower($producto->producto_nombre ?? '');
            if (preg_match('/\b(huevo|tomate|fruta)\b/', $nombre)) {
                $recomendaciones[] = 'Manejar con cuidado, productos frágiles detectados';
                break;
            }
        }

        return $recomendaciones;
    }
}

