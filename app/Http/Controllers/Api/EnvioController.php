<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use App\Models\EnvioProducto;
use App\Models\Almacen;
use App\Models\RechazoTransportista;
use App\Services\AlmacenIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EnvioController extends Controller
{
    /**
     * URL de la API Node.js
     */
    protected $nodeApiUrl;

    public function __construct()
    {
        $this->nodeApiUrl = env('NODE_API_URL', 'http://bomberos.dasalas.shop/api');
    }
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
            // Validar que el ID sea numérico
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'ID de envío inválido'
                ], 400, [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }

            $envio = Envio::with(['asignacion.transportista', 'asignacion.vehiculo', 'almacenDestino'])->find($id);

            if (!$envio) {
                return response()->json([
                    'success' => false,
                    'error' => 'Envío no encontrado'
                ], 404, [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }

            if ($envio->estado !== 'asignado') {
                return response()->json([
                    'success' => false,
                    'error' => 'El envío no está en estado asignado. Estado actual: ' . $envio->estado
                ], 400, [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }

            $envio->estado = 'aceptado';
            
            // Guardar firma del transportista
            // Si viene firma_base64 en el request, usarla; si no, generar firma de texto
            $request = request();
            $firmaBase64 = $request->input('firma_base64');
            
            // Log para debugging
            Log::info('Intentando guardar firma del transportista', [
                'envio_id' => $envio->id,
                'envio_codigo' => $envio->codigo,
                'tiene_firma_base64' => !empty($firmaBase64),
                'firma_length' => $firmaBase64 ? strlen($firmaBase64) : 0,
                'firma_preview' => $firmaBase64 ? substr($firmaBase64, 0, 50) : 'N/A',
                'firma_starts_with' => $firmaBase64 ? substr($firmaBase64, 0, 20) : 'N/A',
                'request_keys' => array_keys($request->all()),
                'request_all' => $request->all()
            ]);
            
            if ($firmaBase64 && trim($firmaBase64) !== '') {
                // Limpiar espacios en blanco
                $firmaLimpia = trim($firmaBase64);
                
                // Verificar si es base64 válido (imagen)
                $esBase64Valido = false;
                
                if (strpos($firmaLimpia, 'data:image') === 0) {
                    // Ya tiene el prefijo data:image, verificar que tenga contenido base64
                    $base64Part = substr($firmaLimpia, strpos($firmaLimpia, ',') + 1);
                    if (preg_match('/^[A-Za-z0-9+\/]+=*$/', $base64Part) && strlen($base64Part) > 100) {
                        $esBase64Valido = true;
                    }
                } elseif (preg_match('/^[A-Za-z0-9+\/]+=*$/', $firmaLimpia) && strlen($firmaLimpia) > 100) {
                    // Es base64 puro (sin prefijo), agregar prefijo
                    $esBase64Valido = true;
                    $firmaLimpia = 'data:image/png;base64,' . $firmaLimpia;
                }
                
                if ($esBase64Valido) {
                    // Guardar firma base64 directamente
                    $envio->firma_transportista = $firmaLimpia;
                    Log::info('✅ Firma base64 recibida y guardada', [
                        'envio_id' => $envio->id,
                        'envio_codigo' => $envio->codigo,
                        'firma_length' => strlen($firmaLimpia),
                        'tiene_prefijo' => strpos($firmaLimpia, 'data:image') === 0
                    ]);
                } else {
                    // La firma recibida no es base64 válido
                    Log::error('❌ Firma recibida NO es base64 válido', [
                        'envio_id' => $envio->id,
                        'envio_codigo' => $envio->codigo,
                        'firma_length' => strlen($firmaLimpia),
                        'firma_preview' => substr($firmaLimpia, 0, 100)
                    ]);
                    // NO guardar - dejar null
                    $envio->firma_transportista = null;
                }
            } else {
                // NO generar firma de texto automática - dejar null
                // El usuario DEBE capturar la firma desde la app móvil
                Log::error('❌ NO se recibió firma_base64 en el request - NO se generará texto automático', [
                    'envio_id' => $envio->id,
                    'envio_codigo' => $envio->codigo,
                    'request_keys' => array_keys($request->all()),
                    'request_all' => $request->all()
                ]);
                
                // NO generar texto automático - dejar sin firma
                $envio->firma_transportista = null;
            }
            
            $envio->save();

            // Actualizar fecha de aceptación en la asignación
            if ($envio->asignacion) {
                $envio->asignacion->update([
                    'fecha_aceptacion' => now()
                ]);
            }

            // Notificar a Almacenes que el pedido está "en proceso"
            try {
                $almacenService = new AlmacenIntegrationService();
                $almacenService->notifyEnvioAceptado($envio);
            } catch (\Exception $e) {
                Log::warning("No se pudo notificar aceptación a almacenes: " . $e->getMessage());
                // No fallar la aceptación si la notificación falla
            }

            // Sincronizar con API Node.js (bomberos.dasalas.shop)
            try {
                $this->sincronizarEstadoConNodeJS($envio);
            } catch (\Exception $e) {
                Log::warning("No se pudo sincronizar con Node.js: " . $e->getMessage());
                // No fallar la aceptación si la sincronización falla
            }

            return response()->json([
                'success' => true,
                'message' => 'Envío aceptado correctamente. Firma digital registrada.',
                'envio' => [
                    'id' => $envio->id,
                    'codigo' => $envio->codigo,
                    'estado' => $envio->estado,
                ]
            ], 200, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al aceptar envío', [
                'envio_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error al aceptar envío: ' . $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }
    }

    /**
     * Rechazar asignación de envío
     * POST /api/envios/{id}/rechazar
     */
    public function rechazar(Request $request, $id)
    {
        try {
            // Validar que el ID sea numérico
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'ID de envío inválido'
                ], 400, [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }

            DB::beginTransaction();

            $envio = Envio::with(['asignacion.transportista', 'asignacion.vehiculo'])->find($id);
            
            if (!$envio) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Envío no encontrado'
                ], 404, [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }

            if ($envio->estado !== 'asignado') {
                return response()->json([
                    'error' => 'El envío no está en estado asignado'
                ], 400);
            }

            $motivo = $request->input('motivo', 'Sin motivo especificado');
            $transportista = $envio->asignacion?->transportista;
            
            if (!$transportista) {
                // Intentar obtener transportista a través del vehículo
                $transportista = $envio->asignacion?->vehiculo?->transportista;
            }

            if (!$transportista) {
                return response()->json([
                    'error' => 'No se pudo identificar el transportista'
                ], 400);
            }

            // Guardar rechazo en la tabla de rechazos para productividad
            RechazoTransportista::create([
                'envio_id' => $envio->id,
                'transportista_id' => $transportista->id,
                'codigo_envio' => $envio->codigo,
                'motivo_rechazo' => $motivo,
                'fecha_rechazo' => now(),
            ]);

            // Cambiar a estado rechazado
            $envio->estado = 'rechazado';
            $envio->fecha_rechazo = now();
            $envio->motivo_rechazo = "Rechazado por: {$transportista->name}\nMotivo: {$motivo}\nFecha: " . now()->format('d/m/Y H:i:s');
            $envio->save();

            // NO eliminar la asignación, mantenerla para historial pero marcar como rechazada
            if ($envio->asignacion) {
                $envio->asignacion->update([
                    'estado' => 'rechazado',
                    'fecha_rechazo' => now(),
                    'observaciones' => ($envio->asignacion->observaciones ?? '') . "\n\nRECHAZADO: {$motivo}"
                ]);
            }

            DB::commit();

            Log::info('✅ Envío rechazado por transportista', [
                'envio_id' => $envio->id,
                'codigo' => $envio->codigo,
                'transportista_id' => $transportista->id,
                'transportista_nombre' => $transportista->name,
                'motivo' => $motivo,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Envío rechazado. El administrador será notificado y podrá asignar a otro transportista.',
                'envio' => [
                    'id' => $envio->id,
                    'codigo' => $envio->codigo,
                    'estado' => $envio->estado,
                ]
            ], 200, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al rechazar envío', [
                'envio_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error al rechazar envío: ' . $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }
    }

    /**
     * Iniciar envío (cambiar a en_transito)
     * POST /api/envios/{id}/iniciar
     * Solo puede iniciarse si el envío está en estado 'aceptado'
     */
    public function iniciar($id)
    {
        try {
            $envio = Envio::with(['almacenDestino', 'asignacion.transportista', 'asignacion.vehiculo'])
                ->findOrFail($id);

            // Validar que el envío esté en estado 'aceptado' para poder iniciarlo
            if ($envio->estado !== 'aceptado') {
                return response()->json([
                    'success' => false,
                    'error' => "El envío no puede iniciarse. Estado actual: {$envio->estado}. Debe estar en estado 'aceptado' para poder iniciar.",
                    'estado_actual' => $envio->estado,
                    'estado_requerido' => 'aceptado'
                ], 400, [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }

            // Iniciar el envío (cambiar a en_transito)
            $envio->iniciarTransito();

            Log::info('Envío iniciado por transportista', [
                'envio_id' => $envio->id,
                'codigo' => $envio->codigo,
                'transportista' => $envio->asignacion->transportista->name ?? 'N/A',
                'fecha_inicio' => $envio->fecha_inicio_transito,
            ]);

            // Sincronizar con API Node.js (bomberos.dasalas.shop)
            try {
                $this->sincronizarEstadoConNodeJS($envio);
            } catch (\Exception $e) {
                Log::warning("No se pudo sincronizar con Node.js: " . $e->getMessage());
                // No fallar el inicio si la sincronización falla
            }

            return response()->json([
                'success' => true,
                'message' => 'Envío iniciado correctamente. Ahora está en tránsito.',
                'envio' => [
                    'id' => $envio->id,
                    'codigo' => $envio->codigo,
                    'estado' => $envio->estado,
                    'fecha_inicio_transito' => $envio->fecha_inicio_transito,
                ]
            ], 200, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al iniciar envío', [
                'envio_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al iniciar envío: ' . $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]);
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
            
            // Verificar si ya está entregado
            if ($envio->estado === 'entregado') {
                return response()->json([
                    'success' => true,
                    'message' => 'El envío ya estaba marcado como entregado',
                    'envio' => $envio
                ]);
            }
            
            // Marcar como entregado (operación rápida)
            $envio->marcarEntregado();
            $envio->refresh();

            // Responder INMEDIATAMENTE sin esperar documentos
            $response = response()->json([
                'success' => true,
                'message' => 'Envío marcado como entregado',
                'envio' => $envio
            ]);

            // Generar y enviar documentos EN SEGUNDO PLANO (después de enviar la respuesta)
            // Usar register_shutdown_function para ejecutar después de enviar la respuesta HTTP
            register_shutdown_function(function () use ($envio) {
                try {
                    \Log::info('📄 [EnvioController] Iniciando generación de documentos en segundo plano', [
                        'envio_id' => $envio->id,
                        'codigo' => $envio->codigo,
                    ]);
                    
                    $documentoService = new \App\Services\DocumentoEntregaService(
                        new \App\Services\AlmacenIntegrationService()
                    );
                    $documentoService->generarYEnviarDocumentos($envio);
                } catch (\Exception $e) {
                    \Log::error('Error generando/enviando documentos al marcar como entregado (background)', [
                        'envio_id' => $envio->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            });

            return $response;
        } catch (\Exception $e) {
            \Log::error('Error al marcar como entregado', [
                'envio_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
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

            // Obtener ruta real usando OSRM (Open Source Routing Machine) - GRATIS y sin API key
            $puntos = [];
            
            try {
                \Log::info("🔄 Obteniendo ruta con OSRM desde ({$origenLat}, {$origenLng}) hasta ({$destinoLat}, {$destinoLng})");
                
                // OSRM usa formato [lng, lat] para las coordenadas
                $osrmUrl = "https://router.project-osrm.org/route/v1/driving/{$origenLng},{$origenLat};{$destinoLng},{$destinoLat}?overview=full&geometries=geojson&steps=true&alternatives=false";
                
                $osrmResponse = Http::timeout(15)->get($osrmUrl);
                $osrmData = $osrmResponse->json();
                
                if (isset($osrmData['code']) && $osrmData['code'] === 'Ok' && !empty($osrmData['routes'])) {
                    $osrmRoute = $osrmData['routes'][0];
                    $coordinates = $osrmRoute['geometry']['coordinates'];
                    
                    if (empty($coordinates)) {
                        throw new \Exception("OSRM devolvió ruta sin coordenadas");
                    }
                    
                    \Log::info("✅ OSRM devolvió " . count($coordinates) . " puntos de coordenadas");
                    
                    // Convertir coordenadas GeoJSON [lng, lat] a formato [lat, lng] con velocidad y timestamp
                    $tiempoInicio = now();
                    $tiempoPorPunto = 2; // 2 segundos por punto
                    
                    foreach ($coordinates as $index => $coord) {
                        if (count($coord) >= 2 && is_numeric($coord[0]) && is_numeric($coord[1])) {
                            $puntos[] = [
                                'lat' => round((float)$coord[1], 7), // OSRM devuelve [lng, lat], necesitamos [lat, lng]
                                'lng' => round((float)$coord[0], 7),
                                'velocidad' => 30 + rand(0, 20), // 30-50 km/h
                                'timestamp' => $tiempoInicio->copy()->addSeconds($index * $tiempoPorPunto)->toIso8601String()
                            ];
                        }
                    }
                    
                    if (empty($puntos)) {
                        throw new \Exception("No se pudieron convertir coordenadas de OSRM");
                    }
                    
                    \Log::info("✅ Ruta obtenida de OSRM: " . count($puntos) . " puntos válidos");
                    
                    // Información adicional de la ruta
                    if (isset($osrmRoute['distance']) && isset($osrmRoute['duration'])) {
                        $distanciaKm = round($osrmRoute['distance'] / 1000, 1);
                        $duracionMin = round($osrmRoute['duration'] / 60, 1);
                        \Log::info("📊 Distancia: {$distanciaKm} km, Duración estimada: {$duracionMin} min");
                    }
                } else {
                    $errorMsg = $osrmData['code'] ?? 'unknown';
                    throw new \Exception("OSRM error: " . $errorMsg);
                }
            } catch (\Exception $e) {
                \Log::error("❌ Error al obtener ruta de OSRM: " . $e->getMessage());
                \Log::warning("⚠️ Usando interpolación como último recurso (línea recta)");
                
                // Último fallback: Generar puntos interpolados (línea recta)
                // Aumentar a 100 puntos para que al menos se vea más suave
                for ($i = 0; $i <= 100; $i++) {
                    $lat = $origenLat + ($destinoLat - $origenLat) * ($i / 100);
                    $lng = $origenLng + ($destinoLng - $origenLng) * ($i / 100);
                    $puntos[] = [
                        'lat' => round($lat, 7),
                        'lng' => round($lng, 7),
                        'velocidad' => 30 + rand(0, 20),
                        'timestamp' => now()->addSeconds($i * 30)->toIso8601String()
                    ];
                }
                
                \Log::warning("⚠️ Ruta interpolada generada: " . count($puntos) . " puntos (línea recta)");
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
     * Decodificar polyline de Google Maps
     */
    private function decodePolyline($encoded)
    {
        $points = [];
        $index = 0;
        $len = strlen($encoded);
        $lat = 0;
        $lng = 0;

        while ($index < $len) {
            $b = 0;
            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $shift = 0;
            $result = 0;
            do {
                $b = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = [
                'lat' => $lat * 1e-5,
                'lng' => $lng * 1e-5
            ];
        }

        return $points;
    }

    /**
     * Obtener seguimiento de un envío
     * GET /api/envios/{id}/seguimiento
     */
    public function getSeguimiento($id)
    {
        try {
            $seguimiento = DB::table('seguimiento_envio')
                ->where('envio_id', $id)
                ->orderBy('timestamp', 'asc')
                ->get(['latitud', 'longitud', 'velocidad', 'timestamp']);
            
            if ($seguimiento->isEmpty()) {
                return response()->json([]);
            }
            
            return response()->json($seguimiento->map(function($punto) {
                return [
                    'latitud' => (float) $punto->latitud,
                    'longitud' => (float) $punto->longitud,
                    'velocidad' => (float) ($punto->velocidad ?? 0),
                    'timestamp' => $punto->timestamp
                ];
            })->toArray());
        } catch (\Exception $e) {
            \Log::error("Error obteniendo seguimiento para envío {$id}: " . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener seguimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar estado con API Node.js (bomberos.dasalas.shop)
     */
    private function sincronizarEstadoConNodeJS(Envio $envio)
    {
        try {
            $response = Http::timeout(5)->put("{$this->nodeApiUrl}/envios/{$envio->codigo}/estado", [
                'estado_nombre' => $envio->estado,
                'fecha_inicio_transito' => $envio->fecha_inicio_transito ? $envio->fecha_inicio_transito->toIso8601String() : null,
                'fecha_aceptacion' => $envio->asignacion && $envio->asignacion->fecha_aceptacion 
                    ? $envio->asignacion->fecha_aceptacion->toIso8601String() 
                    : null,
            ]);

            if ($response->successful()) {
                Log::info('Estado de envío sincronizado con Node.js', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                    'estado' => $envio->estado,
                ]);
            } else {
                Log::warning('Error al sincronizar estado con Node.js', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                    'estado' => $envio->estado,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al sincronizar estado con Node.js', [
                'envio_id' => $envio->id,
                'codigo' => $envio->codigo,
                'estado' => $envio->estado,
                'error' => $e->getMessage(),
                'node_api_url' => $this->nodeApiUrl,
            ]);
            // No lanzar excepción, solo loguear el error
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

