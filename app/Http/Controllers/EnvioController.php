<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Almacen;
use App\Models\TipoEmpaque;
use App\Models\UnidadMedida;
use App\Models\EnvioProducto;
use App\Models\Vehiculo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnvioController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Si el usuario es propietario, mostrar envíos relacionados con sus pedidos
        if ($user->esPropietario()) {
            $query = Envio::with(['almacenDestino', 'productos', 'asignacion.transportista', 'asignacion.vehiculo', 'pedidoAlmacen'])
                ->whereHas('pedidoAlmacen', function($q) use ($user) {
                    $q->where('usuario_propietario_id', $user->id);
                });
            
            // Filtro para envíos recibidos
            if ($request->has('tipo') && $request->tipo == 'recibidos') {
                $query->where('estado', 'entregado');
            }
            
            $envios = $query->orderBy('id', 'desc')->get();
        }
        // Si el usuario es transportista, mostrar solo sus envíos asignados
        else if ($user->hasRole('transportista')) {
            // Obtener envíos asignados directamente al transportista (cualquier vehículo puede ser usado)
            $envios = Envio::with(['almacenDestino', 'productos', 'asignacion.transportista', 'asignacion.vehiculo'])
                ->whereHas('asignacion', function($query) use ($user) {
                    $query->where('transportista_id', $user->id);
                })
                ->orderBy('id', 'desc') // Ordenar por ID para mostrar los más recientes primero
                ->get();
        } else {
            // Si es admin u otro rol, mostrar todos los envíos
            $envios = Envio::with(['almacenDestino', 'productos', 'asignacion.transportista', 'asignacion.vehiculo'])
                ->orderBy('id', 'desc') // Ordenar por ID para mostrar los más recientes primero
                ->get();
            
            // Corregir envíos inconsistentes: si están "asignado" pero no tienen asignación válida
            // Solo verificar que tenga asignación y vehículo, no verificar transportista (puede no estar cargado)
            foreach ($envios as $envio) {
                if ($envio->estado == 'asignado') {
                    // Recargar relaciones para asegurar que estén cargadas
                    $envio->load(['asignacion.vehiculo']);
                    
                    // Verificar que tenga asignación y vehículo
                    $tieneAsignacion = $envio->asignacion && $envio->asignacion->vehiculo;
                    
                    if (!$tieneAsignacion) {
                        // Solo corregir si realmente no hay asignación o vehículo
                        $envio->update(['estado' => 'pendiente']);
                        \Log::warning("⚠️ Envío {$envio->codigo} corregido: estado 'asignado' sin asignación o vehículo, cambiado a 'pendiente'");
                    } else {
                        // Verificar que el vehículo tenga transportista (solo loguear, no cambiar estado)
                        $vehiculo = $envio->asignacion->vehiculo;
                        if (!$vehiculo->transportista_id) {
                            \Log::warning("⚠️ Envío {$envio->codigo} tiene asignación pero el vehículo {$vehiculo->placa} no tiene transportista asignado");
                            // No cambiar el estado, solo advertir - el vehículo puede tener transportista pero no estar cargado
                        }
                    }
                }
            }
            
            // Recargar los envíos después de las correcciones, ordenados por ID
            $envios = Envio::with(['almacenDestino', 'productos', 'asignacion.transportista', 'asignacion.vehiculo'])
                ->orderBy('id', 'desc') // Ordenar por ID para mostrar los más recientes primero
                ->get();
        }
        
        return view('envios.index', compact('envios'));
    }

    public function create()
    {
        // Solo admin puede crear envíos
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Solo los administradores pueden crear envíos.');
        }
        
        // La planta (origen fijo)
        $planta = Almacen::where('es_planta', true)->first();
        
        // Almacenes destino (NO planta)
        $almacenes = Almacen::where('activo', true)->where('es_planta', false)->get();
        
        // Tipos de empaque y unidades de medida
        $tiposEmpaque = TipoEmpaque::all();
        $unidadesMedida = UnidadMedida::all();
        
        return view('envios.create', compact('planta', 'almacenes', 'tiposEmpaque', 'unidadesMedida'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'almacen_destino_id' => 'required|exists:almacenes,id',
            'fecha_estimada_entrega' => 'nullable|date',
            'hora_estimada' => 'nullable',
            'productos' => 'required|array|min:1',
            'productos.*.producto_nombre' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.peso_unitario' => 'required|numeric|min:0',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
            'productos.*.alto_producto_cm' => 'nullable|numeric|min:0',
            'productos.*.ancho_producto_cm' => 'nullable|numeric|min:0',
            'productos.*.largo_producto_cm' => 'nullable|numeric|min:0',
        ]);

        // Generar código único para el envío
        $codigo = 'ENV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        \Log::info("📝 Creando nuevo envío: {$codigo}");

        // La categoría será "Mixto" si hay productos de diferentes categorías
        $envio = Envio::create([
            'codigo' => $codigo,
            'almacen_destino_id' => $request->almacen_destino_id,
            'categoria' => 'Mixto', // Ahora permite mezclar productos
            'fecha_creacion' => now(),
            'fecha_estimada_entrega' => $request->fecha_estimada_entrega,
            'hora_estimada' => $request->hora_estimada,
            'estado' => 'pendiente',
            'observaciones' => $request->observaciones,
        ]);

        \Log::info("✅ Envío creado con ID: {$envio->id}, Estado: {$envio->estado}");

        // Crear productos del envío
        foreach ($request->productos as $prod) {
            EnvioProducto::create([
                'envio_id' => $envio->id,
                'producto_nombre' => $prod['producto_nombre'],
                'cantidad' => $prod['cantidad'],
                'peso_unitario' => $prod['peso_unitario'],
                'unidad_medida_id' => $prod['unidad_medida_id'] ?? null,
                'tipo_empaque_id' => $prod['tipo_empaque_id'] ?? null,
                'precio_unitario' => $prod['precio_unitario'],
                'total_peso' => $prod['cantidad'] * $prod['peso_unitario'],
                'total_precio' => $prod['cantidad'] * $prod['precio_unitario'],
                // Campos opcionales de medidas del producto
                'alto_producto_cm' => $prod['alto_producto_cm'] ?? null,
                'ancho_producto_cm' => $prod['ancho_producto_cm'] ?? null,
                'largo_producto_cm' => $prod['largo_producto_cm'] ?? null,
            ]);
        }

        // Actualizar totales del envío
        $envio->calcularTotales();

        \Log::info("📦 Productos agregados al envío {$codigo}. Total productos: " . $envio->productos()->count());

        return redirect()->route('envios.index')->with('success', "✅ Envío {$codigo} creado exitosamente y listo para asignación");
    }

    public function show(Envio $envio)
    {
        $user = Auth::user();
        
        // Si el usuario es transportista, verificar que el envío le pertenece
        if ($user->hasRole('transportista')) {
            $tieneAcceso = $envio->asignacion && $envio->asignacion->vehiculo && $envio->asignacion->vehiculo->transportista_id == $user->id;
            
            if (!$tieneAcceso) {
                abort(403, 'No tienes permiso para ver este envío.');
            }
        }
        
        $planta = Almacen::where('es_planta', true)->first();
        $envio->load(['productos', 'almacenDestino', 'asignacion.transportista', 'asignacion.vehiculo', 'pedidoAlmacen']);
        return view('envios.show', compact('envio', 'planta'));
    }

    public function edit(Envio $envio)
    {
        // Solo admin puede editar envíos
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Solo los administradores pueden editar envíos.');
        }
        
        $planta = Almacen::where('es_planta', true)->first();
        $almacenes = Almacen::where('activo', true)->where('es_planta', false)->get();
        $tiposEmpaque = TipoEmpaque::all();
        $unidadesMedida = UnidadMedida::all();
        $envio->load('productos');
        
        return view('envios.edit', compact('envio', 'planta', 'almacenes', 'tiposEmpaque', 'unidadesMedida'));
    }

    public function update(Request $request, Envio $envio)
    {
        // Solo admin puede actualizar envíos
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Solo los administradores pueden actualizar envíos.');
        }
        
        $request->validate([
            'almacen_destino_id' => 'required|exists:almacenes,id',
        ]);

        $envio->update([
            'almacen_destino_id' => $request->almacen_destino_id,
            'fecha_estimada_entrega' => $request->fecha_estimada_entrega,
            'hora_estimada' => $request->hora_estimada,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('envios.index')->with('success', 'Envío actualizado exitosamente');
    }

    public function destroy(Envio $envio)
    {
        // Solo admin puede eliminar envíos
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            abort(403, 'Solo los administradores pueden eliminar envíos.');
        }
        
        try {
            \DB::beginTransaction();
            
            // Eliminar notas de venta asociadas
            \DB::table('notas_venta')->where('envio_id', $envio->id)->delete();
            
            // Eliminar seguimiento/tracking
            \DB::table('envio_seguimiento')->where('envio_id', $envio->id)->delete();
            
            // Eliminar asignaciones (por si acaso no tiene cascade)
            \DB::table('envio_asignaciones')->where('envio_id', $envio->id)->delete();
            
            // Eliminar productos del envío (por si acaso no tiene cascade)
            \DB::table('envio_productos')->where('envio_id', $envio->id)->delete();
            
            // Finalmente eliminar el envío
            $envio->delete();
            
            \DB::commit();
            
            return redirect()->route('envios.index')->with('success', 'Envío eliminado exitosamente');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('envios.index')->with('error', 'Error al eliminar el envío: ' . $e->getMessage());
        }
    }

    public function tracking(Envio $envio)
    {
        $user = Auth::user();
        
        // Si el usuario es transportista, verificar que el envío le pertenece
        if ($user->hasRole('transportista')) {
            $tieneAcceso = $envio->asignacion && $envio->asignacion->vehiculo && $envio->asignacion->vehiculo->transportista_id == $user->id;
            
            if (!$tieneAcceso) {
                abort(403, 'No tienes permiso para ver el tracking de este envío.');
            }
        }
        
        $planta = Almacen::where('es_planta', true)->first();
        $envio->load(['almacenDestino', 'productos', 'asignacion.transportista', 'asignacion.vehiculo']);
        return view('envios.tracking', compact('envio', 'planta'));
    }

    public function actualizarEstado(Request $request, Envio $envio)
    {
        $envio->update(['estado' => $request->estado]);
        return response()->json(['success' => true, 'message' => 'Estado actualizado']);
    }

    /**
     * Aprobar envío desde Trazabilidad
     * Cambia el estado de pendiente_aprobacion_trazabilidad a pendiente para que pueda ser asignado
     */
    public function aprobarEnvioTrazabilidad(Request $request, Envio $envio)
    {
        $user = Auth::user();
        
        // Solo admin puede aprobar envíos de trazabilidad
        if (!$user->hasRole('admin')) {
            abort(403, 'Solo los administradores pueden aprobar envíos de trazabilidad.');
        }

        // Verificar que el envío esté en el estado correcto
        if ($envio->estado !== 'pendiente_aprobacion_trazabilidad') {
            return back()->with('error', "El envío no está en estado 'pendiente_aprobacion_trazabilidad'. Estado actual: {$envio->estado}");
        }

        DB::beginTransaction();
        try {
            // Cambiar estado a pendiente para que pueda ser asignado
            $envio->update([
                'estado' => 'pendiente',
            ]);

            DB::commit();

            \Log::info('Envío aprobado desde Trazabilidad', [
                'envio_id' => $envio->id,
                'codigo' => $envio->codigo,
                'usuario' => $user->name,
            ]);

            return redirect()->route('envios.index')
                ->with('success', "Envío {$envio->codigo} aprobado exitosamente. Ahora puede ser asignado a un transportista.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error aprobando envío de trazabilidad', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al aprobar el envío: ' . $e->getMessage());
        }
    }

}
