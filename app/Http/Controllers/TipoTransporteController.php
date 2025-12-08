<?php

namespace App\Http\Controllers;

use App\Models\TipoTransporte;
use Illuminate\Http\Request;

class TipoTransporteController extends Controller
{
    public function index()
    {
        $tipos = TipoTransporte::all();
        return view('tipos-transporte.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipos-transporte.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_transporte,nombre',
        ]);

        TipoTransporte::create($request->all());
        return redirect()->route('tipos-transporte.index')->with('success', 'Tipo de transporte creado exitosamente');
    }

    public function edit(TipoTransporte $tiposTransporte)
    {
        return view('tipos-transporte.edit', compact('tiposTransporte'));
    }

    public function update(Request $request, TipoTransporte $tiposTransporte)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_transporte,nombre,' . $tiposTransporte->id,
        ]);

        $tiposTransporte->update($request->all());
        return redirect()->route('tipos-transporte.index')->with('success', 'Tipo de transporte actualizado exitosamente');
    }

    public function destroy(TipoTransporte $tiposTransporte)
    {
        try {
            // Verificar si tiene vehículos asociados
            $tieneVehiculos = \DB::table('vehiculos')->where('tipo_transporte_id', $tiposTransporte->id)->exists();
            
            if ($tieneVehiculos) {
                return redirect()->route('tipos-transporte.index')
                    ->with('error', 'No se puede eliminar porque hay vehículos con este tipo de transporte.');
            }
            
            $tiposTransporte->delete();
            return redirect()->route('tipos-transporte.index')->with('success', 'Tipo de transporte eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('tipos-transporte.index')
                ->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}

