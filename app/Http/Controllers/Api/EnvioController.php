<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use App\Models\EnvioProducto;
use App\Models\Almacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnvioController extends Controller
{
    /**
     * Obtener envío por ID con todos sus detalles
     * GET /api/envios/{id}
     */
    public function show($id)
    {
        try {
            $envio = Envio::with(['productos', 'almacenDestino', 'asignacion.vehiculo'])
                ->findOrFail($id);

            // Obtener coordenadas del almacén destino (están en la tabla almacenes)
            $almacen = Almacen::find($envio->almacen_destino_id);
            
            // Obtener planta (origen)
            $planta = Almacen::where('es_planta', true)->first();
            
            $response = [
                'id' => $envio->id,
                'codigo' => $envio->codigo,
                'estado' => $envio->estado,
                'fecha_creacion' => $envio->fecha_creacion,
                'fecha_estimada_entrega' => $envio->fecha_estimada_entrega,
                'hora_estimada' => $envio->hora_estimada,
                'fecha_asignacion' => $envio->fecha_asignacion,
                'fecha_inicio_transito' => $envio->fecha_inicio_transito,
                'fecha_entrega' => $envio->fecha_entrega,
                'total_cantidad' => $envio->total_cantidad,
                'total_peso' => $envio->total_peso,
                'total_precio' => $envio->total_precio,
                'observaciones' => $envio->observaciones,
                'almacen_nombre' => $almacen->nombre ?? null,
                'direccion_completa' => $almacen->direccion_completa ?? null,
                'latitud' => $almacen->latitud ?? null,
                'longitud' => $almacen->longitud ?? null,
                'origen_lat' => $planta->latitud ?? -17.7833,
                'origen_lng' => $planta->longitud ?? -63.1821,
                'origen_direccion' => $planta->direccion_completa ?? 'Planta Principal',
                'productos' => $envio->productos->map(function($p) {
                    return [
                        'producto_nombre' => $p->producto_nombre,
                        'cantidad' => $p->cantidad,
                        'peso_unitario' => $p->peso_unitario,
                        'precio_unitario' => $p->precio_unitario,
                        'total_peso' => $p->total_peso,
                        'total_precio' => $p->total_precio,
                    ];
                }),
                'qr_code' => 'data:image/png;base64,' . base64_encode($this->generarQR($envio->codigo))
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener envío: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aceptar asignación de envío (transportista acepta)
     * POST /api/envios/{id}/aceptar
     */
    public function aceptar($id)
    {
        try {
            $envio = Envio::findOrFail($id);

            if ($envio->estado !== 'asignado') {
                return response()->json([
                    'error' => 'El envío no está en estado asignado'
                ], 400);
            }

            $envio->estado = 'aceptado';
            $envio->save();

            // Actualizar fecha de aceptación en la asignación
            if ($envio->asignacion) {
                $envio->asignacion->update([
                    'fecha_aceptacion' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Envío aceptado correctamente',
                'envio' => $envio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al aceptar envío: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar asignación de envío
     * POST /api/envios/{id}/rechazar
     */
    public function rechazar(Request $request, $id)
    {
        try {
            $envio = Envio::findOrFail($id);

            // Volver a estado pendiente
            $envio->estado = 'pendiente';
            $envio->save();

            // Eliminar asignación
            if ($envio->asignacion) {
                $envio->asignacion->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Asignación rechazada. El envío volverá a estar disponible.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al rechazar envío: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Iniciar envío (cambiar a en_transito)
     * POST /api/envios/{id}/iniciar
     */
    public function iniciar($id)
    {
        try {
            $envio = Envio::findOrFail($id);
            $envio->iniciarTransito();

            return response()->json([
                'success' => true,
                'message' => 'Envío iniciado correctamente',
                'envio' => $envio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al iniciar envío: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar envío como entregado
     * POST /api/envios/{id}/entregado
     */
    public function marcarEntregado($id)
    {
        try {
            $envio = Envio::findOrFail($id);
            $envio->marcarEntregado();

            return response()->json([
                'success' => true,
                'message' => 'Envío marcado como entregado',
                'envio' => $envio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al marcar como entregado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simular movimiento (para pruebas)
     * POST /api/envios/{id}/simular-movimiento
     */
    public function simularMovimiento($id)
    {
        try {
            $envio = Envio::with('almacenDestino')->findOrFail($id);

            // Obtener planta (origen) - coordenadas están en tabla almacenes
            $planta = Almacen::where('es_planta', true)->first();
            $origenLat = $planta->latitud ?? -17.7833;
            $origenLng = $planta->longitud ?? -63.1821;

            // Coordenadas de destino (almacén) - también en tabla almacenes
            $destinoLat = $envio->almacenDestino->latitud ?? -17.7892;
            $destinoLng = $envio->almacenDestino->longitud ?? -63.1751;

            // Generar 20 puntos intermedios para animación más suave
            $puntos = [];
            for ($i = 0; $i <= 20; $i++) {
                $lat = $origenLat + ($destinoLat - $origenLat) * ($i / 20);
                $lng = $origenLng + ($destinoLng - $origenLng) * ($i / 20);
                $puntos[] = [
                    'lat' => round($lat, 7),
                    'lng' => round($lng, 7),
                    'velocidad' => 30 + rand(0, 20), // 30-50 km/h
                    'timestamp' => now()->addSeconds($i * 30)->toIso8601String() // Cada 30 segundos
                ];
            }

            // Cambiar estado a en_transito si no lo está
            if ($envio->estado !== 'en_transito') {
                $envio->iniciarTransito();
            }

            // Guardar puntos en tabla seguimiento_envio si existe
            try {
                // Limpiar seguimiento anterior
                DB::table('seguimiento_envio')->where('envio_id', $envio->id)->delete();
                
                foreach ($puntos as $punto) {
                    DB::table('seguimiento_envio')->insert([
                        'envio_id' => $envio->id,
                        'latitud' => $punto['lat'],
                        'longitud' => $punto['lng'],
                        'velocidad' => $punto['velocidad'],
                        'timestamp' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("No se pudieron guardar puntos en seguimiento_envio: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Simulación de ruta creada correctamente',
                'puntos' => $puntos,
                'origen' => ['lat' => $origenLat, 'lng' => $origenLng, 'direccion' => $planta->direccion_completa ?? 'Planta'],
                'destino' => ['lat' => $destinoLat, 'lng' => $destinoLng, 'direccion' => $envio->almacenDestino->direccion_completa ?? $envio->almacenDestino->nombre]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al simular movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener seguimiento de un envío
     * GET /api/envios/{id}/seguimiento
     */
    public function getSeguimiento($id)
    {
        try {
            // Por ahora retornamos array vacío ya que no tenemos tabla de seguimiento
            // En producción esto consultaría la tabla seguimiento_envio
            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener seguimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar código QR simple
     */
    private function generarQR($codigo)
    {
        // Generar un QR simple (en producción usarías una librería como SimpleSoftwareIO/simple-qrcode)
        // Por ahora retornamos un placeholder
        return '';
    }
}

