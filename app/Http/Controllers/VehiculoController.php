<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;
use App\Models\TipoVehiculo;
use App\Models\EstadoVehiculo;
use App\Models\User;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::with('user')->get();
        return view('vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        $tipos = TipoVehiculo::all();
        $estados = EstadoVehiculo::all();
        $users = User::where('role', 'transportista')->get();
        return view('vehiculos.create', compact('tipos', 'estados', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|max:50|unique:vehiculos,placa',
            'tipo' => 'nullable|integer',
            'capacidad' => 'nullable|numeric',
            'user_id' => 'nullable|integer',
        ]);

        Vehiculo::create($request->only(['placa', 'tipo', 'capacidad', 'user_id']));

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo creado');
    }

    public function edit(Vehiculo $vehiculo)
    {
        $tipos = TipoVehiculo::all();
        $estados = EstadoVehiculo::all();
        $users = User::where('role', 'transportista')->get();
        return view('vehiculos.edit', compact('vehiculo', 'tipos', 'estados', 'users'));
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        $request->validate([
            'placa' => 'required|string|max:50|unique:vehiculos,placa,' . $vehiculo->id,
            'tipo' => 'nullable|integer',
            'capacidad' => 'nullable|numeric',
            'user_id' => 'nullable|integer',
        ]);

        $vehiculo->update($request->only(['placa', 'tipo', 'capacidad', 'user_id']));

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo actualizado');
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $vehiculo->delete();
        return redirect()->route('vehiculos.index')->with('success', 'Vehículo eliminado');
    }
}