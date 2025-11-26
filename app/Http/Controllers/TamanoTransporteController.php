<?php

namespace App\Http\Controllers;

use App\Models\TamanoTransporte;
use Illuminate\Http\Request;

class TamanoTransporteController extends Controller
{
    public function index()
    {
        $tamanosTransporte = TamanoTransporte::all();
        return view('tamanos-transporte.index', compact('tamanosTransporte'));
    }

    public function create()
    {
        return view('tamanos-transporte.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:tamanos_transporte,nombre',
            'descripcion' => 'nullable|string',
        ]);

        TamanoTransporte::create($request->all());

        return redirect()->route('tamanos-transporte.index')
            ->with('success', 'Tamaño de transporte creado correctamente');
    }

    public function edit(TamanoTransporte $tamanosTransporte)
    {
        return view('tamanos-transporte.edit', compact('tamanosTransporte'));
    }

    public function update(Request $request, TamanoTransporte $tamanosTransporte)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:tamanos_transporte,nombre,' . $tamanosTransporte->id,
            'descripcion' => 'nullable|string',
        ]);

        $tamanosTransporte->update($request->all());

        return redirect()->route('tamanos-transporte.index')
            ->with('success', 'Tamaño de transporte actualizado correctamente');
    }

    public function destroy(TamanoTransporte $tamanosTransporte)
    {
        $tamanosTransporte->delete();

        return redirect()->route('tamanos-transporte.index')
            ->with('success', 'Tamaño de transporte eliminado correctamente');
    }
}

