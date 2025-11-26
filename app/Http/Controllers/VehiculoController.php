<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;
use App\Models\TipoTransporte;
use App\Models\UnidadMedida;
use App\Models\User;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::with(['tipoTransporte', 'transportista'])->get();
        return view('vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        $tiposTransporte = TipoTransporte::all();
        $unidadesMedida = UnidadMedida::all();
        
        return view('vehiculos.create', compact('tiposTransporte', 'unidadesMedida'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|max:50|unique:vehiculos,placa',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'anio' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'tipo_transporte_id' => 'nullable|exists:tipos_transporte,id',
            'licencia_requerida' => 'required|in:A,B,C',
            'capacidad_carga' => 'nullable|numeric|min:0',
            'unidad_medida_carga_id' => 'nullable|exists:unidades_medida,id',
        ]);

        Vehiculo::create([
            'placa' => $request->placa,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'anio' => $request->anio,
            'tipo_transporte_id' => $request->tipo_transporte_id,
            'licencia_requerida' => $request->licencia_requerida,
            'capacidad_carga' => $request->capacidad_carga,
            'unidad_medida_carga_id' => $request->unidad_medida_carga_id,
            'disponible' => true,
            'estado' => 'activo',
        ]);

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo creado correctamente');
    }

    public function edit(Vehiculo $vehiculo)
    {
        $tiposTransporte = TipoTransporte::all();
        $unidadesMedida = UnidadMedida::all();
        
        return view('vehiculos.edit', compact('vehiculo', 'tiposTransporte', 'unidadesMedida'));
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        $request->validate([
            'placa' => 'required|string|max:50|unique:vehiculos,placa,' . $vehiculo->id,
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'anio' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'tipo_transporte_id' => 'nullable|exists:tipos_transporte,id',
            'licencia_requerida' => 'required|in:A,B,C',
            'capacidad_carga' => 'nullable|numeric|min:0',
            'unidad_medida_carga_id' => 'nullable|exists:unidades_medida,id',
            'disponible' => 'nullable|boolean',
            'estado' => 'nullable|in:activo,mantenimiento,inactivo',
        ]);

        $vehiculo->update([
            'placa' => $request->placa,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'anio' => $request->anio,
            'tipo_transporte_id' => $request->tipo_transporte_id,
            'licencia_requerida' => $request->licencia_requerida,
            'capacidad_carga' => $request->capacidad_carga,
            'unidad_medida_carga_id' => $request->unidad_medida_carga_id,
            'disponible' => $request->has('disponible'),
            'estado' => $request->estado ?? 'activo',
        ]);

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo actualizado correctamente');
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $vehiculo->delete();
        return redirect()->route('vehiculos.index')->with('success', 'Vehículo eliminado correctamente');
    }
}
