<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index()
    {
        $almacenes = Almacen::with('usuarioAlmacen')->get();
        return view('almacenes.index', compact('almacenes'));
    }

    public function create()
    {
        return view('almacenes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'direccion_completa' => 'nullable|string',
        ]);

        $validated['activo'] = $request->has('activo');
        $validated['es_planta'] = $request->has('es_planta');
        
        Almacen::create($validated);
        return redirect()->route('almacenes.index')->with('success', 'Almacén creado exitosamente.');
    }

    public function edit(Almacen $almacen)
    {
        return view('almacenes.edit', compact('almacen'));
    }

    public function update(Request $request, Almacen $almacen)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'direccion_completa' => 'nullable|string',
        ]);

        $validated['activo'] = $request->has('activo');
        $validated['es_planta'] = $request->has('es_planta');
        
        $almacen->update($validated);
        return redirect()->route('almacenes.index')->with('success', 'Almacén actualizado exitosamente.');
    }

    public function destroy(Almacen $almacen)
    {
        try {
            // Verificar si tiene envíos asociados
            $tieneEnvios = \DB::table('envios')->where('almacen_destino_id', $almacen->id)->exists();
            
            if ($tieneEnvios) {
                return redirect()->route('almacenes.index')
                    ->with('error', 'No se puede eliminar el almacén porque tiene envíos asociados.');
            }
            
            // Verificar si tiene usuario asociado
            $tieneUsuario = \DB::table('users')->where('almacen_id', $almacen->id)->exists();
            
            if ($tieneUsuario) {
                return redirect()->route('almacenes.index')
                    ->with('error', 'No se puede eliminar el almacén porque tiene usuarios asociados.');
            }
            
            $almacen->delete();
            return redirect()->route('almacenes.index')->with('success', 'Almacén eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('almacenes.index')
                ->with('error', 'Error al eliminar el almacén: ' . $e->getMessage());
        }
    }

    public function inventario(Almacen $almacen)
    {
        $user = auth()->user();
        
        // Si el usuario es almacen, verificar que solo pueda ver su propio almacén
        if ($user->hasRole('almacen') || $user->esAlmacen()) {
            $almacenUsuario = Almacen::where('usuario_almacen_id', $user->id)
                ->where('id', $almacen->id)
                ->first();
            
            if (!$almacenUsuario) {
                abort(403, 'No tienes permiso para ver el inventario de este almacén.');
            }
        }
        
        $inventario = $almacen->inventario()->get();
        return view('almacenes.inventario', compact('almacen', 'inventario'));
    }

    public function monitoreo()
    {
        $user = auth()->user();
        $almacenUsuario = null;
        
        // Si el usuario es almacen, obtener su almacén asignado
        if ($user->hasRole('almacen')) {
            $almacenUsuario = Almacen::where('usuario_almacen_id', $user->id)
                ->where('es_planta', false)
                ->where('activo', true)
                ->first();
            
            if (!$almacenUsuario) {
                return redirect()->route('home')
                    ->with('error', 'No tienes un almacén asignado. Contacta al administrador.');
            }
        } elseif (!$user->hasRole('admin')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
        
        return view('almacenes.monitoreo', [
            'almacenUsuario' => $almacenUsuario,
            'almacenId' => $almacenUsuario ? $almacenUsuario->id : null
        ]);
    }
}
