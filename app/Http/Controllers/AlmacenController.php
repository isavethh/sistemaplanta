<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Si es propietario, mostrar solo sus almacenes
        if ($user->esPropietario()) {
            $almacenes = Almacen::with('usuarioAlmacen')
                ->where('usuario_almacen_id', $user->id)
                ->where('es_planta', false)
                ->get();
        } else {
            // Admin u otros roles ven todos los almacenes
        $almacenes = Almacen::with('usuarioAlmacen')->get();
        }
        
        return view('almacenes.index', compact('almacenes'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Si es propietario, pre-llenar datos
        $almacen = new Almacen();
        if ($user->esPropietario()) {
            $almacen->latitud = -17.8146; // Santa Cruz por defecto
            $almacen->longitud = -63.1561;
            $almacen->activo = true;
        }
        
        return view('almacenes.create', compact('almacen'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'direccion_completa' => 'nullable|string',
        ]);

        // Si es propietario, asignar automáticamente
        if ($user->esPropietario()) {
            $validated['usuario_almacen_id'] = $user->id;
            $validated['es_planta'] = false;
            // Si no viene latitud/longitud, usar coordenadas por defecto de Santa Cruz
            $validated['latitud'] = $validated['latitud'] ?? -17.8146;
            $validated['longitud'] = $validated['longitud'] ?? -63.1561;
        } else {
        $validated['activo'] = $request->has('activo');
        $validated['es_planta'] = $request->has('es_planta');
        }
        
        $validated['activo'] = $validated['activo'] ?? true;
        
        Almacen::create($validated);
        return redirect()->route('almacenes.index')->with('success', 'Almacén creado exitosamente.');
    }

    public function show($id)
    {
        $user = auth()->user();
        
        $almacen = Almacen::with('usuarioAlmacen')->findOrFail($id);
        
        // Si es propietario, verificar que solo pueda ver sus propios almacenes
        if ($user->esPropietario()) {
            if ($almacen->usuario_almacen_id != $user->id) {
                abort(403, 'No tienes permiso para ver este almacén.');
            }
        }
        
        return view('almacenes.show', compact('almacen'));
    }

    public function edit($id)
    {
        $user = auth()->user();
        
        $almacen = Almacen::findOrFail($id);
        
        // Si es propietario, verificar que solo pueda editar sus propios almacenes
        if ($user->esPropietario()) {
            if ($almacen->usuario_almacen_id != $user->id) {
                abort(403, 'No tienes permiso para editar este almacén.');
            }
        }
        
        return view('almacenes.edit', compact('almacen'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        
        $almacen = Almacen::findOrFail($id);
        
        // Si es propietario, verificar que solo pueda editar sus propios almacenes
        if ($user->esPropietario()) {
            if ($almacen->usuario_almacen_id != $user->id) {
                abort(403, 'No tienes permiso para editar este almacén.');
            }
        }
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'direccion_completa' => 'nullable|string',
        ]);

        // Si es propietario, no permitir cambiar es_planta
        if (!$user->esPropietario()) {
        $validated['activo'] = $request->has('activo');
        $validated['es_planta'] = $request->has('es_planta');
        } else {
            // Mantener valores originales para propietarios
            $validated['activo'] = $almacen->activo;
            $validated['es_planta'] = false;
            // Si no viene latitud/longitud, usar valores por defecto
            $validated['latitud'] = $validated['latitud'] ?? -17.8146;
            $validated['longitud'] = $validated['longitud'] ?? -63.1561;
        }
        
        $almacen->update($validated);
        return redirect()->route('almacenes.index')->with('success', 'Almacén actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        
        $almacen = Almacen::findOrFail($id);
        
        // Si es propietario, verificar que solo pueda eliminar sus propios almacenes
        if ($user->esPropietario()) {
            if ($almacen->usuario_almacen_id != $user->id) {
                abort(403, 'No tienes permiso para eliminar este almacén.');
            }
        }
        
        try {
            // Verificar si tiene envíos asociados
            $tieneEnvios = \DB::table('envios')->where('almacen_destino_id', $almacen->id)->exists();
            
            if ($tieneEnvios) {
                return redirect()->route('almacenes.index')
                    ->with('error', 'No se puede eliminar el almacén porque tiene envíos asociados.');
            }
            
            // Verificar si tiene pedidos asociados
            $tienePedidos = \DB::table('pedidos_almacen')->where('almacen_id', $almacen->id)->exists();
            
            if ($tienePedidos) {
                return redirect()->route('almacenes.index')
                    ->with('error', 'No se puede eliminar el almacén porque tiene pedidos asociados.');
            }
            
            $almacen->delete();
            return redirect()->route('almacenes.index')->with('success', 'Almacén eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('almacenes.index')
                ->with('error', 'Error al eliminar el almacén: ' . $e->getMessage());
        }
    }

    public function inventario($id)
    {
        $user = auth()->user();
        
        $almacen = Almacen::findOrFail($id);
        
        // Si el usuario es almacen o propietario, verificar que solo pueda ver su propio almacén
        if ($user->hasRole('almacen') || $user->esAlmacen() || $user->esPropietario()) {
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
        $almacenesIds = [];
        
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
            $almacenesIds = [$almacenUsuario->id];
        }
        // Si el usuario es propietario, obtener TODOS sus almacenes
        elseif ($user->esPropietario()) {
            $almacenes = Almacen::where('usuario_almacen_id', $user->id)
                ->where('es_planta', false)
                ->where('activo', true)
                ->get();
            
            if ($almacenes->isEmpty()) {
                return redirect()->route('home')
                    ->with('error', 'No tienes almacenes asignados. Contacta al administrador.');
            }
            
            $almacenesIds = $almacenes->pluck('id')->toArray();
            // Si solo tiene un almacén, usar almacenUsuario para compatibilidad con la vista
            if ($almacenes->count() === 1) {
                $almacenUsuario = $almacenes->first();
            }
        } elseif (!$user->hasRole('admin')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
        
        // Si es admin, puede ver todos los almacenes (almacenUsuario será null, almacenesIds vacío)
        
        return view('almacenes.monitoreo', [
            'almacenUsuario' => $almacenUsuario,
            'almacenId' => $almacenUsuario ? $almacenUsuario->id : null,
            'almacenesIds' => $almacenesIds,
            'esPropietario' => $user->esPropietario()
        ]);
    }
}
