<?php

namespace App\Http\Controllers;

use App\Models\PedidoAlmacen;
use App\Models\PedidoProducto;
use App\Models\Almacen;
use App\Services\TrazabilidadProductosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoAlmacenController extends Controller
{
    protected $productosService;

    public function __construct(TrazabilidadProductosService $productosService)
    {
        $this->productosService = $productosService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->esPropietario()) {
            abort(403, 'Solo los propietarios pueden ver pedidos de almacén.');
        }

        $query = PedidoAlmacen::with(['almacen', 'productos', 'envio'])
            ->where('usuario_propietario_id', $user->id);

        // Filtro por estado si se proporciona
        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        $pedidos = $query->orderBy('created_at', 'desc')->get();

        return view('pedidos-almacen.index', compact('pedidos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->esPropietario()) {
            abort(403, 'Solo los propietarios pueden crear pedidos.');
        }

        // Obtener almacenes del propietario
        $almacenes = Almacen::where('usuario_almacen_id', $user->id)
            ->orWhereHas('pedidos', function($query) use ($user) {
                $query->where('usuario_propietario_id', $user->id);
            })
            ->where('activo', true)
            ->where('es_planta', false)
            ->get();

        // Si no tiene almacenes, permitir crear uno nuevo
        if ($almacenes->isEmpty()) {
            $almacenes = collect();
        }

        // Obtener productos desde API de Trazabilidad
        $productos = $this->productosService->obtenerProductos(['activo' => true]);

        return view('pedidos-almacen.create', compact('almacenes', 'productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->esPropietario()) {
            abort(403, 'Solo los propietarios pueden crear pedidos.');
        }

        $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'fecha_requerida' => 'required|date',
            'hora_requerida' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*.producto_nombre' => 'required|string',
            'productos.*.producto_codigo' => 'nullable|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.peso_unitario' => 'nullable|numeric|min:0',
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Obtener almacén
            $almacen = Almacen::findOrFail($request->almacen_id);

            // Generar código único
            $codigo = 'PED-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -6));

            // Crear pedido
            $pedido = PedidoAlmacen::create([
                'codigo' => $codigo,
                'almacen_id' => $request->almacen_id,
                'usuario_propietario_id' => $user->id,
                'fecha_requerida' => $request->fecha_requerida,
                'hora_requerida' => $request->hora_requerida,
                'estado' => 'pendiente',
                'latitud' => $almacen->latitud ?? -17.8146,
                'longitud' => $almacen->longitud ?? -63.1561,
                'direccion_completa' => $almacen->direccion_completa ?? $almacen->nombre,
                'observaciones' => $request->observaciones,
            ]);

            // Crear productos del pedido
            foreach ($request->productos as $prod) {
                $pesoUnitario = floatval($prod['peso_unitario'] ?? 0);
                $precioUnitario = floatval($prod['precio_unitario'] ?? 0);
                $cantidad = intval($prod['cantidad']);

                // Si el peso unitario es mayor a 10, probablemente está en gramos, convertir a kg
                if ($pesoUnitario > 10) {
                    $pesoUnitario = $pesoUnitario / 1000;
                }

                PedidoProducto::create([
                    'pedido_almacen_id' => $pedido->id,
                    'producto_nombre' => $prod['producto_nombre'],
                    'producto_codigo' => $prod['producto_codigo'] ?? null,
                    'cantidad' => $cantidad,
                    'peso_unitario' => round($pesoUnitario, 2), // Redondear a 2 decimales
                    'precio_unitario' => round($precioUnitario, 2),
                    'total_peso' => round($pesoUnitario * $cantidad, 2),
                    'total_precio' => round($precioUnitario * $cantidad, 2),
                ]);
            }

            // Enviar automáticamente a Trazabilidad
            $pedido->enviarATrazabilidad();

            DB::commit();

            Log::info('Pedido de almacén creado y enviado a Trazabilidad', [
                'pedido_id' => $pedido->id,
                'codigo' => $pedido->codigo,
                'usuario_id' => $user->id,
            ]);

            return redirect()->route('pedidos-almacen.index')
                ->with('success', 'Pedido creado exitosamente y enviado a Trazabilidad.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando pedido de almacén', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()
                ->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        
        if (!$user->esPropietario()) {
            abort(403, 'Solo los propietarios pueden ver pedidos.');
        }

        $pedido = PedidoAlmacen::with(['almacen', 'productos', 'envio', 'propietario'])
            ->where('usuario_propietario_id', $user->id)
            ->findOrFail($id);

        return view('pedidos-almacen.show', compact('pedido'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        
        if (!$user->esPropietario()) {
            abort(403, 'Solo los propietarios pueden editar pedidos.');
        }

        $pedido = PedidoAlmacen::with(['productos', 'almacen'])
            ->where('usuario_propietario_id', $user->id)
            ->where('estado', 'pendiente')
            ->findOrFail($id);

        $almacenes = Almacen::where('usuario_almacen_id', $user->id)
            ->where('activo', true)
            ->where('es_planta', false)
            ->get();

        $productos = $this->productosService->obtenerProductos(['activo' => true]);

        return view('pedidos-almacen.edit', compact('pedido', 'almacenes', 'productos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        
        if (!$user->esPropietario()) {
            abort(403, 'Solo los propietarios pueden editar pedidos.');
        }

        $pedido = PedidoAlmacen::where('usuario_propietario_id', $user->id)
            ->where('estado', 'pendiente')
            ->findOrFail($id);

        $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'fecha_requerida' => 'required|date',
            'hora_requerida' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*.producto_nombre' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'observaciones' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $almacen = Almacen::findOrFail($request->almacen_id);

            $pedido->update([
                'almacen_id' => $request->almacen_id,
                'fecha_requerida' => $request->fecha_requerida,
                'hora_requerida' => $request->hora_requerida,
                'latitud' => $almacen->latitud ?? -17.8146,
                'longitud' => $almacen->longitud ?? -63.1561,
                'direccion_completa' => $almacen->direccion_completa ?? $almacen->nombre,
                'observaciones' => $request->observaciones,
            ]);

            // Eliminar productos antiguos
            $pedido->productos()->delete();

            // Crear nuevos productos
            foreach ($request->productos as $prod) {
                $pesoUnitario = floatval($prod['peso_unitario'] ?? 0);
                $precioUnitario = floatval($prod['precio_unitario'] ?? 0);
                $cantidad = intval($prod['cantidad']);

                // Si el peso unitario es mayor a 10, probablemente está en gramos, convertir a kg
                if ($pesoUnitario > 10) {
                    $pesoUnitario = $pesoUnitario / 1000;
                }

                PedidoProducto::create([
                    'pedido_almacen_id' => $pedido->id,
                    'producto_nombre' => $prod['producto_nombre'],
                    'producto_codigo' => $prod['producto_codigo'] ?? null,
                    'cantidad' => $cantidad,
                    'peso_unitario' => round($pesoUnitario, 2), // Redondear a 2 decimales
                    'precio_unitario' => round($precioUnitario, 2),
                    'total_peso' => round($pesoUnitario * $cantidad, 2),
                    'total_precio' => round($precioUnitario * $cantidad, 2),
                ]);
            }

            DB::commit();

            return redirect()->route('pedidos-almacen.show', $pedido->id)
                ->with('success', 'Pedido actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando pedido', [
                'pedido_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()
                ->with('error', 'Error al actualizar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        
        if (!$user->esPropietario()) {
            abort(403, 'Solo los propietarios pueden eliminar pedidos.');
        }

        $pedido = PedidoAlmacen::where('usuario_propietario_id', $user->id)
            ->where('estado', 'pendiente')
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $pedido->productos()->delete();
            $pedido->delete();

            DB::commit();

            return redirect()->route('pedidos-almacen.index')
                ->with('success', 'Pedido eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando pedido', [
                'pedido_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al eliminar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Ver seguimiento del pedido
     */
    public function seguimiento(string $id)
    {
        $user = Auth::user();
        
        if (!$user->esPropietario()) {
            abort(403, 'Solo los propietarios pueden ver seguimiento.');
        }

        $pedido = PedidoAlmacen::with(['almacen', 'productos', 'envio.asignacion.vehiculo', 'envio.productos'])
            ->where('usuario_propietario_id', $user->id)
            ->findOrFail($id);

        return view('pedidos-almacen.seguimiento', compact('pedido'));
    }
}
