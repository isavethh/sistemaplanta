<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaVentaController extends Controller
{
    public function index()
    {
        try {
            // Obtener notas de venta desde PostgreSQL con timeout
            DB::connection('pgsql')->statement('SET statement_timeout = 5000'); // 5 segundos
            
            $notasVenta = DB::connection('pgsql')->select("
                SELECT nv.id, 
                       nv.numero_nota,
                       nv.fecha_emision,
                       nv.total_cantidad,
                       nv.total_precio,
                       e.codigo as envio_codigo,
                       a.nombre as almacen_nombre
                FROM notas_venta nv
                LEFT JOIN envios e ON nv.envio_id = e.id
                LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
                ORDER BY nv.id DESC
                LIMIT 100
            ");

            return view('notas-venta.index', compact('notasVenta'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar notas de venta: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            DB::connection('pgsql')->statement('SET statement_timeout = 5000');
            
            // Obtener nota de venta específica
            $nota = DB::connection('pgsql')->selectOne("
                SELECT nv.*, 
                       e.codigo as envio_codigo,
                       e.created_at as envio_fecha,
                       a.nombre as almacen_nombre,
                       a.direccion_completa as almacen_direccion
                FROM notas_venta nv
                LEFT JOIN envios e ON nv.envio_id = e.id
                LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
                WHERE nv.id = ?
            ", [$id]);

            if (!$nota) {
                abort(404, 'Nota de venta no encontrada');
            }

            // Obtener productos del envío
            $productos = DB::connection('pgsql')->select("
                SELECT producto_nombre, cantidad, precio_unitario, peso_unitario, total_peso, total_precio 
                FROM envio_productos 
                WHERE envio_id = ?
            ", [$nota->envio_id]);

            return view('notas-venta.show', compact('nota', 'productos'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar nota de venta: ' . $e->getMessage());
        }
    }

    public function verHTML($id)
    {
        // Redirigir a la URL del backend Node.js para ver el HTML
        $nodeBackendUrl = env('NODE_BACKEND_URL', 'http://localhost:3001');
        return redirect("{$nodeBackendUrl}/api/notas-venta/{$id}/html");
    }
}
