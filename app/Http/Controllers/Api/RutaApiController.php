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
            WHERE e.estado = 'en_transito'
            ORDER BY e.fecha_inicio_transito DESC
        ");
        
        // Envíos esperando inicio (asignados o aceptados)
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
            WHERE e.estado IN ('asignado', 'aceptado')
            ORDER BY e.created_at DESC
        ");
        
        return response()->json([
            'success' => true,
            'en_transito' => $enTransito,
            'esperando' => $esperando,
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
}

