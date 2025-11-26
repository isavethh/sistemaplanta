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
        $envios = Envio::with(['almacenDestino', 'productos.producto', 'asignacion'])
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $envios
        ]);
    }

    /**
     * Crear un nuevo envío y sincronizar con Node.js
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'almacen_destino_id' => 'required|exists:almacens,id',
            'categoria' => 'nullable|string',
            'fecha_estimada_entrega' => 'required|date',
            'hora_estimada' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'productos' => 'required|array',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|numeric|min:0',
            'productos.*.peso_kg' => 'required|numeric|min:0',
            'productos.*.precio' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Crear envío
            $envio = Envio::create([
                'codigo' => $this->generarCodigoEnvio(),
                'almacen_destino_id' => $validated['almacen_destino_id'],
                'categoria' => $validated['categoria'] ?? 'general',
                'fecha_creacion' => now(),
                'fecha_estimada_entrega' => $validated['fecha_estimada_entrega'],
                'hora_estimada' => $validated['hora_estimada'],
                'estado' => 'pendiente',
                'observaciones' => $validated['observaciones'],
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
                
                EnvioProducto::create([
                    'envio_id' => $envio->id,
                    'producto_id' => $producto['producto_id'],
                    'cantidad' => $producto['cantidad'],
                    'peso_kg' => $producto['peso_kg'],
                    'precio_unitario' => $producto['precio'],
                    'total_peso' => $producto['cantidad'] * $producto['peso_kg'],
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

            // Generar QR
            $qrData = [
                'type' => 'ENVIO',
                'codigo' => $envio->codigo,
                'envio_id' => $envio->id,
                'url' => url("/envios/{$envio->id}")
            ];

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

            // Sincronizar con Node.js backend
            $this->sincronizarConNodeJS($envio);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Envío creado exitosamente',
                'data' => $envio->load(['almacenDestino', 'productos.producto']),
                'qr_code' => 'data:image/png;base64,' . $qrCode
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
    private function generarCodigoEnvio()
    {
        $fecha = now()->format('ymd');
        $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        return "ENV-{$fecha}-{$random}";
    }
}


