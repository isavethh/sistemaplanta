<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoVehiculo;

class TipoVehiculoController extends Controller
{
    public function index()
    {
        $tipos = TipoVehiculo::all();
        return view('tiposvehiculo.index', compact('tipos'));
    }

    public function create()
    {
        return view('tiposvehiculo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_vehiculo,nombre',
        ]);

        TipoVehiculo::create(['nombre' => $request->nombre]);

        return redirect()->route('tiposvehiculo.index')->with('success', 'Tipo de vehículo creado');
    }

    public function edit(TipoVehiculo $tiposvehiculo)
    {
        return view('tiposvehiculo.edit', ['tiposvehiculo' => $tiposvehiculo]);
    }

    public function update(Request $request, TipoVehiculo $tiposvehiculo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_vehiculo,nombre,' . $tiposvehiculo->id,
        ]);

        $tiposvehiculo->update(['nombre' => $request->nombre]);

        return redirect()->route('tiposvehiculo.index')->with('success', 'Tipo de vehículo actualizado');
    }

    public function destroy(TipoVehiculo $tiposvehiculo)
    {
        $tiposvehiculo->delete();
        return redirect()->route('tiposvehiculo.index')->with('success', 'Tipo de vehículo eliminado');
    }
}