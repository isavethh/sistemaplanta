<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;
use App\Models\TipoTransporte;
use App\Models\TamanoVehiculo;
use App\Models\UnidadMedida;
use App\Models\User;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::with(['tipoTransporte', 'transportista', 'tamanoVehiculo', 'unidadMedidaCarga'])->get();
        return view('vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        $tiposTransporte = TipoTransporte::all();
        $tamanosVehiculo = TamanoVehiculo::all();
        $unidadesMedida = UnidadMedida::all();
        
        return view('vehiculos.create', compact('tiposTransporte', 'tamanosVehiculo', 'unidadesMedida'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|max:50|unique:vehiculos,placa',
            'tipo_transporte_id' => 'nullable|exists:tipos_transporte,id',
            'tamano_vehiculo_id' => 'nullable|exists:tamano_vehiculos,id',
            'licencia_requerida' => 'required|in:A,B,C',
            'capacidad_carga' => 'nullable|numeric|min:0',
            'unidad_medida_carga_id' => 'nullable|exists:unidades_medida,id',
        ]);

        // Convertir cadenas vacías a null para campos nullable
        $tipoTransporteId = $request->tipo_transporte_id === '' ? null : $request->tipo_transporte_id;
        $tamanoVehiculoId = $request->tamano_vehiculo_id === '' ? null : $request->tamano_vehiculo_id;
        $capacidadCarga = $request->capacidad_carga === '' || $request->capacidad_carga === null ? null : $request->capacidad_carga;
        $unidadMedidaCargaId = $request->unidad_medida_carga_id === '' ? null : $request->unidad_medida_carga_id;

        Vehiculo::create([
            'placa' => $request->placa,
            'tipo_transporte_id' => $tipoTransporteId,
            'tamano_vehiculo_id' => $tamanoVehiculoId,
            'licencia_requerida' => $request->licencia_requerida,
            'capacidad_carga' => $capacidadCarga,
            'unidad_medida_carga_id' => $unidadMedidaCargaId,
            'disponible' => true,
            'estado' => 'activo',
        ]);

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo creado correctamente');
    }

    public function edit($id)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($id);
            $tiposTransporte = TipoTransporte::all();
            $tamanosVehiculo = TamanoVehiculo::all();
            $unidadesMedida = UnidadMedida::all();
            
            return view('vehiculos.edit', compact('vehiculo', 'tiposTransporte', 'tamanosVehiculo', 'unidadesMedida'));
        } catch (\Exception $e) {
            \Log::error('Error en VehiculoController@edit: ' . $e->getMessage(), [
                'vehiculo_id' => $id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('vehiculos.index')
                ->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        
        $request->validate([
            'placa' => 'required|string|max:50|unique:vehiculos,placa,' . $vehiculo->id,
            'tipo_transporte_id' => 'nullable|exists:tipos_transporte,id',
            'tamano_vehiculo_id' => 'nullable|exists:tamano_vehiculos,id',
            'licencia_requerida' => 'required|in:A,B,C',
            'capacidad_carga' => 'nullable|numeric|min:0',
            'unidad_medida_carga_id' => 'nullable|exists:unidades_medida,id',
            'disponible' => 'nullable|boolean',
            'estado' => 'nullable|in:activo,mantenimiento,inactivo',
        ]);

        // Convertir cadenas vacías a null para campos nullable
        $tipoTransporteId = $request->tipo_transporte_id === '' ? null : $request->tipo_transporte_id;
        $tamanoVehiculoId = $request->tamano_vehiculo_id === '' ? null : $request->tamano_vehiculo_id;
        $capacidadCarga = $request->capacidad_carga === '' || $request->capacidad_carga === null ? null : $request->capacidad_carga;
        $unidadMedidaCargaId = $request->unidad_medida_carga_id === '' ? null : $request->unidad_medida_carga_id;

        $vehiculo->update([
            'placa' => $request->placa,
            'tipo_transporte_id' => $tipoTransporteId,
            'tamano_vehiculo_id' => $tamanoVehiculoId,
            'licencia_requerida' => $request->licencia_requerida,
            'capacidad_carga' => $capacidadCarga,
            'unidad_medida_carga_id' => $unidadMedidaCargaId,
            'disponible' => $request->has('disponible'),
            'estado' => $request->estado ?? 'activo',
        ]);

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo actualizado correctamente');
    }

    public function destroy(Vehiculo $vehiculo)
    {
        try {
            // Verificar si tiene asignaciones activas
            $tieneAsignaciones = \DB::table('envio_asignaciones')
                ->where('vehiculo_id', $vehiculo->id)
                ->whereIn('estado', ['asignado', 'aceptado', 'en_transito'])
                ->exists();
            
            if ($tieneAsignaciones) {
                return redirect()->route('vehiculos.index')
                    ->with('error', 'No se puede eliminar el vehículo porque tiene envíos activos asignados.');
            }
            
            // Eliminar asignaciones completadas/canceladas
            \DB::table('envio_asignaciones')->where('vehiculo_id', $vehiculo->id)->delete();
            
            $vehiculo->delete();
            return redirect()->route('vehiculos.index')->with('success', 'Vehículo eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('vehiculos.index')
                ->with('error', 'Error al eliminar el vehículo: ' . $e->getMessage());
        }
    }
}
