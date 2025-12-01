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
            $transportistas = User::where('tipo', 'transportista')
                ->select('id', 'name as nombre', 'email', 'telefono', 'licencia')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $transportistas
            ]);
        } catch (\Exception $e) {
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

            $transportista = User::where('id', $request->transportista_id)
                ->where('tipo', 'transportista')
                ->first();

            if (!$transportista) {
                return response()->json([
                    'success' => false,
                    'error' => 'Transportista no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'transportista' => [
                        'id' => $transportista->id,
                        'nombre' => $transportista->name,
                        'email' => $transportista->email,
                        'telefono' => $transportista->telefono,
                        'licencia' => $transportista->licencia,
                        'tipo' => 'transportista'
                    ],
                    'token' => 'transportista-token-' . $transportista->id
                ]
            ]);
        } catch (\Exception $e) {
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
                    'envio_asignaciones.transportista_id',
                    'envio_asignaciones.vehiculo_id',
                    'envio_asignaciones.fecha_asignacion',
                    'vehiculos.placa as vehiculo_placa')
                ->join('envio_asignaciones', 'envios.id', '=', 'envio_asignaciones.envio_id')
                ->leftJoin('almacenes', 'envios.almacen_destino_id', '=', 'almacenes.id')
                ->leftJoin('vehiculos', 'envio_asignaciones.vehiculo_id', '=', 'vehiculos.id')
                ->where('envio_asignaciones.transportista_id', '=', $transportistaId) // Filtro ESTRICTO
                ->whereIn('envios.estado', ['asignado', 'aceptado', 'en_transito'])
                ->orderBy('envios.created_at', 'desc')
                ->get()
                ->map(function($envio) use ($planta) {
                    // Agregar coordenadas de origen (planta)
                    $envio->origen_lat = $planta->latitud ?? -17.7833;
                    $envio->origen_lng = $planta->longitud ?? -63.1821;
                    $envio->origen_direccion = $planta->direccion_completa ?? 'Planta Principal';
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

