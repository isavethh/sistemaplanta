<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RutaApiController extends Controller
{
    /**
     * Obtener envíos activos para el mapa en tiempo real
     * GET /api/rutas/envios-activos
     */
    public function enviosActivos()
    {
        // Envíos en tránsito (todos los envíos activos)
        $enTransito = DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                e.fecha_inicio_transito,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                a.direccion_completa,
                u.name as transportista_nombre,
                v.placa as vehiculo_placa
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            LEFT JOIN users u ON v.transportista_id = u.id
            WHERE e.estado = 'en_transito'
            ORDER BY e.fecha_inicio_transito DESC
        ");
        
        // Envíos esperando inicio (asignados o aceptados) - "Esperando inicio"
        $esperando = DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                u.name as transportista_nombre,
                ea.fecha_aceptacion
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            LEFT JOIN users u ON v.transportista_id = u.id
            WHERE e.estado IN ('asignado', 'aceptado')
            ORDER BY e.created_at DESC
        ");
        
        // Envíos cancelados por incidente (últimos 7 días)
        $cancelados = DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                e.updated_at as fecha_cancelacion,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                a.direccion_completa,
                u.name as transportista_nombre,
                v.placa as vehiculo_placa,
                CASE WHEN i.id IS NOT NULL THEN true ELSE false END as cancelado_por_incidente,
                i.id as incidente_id
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            LEFT JOIN users u ON v.transportista_id = u.id
            LEFT JOIN incidentes i ON e.id = i.envio_id AND i.accion = 'cancelar'
            WHERE e.estado = 'cancelado' 
                AND e.updated_at >= NOW() - INTERVAL '7 days'
            ORDER BY e.updated_at DESC
            LIMIT 50
        ");
        
        return response()->json([
            'success' => true,
            'en_transito' => $enTransito,
            'esperando' => $esperando,
            'cancelados' => $cancelados,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Obtener envíos activos filtrados por almacén
     * GET /api/rutas/envios-activos-almacen/{almacenId}
     */
    public function enviosActivosPorAlmacen($almacenId)
    {
        // Envíos en tránsito hacia este almacén
        $enTransito = DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                e.fecha_inicio_transito,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                a.direccion_completa,
                u.name as transportista_nombre,
                v.placa as vehiculo_placa
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            LEFT JOIN users u ON v.transportista_id = u.id
            WHERE e.estado = 'en_transito' AND e.almacen_destino_id = ?
            ORDER BY e.fecha_inicio_transito DESC
        ", [$almacenId]);
        
        // Envíos esperando inicio (asignados o aceptados) hacia este almacén
        $esperando = DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                u.name as transportista_nombre
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            LEFT JOIN users u ON v.transportista_id = u.id
            WHERE e.estado IN ('asignado', 'aceptado') AND e.almacen_destino_id = ?
            ORDER BY e.created_at DESC
        ", [$almacenId]);
        
        return response()->json([
            'success' => true,
            'en_transito' => $enTransito,
            'esperando' => $esperando,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Obtener seguimiento de un envío específico
     * GET /api/rutas/seguimiento/{id}
     */
    public function seguimiento($id)
    {
        $seguimiento = DB::select("
            SELECT latitud, longitud, velocidad, timestamp as created_at
            FROM seguimiento_envio
            WHERE envio_id = ?
            ORDER BY timestamp ASC
        ", [$id]);
        
        return response()->json([
            'success' => true,
            'data' => $seguimiento
        ]);
    }

    /**
     * Obtener información de envíos por IDs (incluyendo entregados)
     * POST /api/rutas/envios-por-ids
     * Body: { "ids": [1, 2, 3, ...] }
     */
    public function enviosPorIds(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $ids = $request->input('ids', []);
        
        \Log::info('📦 [enviosPorIds] Consultando envíos', [
            'ids_recibidos' => $ids,
            'total_ids' => count($ids),
        ]);
        
        if (empty($ids)) {
            \Log::info('📦 [enviosPorIds] Sin IDs, retornando vacío');
            return response()->json([
                'success' => true,
                'en_transito' => [],
                'esperando' => [],
                'entregados' => [],
                'cancelados' => [],
                'timestamp' => now()->toIso8601String()
            ], 200, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization',
            ]);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // Envíos en tránsito
        $enTransito = DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                e.fecha_inicio_transito,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                a.direccion_completa,
                u.name as transportista_nombre,
                v.placa as vehiculo_placa
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            LEFT JOIN users u ON v.transportista_id = u.id
            WHERE e.estado = 'en_transito' AND e.id IN ($placeholders)
            ORDER BY e.fecha_inicio_transito DESC
        ", $ids);
        
        // Envíos esperando inicio (asignados o aceptados)
        $esperando = DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                a.direccion_completa,
                u.name as transportista_nombre,
                v.placa as vehiculo_placa
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            LEFT JOIN users u ON v.transportista_id = u.id
            WHERE e.estado IN ('asignado', 'aceptado') AND e.id IN ($placeholders)
            ORDER BY e.created_at DESC
        ", $ids);
        
        // Envíos entregados
        $entregados = DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                e.fecha_inicio_transito,
                e.updated_at as fecha_entrega,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                a.direccion_completa,
                u.name as transportista_nombre,
                v.placa as vehiculo_placa
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            LEFT JOIN users u ON v.transportista_id = u.id
            WHERE e.estado = 'entregado' AND e.id IN ($placeholders)
            ORDER BY e.updated_at DESC
        ", $ids);
        
        // Envíos cancelados
        $cancelados = DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                e.fecha_inicio_transito,
                e.updated_at as fecha_cancelacion,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                a.direccion_completa,
                u.name as transportista_nombre,
                v.placa as vehiculo_placa
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            LEFT JOIN users u ON v.transportista_id = u.id
            WHERE e.estado = 'cancelado' AND e.id IN ($placeholders)
            ORDER BY e.updated_at DESC
        ", $ids);
        
        \Log::info('📦 [enviosPorIds] Resultados', [
            'en_transito_count' => count($enTransito),
            'esperando_count' => count($esperando),
            'entregados_count' => count($entregados),
            'cancelados_count' => count($cancelados),
        ]);
        
        return response()->json([
            'success' => true,
            'en_transito' => $enTransito,
            'esperando' => $esperando,
            'entregados' => $entregados,
            'cancelados' => $cancelados,
            'timestamp' => now()->toIso8601String()
        ], 200, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization',
        ]);
    }
}

