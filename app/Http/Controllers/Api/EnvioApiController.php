<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use App\Models\EnvioAsignacion;
use App\Models\EnvioProducto;
use App\Models\CodigoQR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EnvioApiController extends Controller
{
    private $nodeApiUrl;

    public function __construct()
    {
        $this->nodeApiUrl = env('NODE_API_URL', 'http://localhost:3000/api');
    }

    /**
     * Obtener todos los envíos
     */
    public function index()
    {
        try {
            $envios = Envio::with(['almacenDestino', 'productos', 'asignacion'])
                ->orderBy('fecha_creacion', 'desc')
                ->get()
                ->map(function($envio) {
                    // Agregar flag para identificar si es asignación múltiple
                    // Un envío es parte de asignación múltiple si tiene la misma fecha_asignacion
                    // y el mismo transportista/vehiculo que otros envíos
                    $asignacion = $envio->asignacion;
                    if ($asignacion) {
                        $mismoDia = EnvioAsignacion::whereHas('vehiculo', function($q) use ($asignacion) {
                                $vehiculoTransportistaId = $asignacion->vehiculo ? $asignacion->vehiculo->transportista_id : null;
                                if ($vehiculoTransportistaId) {
                                    $q->where('transportista_id', $vehiculoTransportistaId);
                                }
                            })
                            ->where('vehiculo_id', $asignacion->vehiculo_id)
                            ->whereDate('fecha_asignacion', $asignacion->fecha_asignacion)
                            ->where('id', '!=', $asignacion->id)
                            ->exists();
                        
                        $envio->es_asignacion_multiple = $mismoDia;
                        $envio->tipo_asignacion = $mismoDia ? 'multiple' : 'normal';
                    } else {
                        $envio->es_asignacion_multiple = false;
                        $envio->tipo_asignacion = 'normal';
                    }
                    
                    return $envio;
                });

            return response()->json([
                'success' => true,
                'data' => $envios
            ]);
        } catch (\Exception $e) {
            \Log::error("❌ Error en EnvioApiController::index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener envíos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo envío y sincronizar con Node.js
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'almacen_destino_id' => 'required|exists:almacenes,id',
            'categoria' => 'nullable|string',
            'fecha_estimada_entrega' => 'required|date',
            'hora_estimada' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'productos' => 'required|array',
            'productos.*.producto_id' => 'nullable|exists:productos,id',
            'productos.*.producto_nombre' => 'nullable|string',
            'productos.*.cantidad' => 'required|numeric|min:0',
            'productos.*.peso_kg' => 'required|numeric|min:0',
            'productos.*.precio' => 'required|numeric|min:0',
            'origen' => 'nullable|string|in:trazabilidad,manual',
            'pedido_trazabilidad_id' => 'nullable|integer',
            'numero_pedido_trazabilidad' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Preparar observaciones con información de Trazabilidad si viene
            $observaciones = $validated['observaciones'] ?? '';
            if (($validated['origen'] ?? '') === 'trazabilidad' && !empty($validated['numero_pedido_trazabilidad'])) {
                $observaciones = "ORIGEN: TRAZABILIDAD\n" .
                                "Pedido: {$validated['numero_pedido_trazabilidad']}\n" .
                                ($observaciones ? "\n{$observaciones}" : '');
            }

            // Generar código según origen
            $codigo = ($validated['origen'] ?? '') === 'trazabilidad' 
                ? $this->generarCodigoEnvio('TRAZ')
                : $this->generarCodigoEnvio();

            // Crear envío
            $envio = Envio::create([
                'codigo' => $codigo,
                'almacen_destino_id' => $validated['almacen_destino_id'],
                'categoria' => $validated['categoria'] ?? 'general',
                'fecha_creacion' => now(),
                'fecha_estimada_entrega' => $validated['fecha_estimada_entrega'],
                'hora_estimada' => $validated['hora_estimada'] ?? null,
                'estado' => 'pendiente',
                'observaciones' => $observaciones,
                'total_cantidad' => 0,
                'total_peso' => 0,
                'total_precio' => 0,
            ]);

            // Agregar productos
            $totalCantidad = 0;
            $totalPeso = 0;
            $totalPrecio = 0;

            foreach ($validated['productos'] as $producto) {
                $totalProducto = $producto['cantidad'] * $producto['precio'];

                // Usar producto_nombre si está disponible, si no buscar por producto_id
                $productoNombre = $producto['producto_nombre'] ?? null;
                if (!$productoNombre && isset($producto['producto_id'])) {
                    $productoModel = \App\Models\Producto::find($producto['producto_id']);
                    $productoNombre = $productoModel ? $productoModel->nombre : 'Producto';
                }

                EnvioProducto::create([
                    'envio_id' => $envio->id,
                    'producto_nombre' => $productoNombre,
                    'cantidad' => $producto['cantidad'],
                    'peso_unitario' => $producto['peso_kg'] ?? 0,
                    'precio_unitario' => $producto['precio'],
                    'total_peso' => $producto['cantidad'] * ($producto['peso_kg'] ?? 0),
                    'total_precio' => $totalProducto,
                ]);

                $totalCantidad += $producto['cantidad'];
                $totalPeso += $producto['cantidad'] * $producto['peso_kg'];
                $totalPrecio += $totalProducto;
            }

            // Actualizar totales
            $envio->update([
                'total_cantidad' => $totalCantidad,
                'total_peso' => $totalPeso,
                'total_precio' => $totalPrecio,
            ]);

            // Generar QR (opcional - puede fallar si el paquete no está instalado)
            $qrCode = null;
            try {
                $qrData = [
                    'type' => 'ENVIO',
                    'codigo' => $envio->codigo,
                    'envio_id' => $envio->id,
                    'url' => url("/envios/{$envio->id}")
                ];

                if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                    $qrCode = base64_encode(QrCode::format('png')
                        ->size(300)
                        ->generate(json_encode($qrData)));

                    // Guardar QR en la base de datos
                    CodigoQR::create([
                        'codigo' => $envio->codigo,
                        'tipo' => 'envio',
                        'referencia_id' => $envio->id,
                        'qr_image' => $qrCode,
                        'datos_json' => json_encode($qrData),
                    ]);
                }
            } catch (\Exception $qrException) {
                // QR generation failed, but we can still continue
                \Log::warning('QR generation failed: ' . $qrException->getMessage());
            }

            // Sincronizar con Node.js backend (opcional)
            try {
                $this->sincronizarConNodeJS($envio);
            } catch (\Exception $nodeException) {
                \Log::warning('Node.js sync failed: ' . $nodeException->getMessage());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Envío creado exitosamente',
                'data' => $envio->load(['almacenDestino', 'productos']),
                'qr_code' => $qrCode ? 'data:image/png;base64,' . $qrCode : null
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear envío: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un envío específico
     */
    public function show($id)
    {
        $envio = Envio::with([
            'almacenDestino',
            'productos.producto.categoria',
            'asignacion.transportista.usuario',
            'asignacion.vehiculo'
        ])->find($id);

        if (!$envio) {
            return response()->json([
                'success' => false,
                'message' => 'Envío no encontrado'
            ], 404);
        }

        // Obtener QR
        $qr = CodigoQR::where('referencia_id', $id)
            ->where('tipo', 'envio')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $envio,
            'qr_code' => $qr ? 'data:image/png;base64,' . $qr->qr_image : null
        ]);
    }

    /**
     * Obtener envío por código QR
     */
    public function getByQrCode($codigo)
    {
        $qr = CodigoQR::where('codigo', $codigo)
            ->where('tipo', 'envio')
            ->first();

        if (!$qr) {
            return response()->json([
                'success' => false,
                'message' => 'Código QR no encontrado'
            ], 404);
        }

        $envio = Envio::with([
            'almacenDestino',
            'productos.producto',
            'asignacion'
        ])->find($qr->referencia_id);

        return response()->json([
            'success' => true,
            'data' => $envio,
            'qr_code' => 'data:image/png;base64,' . $qr->qr_image
        ]);
    }

    /**
     * Actualizar estado del envío
     */
    public function updateEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,asignado,en_transito,entregado,cancelado'
        ]);

        $envio = Envio::find($id);

        if (!$envio) {
            return response()->json([
                'success' => false,
                'message' => 'Envío no encontrado'
            ], 404);
        }

        $estadoAnterior = $envio->estado;
        $envio->estado = $validated['estado'];

        // Actualizar fechas según el estado
        if ($validated['estado'] === 'en_transito' && !$envio->fecha_inicio_transito) {
            $envio->fecha_inicio_transito = now();
        }

        if ($validated['estado'] === 'entregado' && !$envio->fecha_entrega) {
            $envio->fecha_entrega = now();
        }

        $envio->save();

        // Sincronizar con Node.js
        $this->sincronizarEstadoConNodeJS($envio);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'data' => $envio,
            'estado_anterior' => $estadoAnterior
        ]);
    }

    /**
     * Iniciar envío (marcar como en tránsito)
     */
    public function iniciar($id)
    {
        $envio = Envio::find($id);

        if (!$envio) {
            return response()->json([
                'success' => false,
                'message' => 'Envío no encontrado'
            ], 404);
        }

        if ($envio->estado !== 'asignado') {
            return response()->json([
                'success' => false,
                'message' => 'El envío debe estar asignado para iniciarse'
            ], 400);
        }

        $envio->iniciarTransito();

        // Sincronizar con Node.js para iniciar simulación
        $this->iniciarSimulacionNodeJS($envio);

        return response()->json([
            'success' => true,
            'message' => 'Envío iniciado exitosamente',
            'data' => $envio
        ]);
    }

    /**
     * Sincronizar envío con Node.js backend
     */
    private function sincronizarConNodeJS($envio)
    {
        try {
            $response = Http::timeout(5)->post("{$this->nodeApiUrl}/envios/sync", [
                'laravel_envio_id' => $envio->id,
                'codigo' => $envio->codigo,
                'almacen_destino_id' => $envio->almacen_destino_id,
                'estado' => $envio->estado,
                'fecha_programada' => $envio->fecha_estimada_entrega,
                'hora_estimada_llegada' => $envio->hora_estimada,
                'notas' => $envio->observaciones,
            ]);

            if ($response->successful()) {
                \Log::info('Envío sincronizado con Node.js', ['envio_id' => $envio->id]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al sincronizar con Node.js', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sincronizar estado con Node.js
     */
    private function sincronizarEstadoConNodeJS($envio)
    {
        try {
            Http::timeout(5)->put("{$this->nodeApiUrl}/envios/{$envio->codigo}/estado", [
                'estado_nombre' => $envio->estado
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al sincronizar estado con Node.js', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Iniciar simulación en Node.js
     */
    private function iniciarSimulacionNodeJS($envio)
    {
        try {
            $response = Http::timeout(5)->post("{$this->nodeApiUrl}/envios/{$envio->codigo}/simular-movimiento");

            if ($response->successful()) {
                \Log::info('Simulación iniciada en Node.js', ['envio_id' => $envio->id]);
            }
        } catch (\Exception $e) {
            \Log::error('Error al iniciar simulación', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generar código único para envío
     */
    private function generarCodigoEnvio(string $prefijo = 'ENV'): string
    {
        $fecha = now()->format('ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        return "{$prefijo}-{$fecha}-{$random}";
    }
}


