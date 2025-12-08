<?php

namespace App\Http\Controllers;

use App\Models\InventarioAlmacen;
use App\Models\Almacen;
use App\Models\EnvioProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioAlmacenController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todos los almacenes (excepto la planta) para el dropdown
        $almacenes = Almacen::where('es_planta', false)->where('activo', true)->get();
        
        // Almacén seleccionado (por defecto el primero)
        $almacenSeleccionado = $request->get('almacen_id');
        
        if ($almacenSeleccionado) {
            // Obtener productos de envíos entregados a este almacén
            $inventarios = DB::table('envio_productos as ep')
                ->join('envios as e', 'ep.envio_id', '=', 'e.id')
                ->where('e.almacen_destino_id', $almacenSeleccionado)
                ->where('e.estado', 'entregado')
                ->select(
                    'ep.producto_nombre',
                    'e.categoria',
                    DB::raw('SUM(ep.cantidad) as cantidad'),
                    DB::raw('SUM(ep.total_peso) as peso'),
                    DB::raw('AVG(ep.precio_unitario) as precio_unitario'),
                    DB::raw('SUM(ep.total_precio) as total_precio'),
                    DB::raw('MAX(e.fecha_entrega) as fecha_llegada')
                )
                ->groupBy('ep.producto_nombre', 'e.categoria')
                ->get();
            
            $almacenActual = Almacen::find($almacenSeleccionado);
        } else {
            $inventarios = collect([]);
            $almacenActual = null;
        }
        
        return view('inventarios.index', compact('inventarios', 'almacenes', 'almacenSeleccionado', 'almacenActual'));
    }

    public function create()
    {
        $almacenes = Almacen::all();
        $envioProductos = EnvioProducto::all();
        return view('inventarios.create', compact('almacenes', 'envioProductos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'producto_nombre' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'peso' => 'nullable|numeric|min:0',
            'precio_unitario' => 'nullable|numeric|min:0',
            'fecha_llegada' => 'nullable|date',
        ]);

        InventarioAlmacen::create($request->all());
        return redirect()->route('inventarios.index')->with('success', 'Inventario creado exitosamente');
    }

    public function show(InventarioAlmacen $inventario)
    {
        return view('inventarios.show', compact('inventario'));
    }

    public function edit(InventarioAlmacen $inventario)
    {
        $almacenes = Almacen::all();
        $envioProductos = EnvioProducto::all();
        return view('inventarios.edit', compact('inventario', 'almacenes', 'envioProductos'));
    }

    public function update(Request $request, InventarioAlmacen $inventario)
    {
        $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'producto_nombre' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'peso' => 'nullable|numeric|min:0',
            'precio_unitario' => 'nullable|numeric|min:0',
            'fecha_llegada' => 'nullable|date',
        ]);

        $inventario->update($request->all());
        return redirect()->route('inventarios.index')->with('success', 'Inventario actualizado exitosamente');
    }

    public function destroy(InventarioAlmacen $inventario)
    {
        $inventario->delete();
        return redirect()->route('inventarios.index')->with('success', 'Inventario eliminado exitosamente');
    }

    public function porAlmacen(Almacen $almacen)
    {
        $inventarios = InventarioAlmacen::where('almacen_id', $almacen->id)
            ->with('envioProducto')
            ->get();
        return view('inventarios.por-almacen', compact('almacen', 'inventarios'));
    }
}

