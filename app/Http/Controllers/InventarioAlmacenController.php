<?php

namespace App\Http\Controllers;

use App\Models\InventarioAlmacen;
use App\Models\Almacen;
use App\Models\EnvioProducto;
use Illuminate\Http\Request;

class InventarioAlmacenController extends Controller
{
    public function index()
    {
        $inventarios = InventarioAlmacen::with(['almacen', 'envioProducto'])->get();
        return view('inventarios.index', compact('inventarios'));
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

