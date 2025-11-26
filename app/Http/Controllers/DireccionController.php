<?php

namespace App\Http\Controllers;

use App\Models\Direccion;
use App\Models\Almacen;
use Illuminate\Http\Request;

class DireccionController extends Controller
{
    public function index()
    {
        $direcciones = Direccion::with(['almacenOrigen', 'almacenDestino'])->get();
        return view('direcciones.index', compact('direcciones'));
    }

    public function create()
    {
        $almacenes = Almacen::where('activo', true)->get();
        return view('direcciones.create', compact('almacenes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'almacen_origen_id' => 'required|exists:almacenes,id',
            'almacen_destino_id' => 'required|exists:almacenes,id|different:almacen_origen_id',
        ]);

        Direccion::create($request->all());
        return redirect()->route('direcciones.index')->with('success', 'Ruta creada exitosamente');
    }

    public function edit(Direccion $direccion)
    {
        $almacenes = Almacen::where('activo', true)->get();
        return view('direcciones.edit', compact('direccion', 'almacenes'));
    }

    public function update(Request $request, Direccion $direccion)
    {
        $request->validate([
            'almacen_origen_id' => 'required|exists:almacenes,id',
            'almacen_destino_id' => 'required|exists:almacenes,id|different:almacen_origen_id',
        ]);

        $direccion->update($request->all());
        return redirect()->route('direcciones.index')->with('success', 'Ruta actualizada exitosamente');
    }

    public function destroy(Direccion $direccione)
    {
        $direccione->delete();
        return redirect()->route('direcciones.index')->with('success', 'Ruta eliminada exitosamente');
    }
}
