<?php

namespace App\Http\Controllers;

use App\Models\TamanoVehiculo;
use Illuminate\Http\Request;

class TamanoVehiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tamanos = TamanoVehiculo::orderBy('capacidad_min')->get();
        return view('tamanos-vehiculo.index', compact('tamanos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tamanos-vehiculo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tamano_vehiculos,nombre',
            'descripcion' => 'nullable|string|max:500',
            'capacidad_min' => 'nullable|numeric|min:0',
            'capacidad_max' => 'nullable|numeric|min:0',
        ]);

        TamanoVehiculo::create($request->all());

        return redirect()->route('tamanos-vehiculo.index')->with('success', 'Tamaño de vehículo creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(TamanoVehiculo $tamanoVehiculo)
    {
        return view('tamanos-vehiculo.show', compact('tamanoVehiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tamano = TamanoVehiculo::findOrFail($id);
        return view('tamanos-vehiculo.edit', compact('tamano'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tamano = TamanoVehiculo::findOrFail($id);
        
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tamano_vehiculos,nombre,' . $tamano->id,
            'descripcion' => 'nullable|string|max:500',
            'capacidad_min' => 'nullable|numeric|min:0',
            'capacidad_max' => 'nullable|numeric|min:0',
        ]);

        $tamano->update($request->all());

        return redirect()->route('tamanos-vehiculo.index')->with('success', 'Tamaño de vehículo actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tamano = TamanoVehiculo::findOrFail($id);
        $tamano->delete();

        return redirect()->route('tamanos-vehiculo.index')->with('success', 'Tamaño de vehículo eliminado correctamente');
    }
}
