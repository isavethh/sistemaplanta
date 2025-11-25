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
        $planta = Almacen::where('es_planta', true)->first();
        
        // Si no existe la planta, crearla
        if (!$planta) {
            $planta = Almacen::create([
                'nombre' => 'Planta Principal',
                'latitud' => -17.783333,
                'longitud' => -63.182778,
                'direccion_completa' => 'Santa Cruz de la Sierra, Bolivia',
                'es_planta' => true,
                'activo' => true,
            ]);
        }
        
        return view('direcciones.create', compact('almacenes', 'planta'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'almacen_origen_id' => 'required|exists:almacenes,id',
            'almacen_destino_id' => 'required|exists:almacenes,id|different:almacen_origen_id',
            'distancia_km' => 'required|numeric|min:0',
            'tiempo_estimado_minutos' => 'required|integer|min:0',
            'ruta_descripcion' => 'nullable|string|max:1000',
        ]);

        $direccion = Direccion::create($validated);
        
        return redirect()->route('direcciones.index')->with('success', 'Ruta creada exitosamente. Distancia: ' . $direccion->distancia_km . ' km, Tiempo: ' . $direccion->tiempo_estimado_minutos . ' min');
    }

    public function edit(Direccion $direccion)
    {
        $almacenes = Almacen::where('activo', true)->get();
        return view('direcciones.edit', compact('direccion', 'almacenes'));
    }

    public function update(Request $request, Direccion $direccion)
    {
        $validated = $request->validate([
            'almacen_origen_id' => 'required|exists:almacenes,id',
            'almacen_destino_id' => 'required|exists:almacenes,id|different:almacen_origen_id',
            'distancia_km' => 'required|numeric|min:0',
            'tiempo_estimado_minutos' => 'required|integer|min:0',
            'ruta_descripcion' => 'nullable|string|max:1000',
        ]);

        $direccion->update($validated);
        
        return redirect()->route('direcciones.index')->with('success', 'Ruta actualizada exitosamente. Distancia: ' . $direccion->distancia_km . ' km');
    }

    public function destroy(Direccion $direccione)
    {
        $direccione->delete();
        return redirect()->route('direcciones.index')->with('success', 'Ruta eliminada exitosamente');
    }
}
