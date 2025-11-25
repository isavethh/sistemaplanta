<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstadoVehiculo;

class EstadoVehiculoController extends Controller
{
    public function index()
    {
        $estados = EstadoVehiculo::all();
        return view('estadosvehiculo.index', compact('estados'));
    }

    public function create()
    {
        return view('estadosvehiculo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:estados_vehiculo,nombre',
        ]);

        EstadoVehiculo::create(['nombre' => $request->nombre]);

        return redirect()->route('estadosvehiculo.index')->with('success', 'Estado creado');
    }

    public function edit(EstadoVehiculo $estadosvehiculo)
    {
        return view('estadosvehiculo.edit', ['estadosvehiculo' => $estadosvehiculo]);
    }

    public function update(Request $request, EstadoVehiculo $estadosvehiculo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:estados_vehiculo,nombre,' . $estadosvehiculo->id,
        ]);

        $estadosvehiculo->update(['nombre' => $request->nombre]);

        return redirect()->route('estadosvehiculo.index')->with('success', 'Estado actualizado');
    }

    public function destroy(EstadoVehiculo $estadosvehiculo)
    {
        $estadosvehiculo->delete();
        return redirect()->route('estadosvehiculo.index')->with('success', 'Estado eliminado');
    }
}