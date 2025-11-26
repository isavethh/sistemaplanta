<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Direccion;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index()
    {
        $almacenes = Almacen::with('usuarioAlmacen')->get();
        return view('almacenes.index', compact('almacenes'));
    }

    public function create()
    {
        return view('almacenes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'direccion_completa' => 'nullable|string',
        ]);

        $validated['activo'] = $request->has('activo');
        $validated['es_planta'] = $request->has('es_planta');
        
        Almacen::create($validated);
        return redirect()->route('almacenes.index')->with('success', 'Almacén creado exitosamente.');
    }

    public function edit(Almacen $almacen)
    {
        return view('almacenes.edit', compact('almacen'));
    }

    public function update(Request $request, Almacen $almacen)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'direccion_completa' => 'nullable|string',
        ]);

        $validated['activo'] = $request->has('activo');
        $validated['es_planta'] = $request->has('es_planta');
        
        $almacen->update($validated);
        return redirect()->route('almacenes.index')->with('success', 'Almacén actualizado exitosamente.');
    }

    public function destroy(Almacen $almacen)
    {
        $almacen->delete();
        return redirect()->route('almacenes.index')->with('success', 'Almacén eliminado exitosamente.');
    }

    public function inventario(Almacen $almacen)
    {
        $inventario = $almacen->inventario()->get();
        return view('almacenes.inventario', compact('almacen', 'inventario'));
    }
}
