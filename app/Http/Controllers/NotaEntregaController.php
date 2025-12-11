<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\NotaVenta;
use App\Models\Envio;

class NotaEntregaController extends Controller
{
    public function index()
    {
        try {
            // Obtener notas de entrega desde PostgreSQL con timeout
            DB::connection('pgsql')->statement('SET statement_timeout = 5000'); // 5 segundos
            
            $notasEntrega = DB::connection('pgsql')->select("
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

            return view('notas-entrega.index', compact('notasEntrega'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar notas de entrega: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('notas-entrega.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'envio_id' => 'required|exists:envios,id',
            ]);
            
            $envio = Envio::with('almacenDestino')->findOrFail($request->envio_id);
            
            // Verificar si ya existe una nota de venta para este envío
            $notaExistente = NotaVenta::where('envio_id', $envio->id)->first();
            if ($notaExistente) {
                return redirect()->route('notas-entrega.show', $notaExistente->id)
                    ->with('info', 'Ya existe una nota de entrega para este envío.');
            }
            
            // Generar número de nota
            $numeroNota = NotaVenta::generarNumeroNota($envio->codigo);
            
            // Crear nota de venta
            $nota = NotaVenta::create([
                'numero_nota' => $numeroNota,
                'envio_id' => $envio->id,
                'fecha_emision' => now(),
                'almacen_nombre' => $envio->almacenDestino->nombre ?? null,
                'almacen_direccion' => $envio->almacenDestino->direccion_completa ?? null,
                'total_cantidad' => $envio->total_cantidad,
                'total_precio' => $envio->total_precio,
                'subtotal' => $envio->total_precio,
                'porcentaje_iva' => 13,
                'observaciones' => $envio->observaciones,
            ]);
            
            return redirect()->route('notas-entrega.show', $nota->id)
                ->with('success', 'Nota de entrega creada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear nota de entrega: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            DB::connection('pgsql')->statement('SET statement_timeout = 5000');
            
            // Obtener nota de entrega específica
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
                abort(404, 'Nota de entrega no encontrada');
            }

            // Obtener productos del envío
            $productos = DB::connection('pgsql')->select("
                SELECT producto_nombre, cantidad, precio_unitario, peso_unitario, total_peso, total_precio 
                FROM envio_productos 
                WHERE envio_id = ?
            ", [$nota->envio_id]);

            return view('notas-entrega.show', compact('nota', 'productos'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar nota de entrega: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Implementar si es necesario
        return redirect()->route('notas-entrega.index');
    }

    public function update(Request $request, $id)
    {
        // Implementar si es necesario
        return redirect()->route('notas-entrega.index')->with('success', 'Nota de entrega actualizada exitosamente');
    }

    public function destroy($id)
    {
        // Implementar si es necesario
        return redirect()->route('notas-entrega.index')->with('success', 'Nota de entrega eliminada exitosamente');
    }

    public function verHTML($id)
    {
        // ID puede ser el ID del envío o el ID de la nota
        // Primero intentar buscar por ID de nota
        $nota = DB::table('notas_venta')->where('id', $id)->first();
        
        // Si no se encuentra, buscar por envio_id
        if (!$nota) {
            $nota = DB::table('notas_venta')
                ->where('envio_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        if (!$nota) {
            abort(404, 'Nota de entrega no encontrada');
        }
        
        // Obtener datos completos del envío
        $envio = DB::table('envios as e')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->leftJoin('users as t', 'v.transportista_id', '=', 't.id')
            ->where('e.id', $nota->envio_id)
            ->select(
                'e.*',
                'e.codigo as envio_codigo',
                'e.estado as envio_estado',
                'a.nombre as almacen_nombre',
                'a.direccion_completa as almacen_direccion',
                't.name as transportista_nombre',
                't.email as transportista_email',
                'ea.fecha_aceptacion',
                'ea.observaciones as firma_transportista'
            )
            ->first();
        
        // Obtener productos del envío
        $productos = DB::table('envio_productos')
            ->where('envio_id', $nota->envio_id)
            ->select(
                'producto_nombre',
                'cantidad',
                'precio_unitario',
                'peso_unitario',
                'total_peso',
                'total_precio'
            )
            ->get();
        
        return view('notas-entrega.html', compact('nota', 'envio', 'productos'));
    }
}

