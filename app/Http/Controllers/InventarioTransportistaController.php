<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioTransportistaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Verificar que el usuario sea transportista
        if (!$user->hasRole('transportista')) {
            abort(403, 'No tienes permiso para ver este inventario.');
        }
        
        $transportistaId = $user->id;
        
        // Obtener IDs de vehículos del transportista
        $vehiculosIds = DB::table('vehiculos')
            ->where('transportista_id', $transportistaId)
            ->pluck('id');
        
        if ($vehiculosIds->isEmpty()) {
            return view('inventarios-transportista.index', [
                'inventarios' => collect([]),
                'estadisticas' => [
                    'total_productos' => 0,
                    'total_cantidad' => 0,
                    'total_peso' => 0,
                    'total_valor' => 0,
                    'total_envios_entregados' => 0,
                ]
            ])->with('info', 'No tienes vehículos asignados.');
        }
        
        // Obtener productos de envíos entregados por este transportista
        $inventarios = DB::table('envio_productos as ep')
            ->join('envios as e', 'ep.envio_id', '=', 'e.id')
            ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->whereIn('ea.vehiculo_id', $vehiculosIds)
            ->where('e.estado', 'entregado')
            ->select(
                'ep.producto_nombre',
                'e.categoria',
                DB::raw('SUM(ep.cantidad) as cantidad'),
                DB::raw('SUM(ep.total_peso) as peso'),
                DB::raw('AVG(ep.precio_unitario) as precio_unitario'),
                DB::raw('SUM(ep.total_precio) as total_precio'),
                DB::raw('MAX(e.fecha_entrega) as fecha_entrega'),
                DB::raw('COUNT(DISTINCT e.id) as total_envios')
            )
            ->groupBy('ep.producto_nombre', 'e.categoria')
            ->orderBy('ep.producto_nombre')
            ->get();
        
        // Calcular estadísticas
        $estadisticas = [
            'total_productos' => $inventarios->count(),
            'total_cantidad' => $inventarios->sum('cantidad'),
            'total_peso' => $inventarios->sum('peso'),
            'total_valor' => $inventarios->sum('total_precio'),
            'total_envios_entregados' => DB::table('envios as e')
                ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                ->whereIn('ea.vehiculo_id', $vehiculosIds)
                ->where('e.estado', 'entregado')
                ->distinct()
                ->count('e.id'),
        ];
        
        // Aplicar filtros si existen
        if ($request->filled('categoria')) {
            $inventarios = $inventarios->filter(function($item) use ($request) {
                return $item->categoria === $request->categoria;
            })->values();
        }
        
        if ($request->filled('producto')) {
            $inventarios = $inventarios->filter(function($item) use ($request) {
                return stripos($item->producto_nombre, $request->producto) !== false;
            })->values();
        }
        
        // Obtener categorías únicas para el filtro
        $categorias = DB::table('envio_productos as ep')
            ->join('envios as e', 'ep.envio_id', '=', 'e.id')
            ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->whereIn('ea.vehiculo_id', $vehiculosIds)
            ->where('e.estado', 'entregado')
            ->select('e.categoria')
            ->distinct()
            ->pluck('categoria')
            ->filter()
            ->sort()
            ->values();
        
        return view('inventarios-transportista.index', compact('inventarios', 'estadisticas', 'categorias'));
    }
}
