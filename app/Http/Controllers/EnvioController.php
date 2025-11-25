<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Almacen;
use App\Models\Direccion;
use App\Models\TipoEmpaque;
use App\Models\UnidadMedida;
use App\Models\EnvioProducto;
use App\Models\Vehiculo;
use App\Models\User;
use Illuminate\Http\Request;

class EnvioController extends Controller
{
    public function index()
    {
        $envios = Envio::with(['almacenDestino', 'productos', 'asignacion.transportista', 'asignacion.vehiculo'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('envios.index', compact('envios'));
    }

    public function create()
    {
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
        ]);

        // Generar código único para el envío
        $codigo = 'ENV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

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
            ]);
        }

        // Actualizar totales del envío
        $envio->calcularTotales();

        return redirect()->route('envios.index')->with('success', 'Envío creado exitosamente desde la Planta');
    }

    public function show(Envio $envio)
    {
        $planta = Almacen::where('es_planta', true)->first();
        $envio->load(['productos', 'almacenDestino', 'asignacion.transportista', 'asignacion.vehiculo']);
        return view('envios.show', compact('envio', 'planta'));
    }

    public function edit(Envio $envio)
    {
        $planta = Almacen::where('es_planta', true)->first();
        $almacenes = Almacen::where('activo', true)->where('es_planta', false)->get();
        $tiposEmpaque = TipoEmpaque::all();
        $unidadesMedida = UnidadMedida::all();
        $envio->load('productos');
        
        return view('envios.edit', compact('envio', 'planta', 'almacenes', 'tiposEmpaque', 'unidadesMedida'));
    }

    public function update(Request $request, Envio $envio)
    {
        $request->validate([
            'almacen_destino_id' => 'required|exists:almacenes,id',
            'categoria' => 'required|in:Verduras,Frutas',
        ]);

        $envio->update($request->except('productos'));
        
        if ($request->has('productos')) {
            $envio->productos()->delete();
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
                ]);
            }
            
            $envio->calcularTotales();
        }

        return redirect()->route('envios.index')->with('success', 'Envío actualizado exitosamente');
    }

    public function destroy(Envio $envio)
    {
        $envio->delete();
        return redirect()->route('envios.index')->with('success', 'Envío eliminado exitosamente');
    }

    public function tracking(Envio $envio)
    {
        $planta = Almacen::where('es_planta', true)->first();
        $envio->load(['almacenDestino', 'productos', 'asignacion.transportista', 'asignacion.vehiculo']);
        return view('envios.tracking', compact('envio', 'planta'));
    }

    public function actualizarEstado(Request $request, Envio $envio)
    {
        $envio->update(['estado' => $request->estado]);
        return response()->json(['success' => true, 'message' => 'Estado actualizado']);
    }
}
