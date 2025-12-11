<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Envio;
use App\Models\EnvioAsignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportistaController extends Controller
{
    /**
     * Obtener lista de transportistas para login
     * GET /api/transportistas-login
     */
    public function getTransportistasLogin()
    {
        try {
            // Buscar por tipo O por rol (Spatie)
            $transportistas = User::where(function($query) {
                    $query->where('tipo', 'transportista')
                          ->orWhere('role', 'transportista');
                })
                ->select('id', 'name as nombre', 'email', 'telefono', 'licencia', 'tipo', 'role')
                ->orderBy('name')
                ->get();

            \Log::info("📱 API: Transportistas encontrados para login: " . $transportistas->count());

            return response()->json([
                'success' => true,
                'data' => $transportistas,
                'total' => $transportistas->count()
            ]);
        } catch (\Exception $e) {
            \Log::error("❌ Error al obtener transportistas: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener transportistas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login simplificado de transportista
     * POST /api/login-transportista
     */
    public function loginTransportista(Request $request)
    {
        try {
            $request->validate([
                'transportista_id' => 'required|integer'
            ]);

            // Buscar por tipo O por rol (Spatie)
            $transportista = User::where('id', $request->transportista_id)
                ->where(function($query) {
                    $query->where('tipo', 'transportista')
                          ->orWhere('role', 'transportista');
                })
                ->first();

            if (!$transportista) {
                \Log::warning("⚠️ Transportista no encontrado con ID: {$request->transportista_id}");
                return response()->json([
                    'success' => false,
                    'error' => 'Transportista no encontrado'
                ], 404);
            }

            \Log::info("✅ Login exitoso para transportista: {$transportista->name} (ID: {$transportista->id})");

            return response()->json([
                'success' => true,
                'data' => [
                    'transportista' => [
                        'id' => $transportista->id,
                        'nombre' => $transportista->name,
                        'email' => $transportista->email,
                        'telefono' => $transportista->telefono,
                        'licencia' => $transportista->licencia,
                        'disponible' => $transportista->disponible ?? true,
                        'tipo' => 'transportista'
                    ],
                    'token' => 'transportista-token-' . $transportista->id
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error("❌ Error en login: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error en login: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener envíos asignados a un transportista
     * GET /api/transportista/{id}/envios
     */
    public function getEnviosAsignados($transportistaId)
    {
        try {
            \Log::info("🔍 Buscando envíos para transportista ID: {$transportistaId}");
            
            // Obtener planta (origen)
            $planta = \App\Models\Almacen::where('es_planta', true)->first();
            
            // IMPORTANTE: Filtrar SOLO por el transportista específico
            $envios = Envio::select('envios.*', 
                    'almacenes.nombre as almacen_nombre',
                    'almacenes.direccion_completa',
                    'almacenes.latitud',
                    'almacenes.longitud',
                    'envio_asignaciones.vehiculo_id',
                    'envio_asignaciones.fecha_asignacion',
                    'vehiculos.placa as vehiculo_placa',
                    'vehiculos.transportista_id') // Obtener transportista a través de vehiculo
                ->join('envio_asignaciones', 'envios.id', '=', 'envio_asignaciones.envio_id')
                ->leftJoin('almacenes', 'envios.almacen_destino_id', '=', 'almacenes.id')
                ->leftJoin('vehiculos', 'envio_asignaciones.vehiculo_id', '=', 'vehiculos.id')
                ->where('vehiculos.transportista_id', '=', $transportistaId) // Filtro a través de vehiculo
                ->whereIn('envios.estado', ['asignado', 'aceptado', 'en_transito'])
                ->orderBy('envios.created_at', 'desc')
                ->get()
                ->map(function($envio) use ($planta, $transportistaId) {
                    // Agregar coordenadas de origen (planta)
                    $envio->origen_lat = $planta->latitud ?? -17.7833;
                    $envio->origen_lng = $planta->longitud ?? -63.1821;
                    $envio->origen_direccion = $planta->direccion_completa ?? 'Planta Principal';
                    
                    // Verificar si tiene ruta multi-entrega (asignación múltiple)
                    $rutaEntregaId = $envio->ruta_entrega_id ?? null;
                    
                    if ($rutaEntregaId) {
                        // Es parte de una ruta multi-entrega
                        $envio->es_asignacion_multiple = true;
                        $envio->tipo_asignacion = 'multiple';
                        $envio->ruta_id = $rutaEntregaId;
                        $envio->es_multi_entrega = true;
                        $envio->es_ruta_multiple = true;
                        
                        // Intentar obtener información de la ruta desde Node.js
                        try {
                            $nodeApiUrl = env('NODE_API_URL', 'http://localhost:3001/api');
                            $rutaResponse = \Illuminate\Support\Facades\Http::timeout(5)
                                ->get("{$nodeApiUrl}/rutas-entrega/{$rutaEntregaId}");
                            
                            if ($rutaResponse->successful() && $rutaResponse->json('success')) {
                                $rutaData = $rutaResponse->json('ruta');
                                $envio->ruta_codigo = $rutaData['codigo'] ?? null;
                                $envio->ruta_estado = $rutaData['estado'] ?? null;
                                $envio->total_envios_ruta = $rutaData['total_envios'] ?? 0;
                                $envio->total_paradas = count($rutaData['paradas'] ?? []);
                            }
                        } catch (\Exception $e) {
                            \Log::warning("No se pudo obtener info de ruta {$rutaEntregaId}: " . $e->getMessage());
                        }
                    } else {
                        // Verificar si es asignación múltiple sin ruta (legacy)
                        $fechaAsignacion = $envio->fecha_asignacion ? \Carbon\Carbon::parse($envio->fecha_asignacion)->format('Y-m-d') : null;
                        $vehiculoId = $envio->vehiculo_id;
                        
                        if ($fechaAsignacion && $vehiculoId) {
                            // Obtener otros envíos del mismo día y vehículo (transportista_id ya no existe en envio_asignaciones)
                            $otrosEnviosMismoDia = \App\Models\EnvioAsignacion::where('vehiculo_id', $vehiculoId)
                                ->whereDate('fecha_asignacion', $fechaAsignacion)
                                ->whereHas('vehiculo', function($q) use ($transportistaId) {
                                    $q->where('transportista_id', $transportistaId);
                                })
                                ->where('envio_id', '!=', $envio->id)
                                ->count();
                            
                            $envio->es_asignacion_multiple = $otrosEnviosMismoDia > 0;
                            $envio->tipo_asignacion = $otrosEnviosMismoDia > 0 ? 'multiple' : 'normal';
                            $envio->total_envios_asignacion = $otrosEnviosMismoDia + 1;
                        } else {
                            $envio->es_asignacion_multiple = false;
                            $envio->tipo_asignacion = 'normal';
                            $envio->total_envios_asignacion = 1;
                        }
                    }
                    
                    return $envio;
                });

            \Log::info("✅ Encontrados {$envios->count()} envíos para transportista {$transportistaId}");
            
            // Loguear los IDs de los envíos para debugging
            $enviosIds = $envios->pluck('id')->toArray();
            \Log::info("📦 IDs de envíos: " . implode(', ', $enviosIds));

            return response()->json([
                'success' => true,
                'data' => $envios,
                'transportista_id' => $transportistaId, // Para debugging
                'total' => $envios->count()
            ]);
        } catch (\Exception $e) {
            \Log::error("❌ Error al obtener envíos para transportista {$transportistaId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener envíos: ' . $e->getMessage()
            ], 500);
        }
    }
}

