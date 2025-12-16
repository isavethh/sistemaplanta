<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incidente;
use App\Models\Envio;
use App\Services\AlmacenIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IncidenteController extends Controller
{
    /**
     * Reportar un incidente durante el trayecto
     * POST /api/envios/{envioId}/incidentes
     */
    public function reportar(Request $request, $envioId)
    {
        try {
            $validated = $request->validate([
                'tipo_incidente' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'accion' => 'required|in:cancelar,continuar',
                'foto_base64' => 'nullable|string',
                'ubicacion_lat' => 'nullable|numeric',
                'ubicacion_lng' => 'nullable|numeric',
            ], [
                'tipo_incidente.required' => 'El tipo de incidente es requerido',
                'descripcion.required' => 'La descripción es requerida',
                'accion.required' => 'La acción es requerida',
                'accion.in' => 'La acción debe ser "cancelar" o "continuar"',
            ]);

            $envio = Envio::with(['asignacion.transportista', 'asignacion.vehiculo.transportista', 'almacenDestino'])->findOrFail($envioId);
            
            Log::info('📝 Reportando incidente', [
                'envio_id' => $envioId,
                'envio_codigo' => $envio->codigo,
                'envio_estado' => $envio->estado,
                'tiene_asignacion' => $envio->asignacion !== null,
                'tiene_transportista' => $envio->asignacion?->transportista !== null,
            ]);

            // Verificar que el envío esté en tránsito o asignado/aceptado (permitir reportar antes de iniciar también)
            if (!in_array($envio->estado, ['en_transito', 'asignado', 'aceptado'])) {
                Log::warning('Intento de reportar incidente en envío no válido', [
                    'envio_id' => $envioId,
                    'estado' => $envio->estado,
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Solo se pueden reportar incidentes cuando el envío está en tránsito, asignado o aceptado. Estado actual: ' . $envio->estado
                ], 400);
            }

            // Obtener transportista desde la asignación del envío (no requiere autenticación)
            $transportista = $envio->asignacion?->transportista;
            
            // Si no está en asignación, intentar obtenerlo desde el vehículo
            if (!$transportista && $envio->asignacion?->vehiculo) {
                $transportista = $envio->asignacion->vehiculo->transportista;
            }
            
            // Si aún no se encuentra, intentar obtenerlo desde Auth (por si acaso hay autenticación)
            if (!$transportista) {
                $transportista = Auth::user();
            }
            
            if (!$transportista) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudo identificar el transportista del envío'
                ], 400);
            }

            DB::beginTransaction();

            // Guardar foto si existe
            $fotoUrl = null;
            if (!empty($validated['foto_base64'])) {
                try {
                    $directorio = "incidentes/{$envioId}";
                    
                    // Limpiar el prefijo data:image si existe
                    $base64Image = $validated['foto_base64'];
                    $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
                    
                    $nombreArchivo = 'incidente_' . time() . '_' . uniqid() . '.jpg';
                    $fotoContent = base64_decode($base64Image, true);
                    
                    if ($fotoContent !== false && strlen($fotoContent) > 0) {
                        // Asegurar que el directorio existe
                        Storage::disk('public')->makeDirectory($directorio, 0755, true);
                        
                        // Usar storage/app/public para que sea accesible vía web
                        $rutaCompleta = "{$directorio}/{$nombreArchivo}";
                        $guardado = Storage::disk('public')->put($rutaCompleta, $fotoContent);
                        
                        if ($guardado) {
                            $rutaFisica = Storage::disk('public')->path($rutaCompleta);
                            
                            // Verificar que el archivo realmente existe
                            if (file_exists($rutaFisica)) {
                                // Establecer permisos del archivo (solo en sistemas Unix/Linux)
                                @chmod($rutaFisica, 0644);
                                
                                // Verificar permisos después de establecerlos
                                $permisos = fileperms($rutaFisica);
                                $permisosOctales = substr(sprintf('%o', $permisos), -4);
                                
                                // Guardar solo la ruta relativa, sin "storage/"
                                $fotoUrl = $rutaCompleta;
                                Log::info('✅ Foto de incidente guardada correctamente', [
                                    'envio_id' => $envioId,
                                    'ruta' => $fotoUrl,
                                    'ruta_completa' => $rutaFisica,
                                    'url_publica' => asset('storage/' . $fotoUrl),
                                    'tamaño' => strlen($fotoContent),
                                    'tamaño_archivo' => filesize($rutaFisica),
                                    'permisos' => $permisosOctales,
                                    'existe' => true,
                                ]);
                            } else {
                                Log::error('❌ Error: Archivo no encontrado después de guardar', [
                                    'envio_id' => $envioId,
                                    'ruta_intentada' => $rutaCompleta,
                                    'ruta_fisica' => $rutaFisica,
                                ]);
                                $fotoUrl = null; // No guardar URL si el archivo no existe
                            }
                        } else {
                            Log::warning('Error: No se pudo guardar la foto de incidente', [
                                'envio_id' => $envioId,
                                'ruta_intentada' => $rutaCompleta,
                            ]);
                        }
                    } else {
                        Log::warning('Error decodificando foto de incidente (base64 inválido)', [
                            'envio_id' => $envioId,
                            'base64_length' => strlen($validated['foto_base64']),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error guardando foto de incidente', [
                        'envio_id' => $envioId,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
            }

            // Crear el incidente
            $incidente = Incidente::create([
                'envio_id' => $envioId,
                'transportista_id' => $transportista->id,
                'tipo_incidente' => $validated['tipo_incidente'],
                'descripcion' => $validated['descripcion'],
                'foto_url' => $fotoUrl,
                'accion' => $validated['accion'],
                'estado' => 'pendiente',
                'ubicacion_lat' => $validated['ubicacion_lat'] ?? null,
                'ubicacion_lng' => $validated['ubicacion_lng'] ?? null,
                'fecha_reporte' => now(),
                'notificado_admin' => false, // Se marcará como notificado cuando el admin lo vea
            ]);

            // Si la acción es cancelar, marcar el envío como cancelado y generar PDF
            if ($validated['accion'] === 'cancelar') {
                $envio->estado = 'cancelado';
                $envio->observaciones = ($envio->observaciones ?? '') . "\n\n[INCIDENTE] Cancelado por incidente: " . $validated['descripcion'];
                
                // Generar PDF de cancelación
                try {
                    $cancelacionPdfPath = $this->generarPdfCancelacion($envio, $incidente, $validated);
                    if ($cancelacionPdfPath) {
                        $envio->cancelacion_pdf_path = $cancelacionPdfPath;
                    }
                } catch (\Exception $e) {
                    Log::warning('Error generando PDF de cancelación (no crítico)', [
                        'envio_id' => $envioId,
                        'error' => $e->getMessage(),
                    ]);
                }
                
                $envio->save();
            } else {
                // Si continúa, solo agregar nota en observaciones
                $envio->observaciones = ($envio->observaciones ?? '') . "\n\n[INCIDENTE] Incidente reportado pero envío continúa: " . $validated['descripcion'];
                $envio->save();
            }

            DB::commit();

            // Notificar al admin de plantaCruds (log por ahora, se puede mejorar con notificaciones)
            Log::warning('🚨 INCIDENTE REPORTADO', [
                'incidente_id' => $incidente->id,
                'envio_id' => $envioId,
                'envio_codigo' => $envio->codigo,
                'transportista' => $transportista->name,
                'tipo' => $validated['tipo_incidente'],
                'accion' => $validated['accion'],
                'descripcion' => $validated['descripcion'],
            ]);

            // Cargar relaciones necesarias
            $incidente->load('transportista');
            $envio->load('almacenDestino');

            // Intentar notificar al almacén, pero no fallar si hay error
            try {
                $this->notificarAlmacen($incidente, $envio);
            } catch (\Exception $e) {
                // Log del error pero no afectar la respuesta
                Log::error('Error al notificar almacén (no crítico)', [
                    'incidente_id' => $incidente->id,
                    'envio_id' => $envioId,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $validated['accion'] === 'cancelar' 
                    ? 'Incidente reportado y envío cancelado' 
                    : 'Incidente reportado, envío continúa',
                'data' => [
                    'incidente_id' => $incidente->id,
                    'envio_estado' => $envio->estado,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Error de validación al reportar incidente', [
                'envio_id' => $envioId,
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al reportar incidente', [
                'envio_id' => $envioId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al reportar incidente: ' . $e->getMessage(),
                'details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    /**
     * Notificar al almacén destino sobre el incidente
     */
    private function notificarAlmacen(Incidente $incidente, Envio $envio)
    {
        try {
            // Usar reflexión para acceder al método privado o crear una instancia del servicio
            $almacenService = new AlmacenIntegrationService();
            
            // Intentar obtener pedido_id desde las observaciones del envío
            $pedidoAlmacenId = null;
            $observaciones = $envio->observaciones ?? '';
            
            // Buscar patrones comunes en observaciones
            if (preg_match('/pedido[_\s]*almacen[_\s]*id[:\s]*(\d+)/i', $observaciones, $matches)) {
                $pedidoAlmacenId = (int) $matches[1];
            } elseif (preg_match('/pedido[_\s]*id[:\s]*(\d+)/i', $observaciones, $matches)) {
                $pedidoAlmacenId = (int) $matches[1];
            }
            
            // Si no se encuentra en observaciones, buscar por código de envío en la API
            if (!$pedidoAlmacenId && $envio->codigo) {
                try {
                    $apiUrl = config('services.almacen.api_url', env('ALMACEN_API_URL', 'http://localhost:8002/api'));
                    $response = Http::timeout(5)->get("{$apiUrl}/pedidos/buscar-por-envio", [
                        'envio_codigo' => $envio->codigo,
                        'envio_id' => $envio->id,
                    ]);
                    
                    if ($response->successful() && $response->json('success')) {
                        $pedidoAlmacenId = $response->json('data.id');
                    }
                } catch (\Exception $e) {
                    Log::debug("No se pudo buscar pedido por código de envío: " . $e->getMessage());
                }
            }

            if (!$pedidoAlmacenId) {
                Log::info('No se puede notificar incidente al almacén: pedido_id no encontrado', [
                    'envio_id' => $envio->id,
                ]);
                return false;
            }

            $apiUrl = config('services.almacen.api_url', env('ALMACEN_API_URL', 'http://localhost:8002/api'));
            $endpoint = "{$apiUrl}/pedidos/{$pedidoAlmacenId}/incidente";

            // Leer foto si existe
            $fotoBase64 = null;
            if ($incidente->foto_url) {
                try {
                    // La foto está guardada en storage/app/public/incidentes/...
                    if (Storage::disk('public')->exists($incidente->foto_url)) {
                        $fotoContent = Storage::disk('public')->get($incidente->foto_url);
                        $fotoBase64 = base64_encode($fotoContent);
                        Log::info('✅ Foto leída para enviar a almacén', [
                            'incidente_id' => $incidente->id,
                            'tamaño_base64' => strlen($fotoBase64),
                            'tamaño_original' => strlen($fotoContent),
                        ]);
                    } else {
                        Log::warning('Foto no encontrada en storage', [
                            'incidente_id' => $incidente->id,
                            'foto_url' => $incidente->foto_url,
                            'existe_public' => Storage::disk('public')->exists($incidente->foto_url),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Error leyendo foto de incidente para enviar a almacén', [
                        'incidente_id' => $incidente->id,
                        'foto_url' => $incidente->foto_url,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
            }

            $data = [
                'envio_id' => $envio->id,
                'envio_codigo' => $envio->codigo,
                'incidente_id' => $incidente->id,
                'tipo_incidente' => $incidente->tipo_incidente,
                'descripcion' => $incidente->descripcion,
                'accion' => $incidente->accion,
                'transportista' => [
                    'id' => $incidente->transportista->id ?? null,
                    'nombre' => $incidente->transportista->name ?? null,
                ],
                'fecha_reporte' => $incidente->fecha_reporte->toIso8601String(),
                'ubicacion' => [
                    'lat' => $incidente->ubicacion_lat,
                    'lng' => $incidente->ubicacion_lng,
                ],
                'foto_base64' => $fotoBase64, // Incluir foto en base64
            ];

            $response = Http::timeout(10)->post($endpoint, $data);

            if ($response->successful()) {
                $incidente->notificado_almacen = true;
                $incidente->save();
                
                Log::info('✅ Incidente notificado al almacén', [
                    'incidente_id' => $incidente->id,
                    'pedido_id' => $pedidoAlmacenId,
                ]);
                return true;
            } else {
                Log::warning('Error al notificar incidente al almacén', [
                    'incidente_id' => $incidente->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error al notificar incidente al almacén', [
                'incidente_id' => $incidente->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Listar incidentes
     * GET /api/incidentes
     */
    public function index(Request $request)
    {
        try {
            $query = Incidente::with(['envio.almacenDestino', 'transportista']);
            
            // Filtrar por transportista si se proporciona
            if ($request->has('transportista_id')) {
                $query->where('transportista_id', $request->transportista_id);
            }
            
            // Filtrar por estado si se proporciona
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }
            
            // Ordenar por fecha más reciente primero
            $incidentes = $query->orderBy('fecha_reporte', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->get()
                                ->map(function($incidente) {
                                    // Verificar si el archivo existe antes de generar la URL
                                    $fotoUrl = null;
                                    if ($incidente->foto_url) {
                                        $rutaCompleta = Storage::disk('public')->path($incidente->foto_url);
                                        if (file_exists($rutaCompleta)) {
                                            $fotoUrl = asset('storage/' . $incidente->foto_url);
                                        } else {
                                            Log::warning('⚠️ Archivo de incidente no encontrado', [
                                                'incidente_id' => $incidente->id,
                                                'foto_url' => $incidente->foto_url,
                                                'ruta_esperada' => $rutaCompleta,
                                            ]);
                                        }
                                    }
                                    
                                    return [
                                        'id' => $incidente->id,
                                        'envio_id' => $incidente->envio_id,
                                        'envio_codigo' => $incidente->envio->codigo ?? null,
                                        'transportista_id' => $incidente->transportista_id,
                                        'transportista_nombre' => $incidente->transportista->name ?? null,
                                        'tipo_incidente' => $incidente->tipo_incidente,
                                        'descripcion' => $incidente->descripcion,
                                        'foto_url' => $fotoUrl,
                                        'accion' => $incidente->accion,
                                        'estado' => $incidente->estado,
                                        'ubicacion_lat' => $incidente->ubicacion_lat,
                                        'ubicacion_lng' => $incidente->ubicacion_lng,
                                        'fecha_reporte' => $incidente->fecha_reporte,
                                        'created_at' => $incidente->created_at,
                                        'almacen_nombre' => $incidente->envio->almacenDestino->nombre ?? null,
                                        'respuesta' => $incidente->notas_resolucion,
                                    ];
                                });
            
            return response()->json($incidentes);
        } catch (\Exception $e) {
            Log::error('Error al listar incidentes', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al listar incidentes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener un incidente por ID
     * GET /api/incidentes/{id}
     */
    public function show($id)
    {
        try {
            $incidente = Incidente::with(['envio.almacenDestino', 'transportista'])->findOrFail($id);
            
            // Verificar si el archivo existe antes de generar la URL
            $fotoUrl = null;
            if ($incidente->foto_url) {
                $rutaCompleta = Storage::disk('public')->path($incidente->foto_url);
                if (file_exists($rutaCompleta)) {
                    $fotoUrl = asset('storage/' . $incidente->foto_url);
                } else {
                    Log::warning('⚠️ Archivo de incidente no encontrado', [
                        'incidente_id' => $incidente->id,
                        'foto_url' => $incidente->foto_url,
                        'ruta_esperada' => $rutaCompleta,
                    ]);
                }
            }
            
            return response()->json([
                'id' => $incidente->id,
                'envio_id' => $incidente->envio_id,
                'envio_codigo' => $incidente->envio->codigo ?? null,
                'transportista_id' => $incidente->transportista_id,
                'transportista_nombre' => $incidente->transportista->name ?? null,
                'tipo_incidente' => $incidente->tipo_incidente,
                'descripcion' => $incidente->descripcion,
                'foto_url' => $fotoUrl,
                'accion' => $incidente->accion,
                'estado' => $incidente->estado,
                'ubicacion_lat' => $incidente->ubicacion_lat,
                'ubicacion_lng' => $incidente->ubicacion_lng,
                'fecha_reporte' => $incidente->fecha_reporte,
                'created_at' => $incidente->created_at,
                'almacen_nombre' => $incidente->envio->almacenDestino->nombre ?? null,
                'respuesta' => $incidente->respuesta,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Incidente no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener incidente', [
                'incidente_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener incidente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar PDF de cancelación del envío
     */
    private function generarPdfCancelacion(Envio $envio, Incidente $incidente, array $datos): ?string
    {
        try {
            $envio->load(['almacenDestino', 'asignacion.transportista', 'pedidoAlmacen']);
            
            // Obtener planta
            $planta = \App\Models\Almacen::where('es_planta', true)->first();
            
            // Generar PDF usando una vista simple
            $html = view('reportes.pdf.cancelacion-envio', [
                'envio' => $envio,
                'incidente' => $incidente,
                'planta' => $planta,
                'datos' => $datos,
            ])->render();
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('a4', 'portrait');
            
            // Guardar PDF
            $nombreArchivo = 'cancelacion-envio-' . ($envio->codigo ?? $envio->id) . '-' . time() . '.pdf';
            $directorio = 'cancelaciones';
            $rutaCompleta = "{$directorio}/{$nombreArchivo}";
            
            Storage::disk('public')->put($rutaCompleta, $pdf->output());
            
            Log::info('PDF de cancelación generado', [
                'envio_id' => $envio->id,
                'ruta' => $rutaCompleta,
            ]);
            
            return $rutaCompleta;
        } catch (\Exception $e) {
            Log::error('Error generando PDF de cancelación', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
