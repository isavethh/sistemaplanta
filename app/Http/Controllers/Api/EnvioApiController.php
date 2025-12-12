<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use App\Models\EnvioAsignacion;
use App\Models\EnvioProducto;
use App\Models\CodigoQR;
use App\Models\Producto;
use App\Models\Categoria;
use App\Services\PropuestaVehiculosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
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
        // Log de entrada para debugging
        Log::info('🔵 [EnvioApiController] Recibiendo solicitud de creación de envío', [
            'request_data' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            // Validación simplificada y más permisiva
            $validated = $request->validate([
                'almacen_destino_id' => 'required|exists:almacenes,id',
                'categoria' => 'nullable|string',
                'fecha_estimada_entrega' => 'required|date',
                'hora_estimada' => 'nullable|string',
                'observaciones' => 'nullable|string',
                'productos' => 'required|array|min:1',
                'productos.*.producto_id' => 'nullable',
                'productos.*.producto_nombre' => 'nullable|string',
                'productos.*.cantidad' => 'required|numeric|min:0.01',
                'productos.*.peso_kg' => 'nullable|numeric|min:0',
                'productos.*.precio' => 'required|numeric|min:0',
                'origen' => 'nullable|string|in:trazabilidad,manual',
                'pedido_trazabilidad_id' => 'nullable|integer',
                'numero_pedido_trazabilidad' => 'nullable|string',
            ], [
                'almacen_destino_id.required' => 'El almacén destino es requerido',
                'almacen_destino_id.exists' => 'El almacén destino no existe',
                'fecha_estimada_entrega.required' => 'La fecha estimada de entrega es requerida',
                'fecha_estimada_entrega.date' => 'La fecha estimada de entrega debe ser una fecha válida',
                'productos.required' => 'Debe incluir al menos un producto',
                'productos.min' => 'Debe incluir al menos un producto',
                'productos.*.cantidad.required' => 'La cantidad del producto es requerida',
                'productos.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
                'productos.*.precio.required' => 'El precio del producto es requerido',
                'productos.*.precio.min' => 'El precio debe ser mayor o igual a 0',
            ]);

            // Validar que cada producto tenga al menos nombre o ID
            foreach ($validated['productos'] as $index => $producto) {
                if (empty($producto['producto_id']) && empty($producto['producto_nombre'])) {
                    throw new \Exception("El producto en la posición {$index} debe tener 'producto_nombre' o 'producto_id'");
                }
            }

            Log::info('✅ [EnvioApiController] Validación exitosa', [
                'productos_count' => count($validated['productos']),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ [EnvioApiController] Error de validación', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $e->errors())),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ [EnvioApiController] Error en validación personalizada', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . $e->getMessage()
            ], 422);
        }

        DB::beginTransaction();

        try {
            Log::info('🟢 [EnvioApiController] Iniciando creación de envío', [
                'almacen_destino_id' => $validated['almacen_destino_id'],
                'productos_count' => count($validated['productos']),
            ]);

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

            Log::info('🟢 [EnvioApiController] Código generado', ['codigo' => $codigo]);

            // Determinar estado inicial según origen
            $estadoInicial = 'pendiente';
            if (($validated['origen'] ?? '') === 'trazabilidad') {
                $estadoInicial = 'pendiente_aprobacion_trazabilidad';
            }

            // Crear envío
            $envio = Envio::create([
                'codigo' => $codigo,
                'almacen_destino_id' => $validated['almacen_destino_id'],
                'categoria' => $validated['categoria'] ?? 'general',
                'fecha_creacion' => now(),
                'fecha_estimada_entrega' => $validated['fecha_estimada_entrega'],
                'hora_estimada' => $validated['hora_estimada'] ?? null,
                'estado' => $estadoInicial,
                'observaciones' => $observaciones,
                'total_cantidad' => 0,
                'total_peso' => 0,
                'total_precio' => 0,
            ]);

            Log::info('✅ [EnvioApiController] Envío creado', ['envio_id' => $envio->id]);

            // Agregar productos
            $totalCantidad = 0;
            $totalPeso = 0;
            $totalPrecio = 0;

            foreach ($validated['productos'] as $index => $producto) {
                try {
                    Log::info("🟡 [EnvioApiController] Procesando producto {$index}", [
                        'producto_nombre' => $producto['producto_nombre'] ?? null,
                        'producto_id' => $producto['producto_id'] ?? null,
                        'cantidad' => $producto['cantidad'] ?? null,
                    ]);

                    $totalProducto = $producto['cantidad'] * $producto['precio'];

                    // Obtener o crear el producto en Planta
                    $productoNombre = !empty($producto['producto_nombre']) ? trim($producto['producto_nombre']) : null;
                    $productoId = $producto['producto_id'] ?? null;
                    $productoModel = null;

                    // Validar que tenemos al menos nombre o ID
                    if (!$productoNombre && !$productoId) {
                        throw new \Exception("El producto en la posición {$index} debe tener 'producto_nombre' o 'producto_id'");
                    }

                // Si viene producto_id, buscar por ID
                if ($productoId) {
                    $productoModel = Producto::find($productoId);
                    if ($productoModel) {
                        $productoNombre = $productoModel->nombre;
                    } else {
                        Log::warning('Producto ID no encontrado, se buscará por nombre', [
                            'producto_id' => $productoId,
                            'producto_nombre' => $productoNombre,
                        ]);
                    }
                }

                // Si no se encontró por ID y tenemos nombre, buscar o crear por nombre
                if (!$productoModel && $productoNombre) {
                    // Buscar producto existente por nombre
                    $productoModel = Producto::where('nombre', $productoNombre)->first();
                    
                    // Si no existe, crear el producto
                    if (!$productoModel) {
                        // Obtener categoría por defecto (general) o crear una si no existe
                        $categoria = Categoria::where('nombre', 'General')->first();
                        if (!$categoria) {
                            $categoria = Categoria::create([
                                'nombre' => 'General',
                            ]);
                        }

                        // Crear el producto
                        $productoModel = Producto::create([
                            'categoria_id' => $categoria->id,
                            'codigo' => 'TRAZ-' . strtoupper(substr(md5($productoNombre), 0, 8)),
                            'nombre' => $productoNombre,
                            'descripcion' => "Producto importado desde Trazabilidad: {$productoNombre}",
                            'peso_unitario' => $producto['peso_kg'] ?? 0,
                            'volumen_unitario' => 0,
                            'precio_base' => $producto['precio'] ?? 0,
                            'stock_minimo' => 0,
                            'activo' => true,
                        ]);

                        Log::info('Producto creado desde Trazabilidad', [
                            'producto_id' => $productoModel->id,
                            'nombre' => $productoNombre,
                            'envio_id' => $envio->id,
                        ]);
                    }
                }

                // Si aún no tenemos nombre, usar un valor por defecto
                if (!$productoNombre) {
                    $productoNombre = $productoModel ? $productoModel->nombre : 'Producto sin nombre';
                }

                // Validar que tenemos al menos el nombre del producto
                if (!$productoNombre || trim($productoNombre) === '') {
                    throw new \Exception("El nombre del producto es requerido para el producto en la posición del array");
                }

                // Crear el EnvioProducto con producto_id si está disponible
                EnvioProducto::create([
                    'envio_id' => $envio->id,
                    'producto_id' => $productoModel ? $productoModel->id : null,
                    'producto_nombre' => trim($productoNombre),
                    'cantidad' => (float) $producto['cantidad'],
                    'peso_unitario' => (float) ($producto['peso_kg'] ?? 0),
                    'precio_unitario' => (float) $producto['precio'],
                    'total_peso' => (float) ($producto['cantidad'] * ($producto['peso_kg'] ?? 0)),
                    'total_precio' => (float) $totalProducto,
                ]);

                    $totalCantidad += $producto['cantidad'];
                    $totalPeso += $producto['cantidad'] * ($producto['peso_kg'] ?? 0);
                    $totalPrecio += $totalProducto;

                    Log::info("✅ [EnvioApiController] Producto {$index} procesado exitosamente", [
                        'producto_nombre' => $productoNombre,
                        'producto_id' => $productoModel ? $productoModel->id : null,
                    ]);
                } catch (\Exception $e) {
                    Log::error("❌ [EnvioApiController] Error procesando producto {$index}", [
                        'error' => $e->getMessage(),
                        'producto_data' => $producto,
                    ]);
                    throw new \Exception("Error al procesar producto en posición {$index}: " . $e->getMessage());
                }
            }

            Log::info('✅ [EnvioApiController] Todos los productos procesados', [
                'total_cantidad' => $totalCantidad,
                'total_peso' => $totalPeso,
                'total_precio' => $totalPrecio,
            ]);

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

            // Si viene de Trazabilidad, generar propuesta de vehículos
            $propuestaGenerada = false;
            if (($validated['origen'] ?? '') === 'trazabilidad') {
                try {
                    $propuestaService = new PropuestaVehiculosService();
                    $propuesta = $propuestaService->calcularPropuestaVehiculos($envio);
                    $propuestaGenerada = true;
                    
                    Log::info('✅ [EnvioApiController] Propuesta de vehículos generada', [
                        'envio_id' => $envio->id,
                        'vehiculos_count' => count($propuesta['vehiculos_propuestos']),
                    ]);
                } catch (\Exception $propuestaException) {
                    Log::warning('⚠️ [EnvioApiController] Error al generar propuesta de vehículos: ' . $propuestaException->getMessage());
                    // No fallar el envío si falla la propuesta, solo loguear
                }
            }

            // Sincronizar con Node.js backend (opcional)
            try {
                $this->sincronizarConNodeJS($envio);
            } catch (\Exception $nodeException) {
                \Log::warning('Node.js sync failed: ' . $nodeException->getMessage());
            }

            DB::commit();

            $responseData = [
                'success' => true,
                'message' => 'Envío creado exitosamente',
                'data' => $envio->load(['almacenDestino', 'productos']),
                'qr_code' => $qrCode ? 'data:image/png;base64,' . $qrCode : null
            ];

            // Si viene de Trazabilidad, agregar información sobre la propuesta
            if (($validated['origen'] ?? '') === 'trazabilidad') {
                $responseData['estado'] = 'pendiente_aprobacion_trazabilidad';
                $responseData['mensaje'] = 'Envío creado. Debe ser aprobado por Trazabilidad antes de asignar transportista.';
                $responseData['propuesta_vehiculos_url'] = url("/api/envios/{$envio->id}/propuesta-vehiculos-pdf");
            }

            return response()->json($responseData, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear envío', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear envío: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? $e->getTraceAsString() : null
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
            'estado' => 'required|in:pendiente,pendiente_aprobacion_trazabilidad,asignado,en_transito,entregado,cancelado'
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

    /**
     * Obtener PDF de propuesta de vehículos para un envío
     * Endpoint para que Trazabilidad pueda descargar el documento
     */
    public function propuestaVehiculosPdf($id)
    {
        try {
            $envio = Envio::with(['almacenDestino', 'productos.producto', 'productos.tipoEmpaque'])
                ->find($id);

            if (!$envio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Envío no encontrado'
                ], 404);
            }

            // Verificar que el envío viene de Trazabilidad
            if (strpos($envio->observaciones ?? '', 'ORIGEN: TRAZABILIDAD') === false 
                && $envio->estado !== 'pendiente_aprobacion_trazabilidad') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este envío no requiere propuesta de vehículos'
                ], 400);
            }

            // Calcular propuesta de vehículos
            $propuestaService = new PropuestaVehiculosService();
            $propuesta = $propuestaService->calcularPropuestaVehiculos($envio);

            // Generar PDF
            $pdf = Pdf::loadView('envios.pdf.propuesta-vehiculos', compact('propuesta'));
            $pdf->setPaper('a4', 'portrait');

            $filename = 'propuesta-vehiculos-' . $envio->codigo . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error al generar PDF de propuesta de vehículos', [
                'envio_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar o rechazar propuesta de vehículos desde Trazabilidad
     * POST /api/envios/{id}/aprobar-rechazar
     * Body: { "accion": "aprobar" | "rechazar", "observaciones": "opcional" }
     */
    public function aprobarRechazarTrazabilidad(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'accion' => 'required|in:aprobar,rechazar',
                'observaciones' => 'nullable|string|max:1000'
            ]);

            $envio = Envio::find($id);

            if (!$envio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Envío no encontrado'
                ], 404);
            }

            // Verificar que el envío está en estado correcto
            if ($envio->estado !== 'pendiente_aprobacion_trazabilidad') {
                return response()->json([
                    'success' => false,
                    'message' => "El envío no está en estado 'pendiente_aprobacion_trazabilidad'. Estado actual: {$envio->estado}"
                ], 400);
            }

            DB::beginTransaction();

            if ($validated['accion'] === 'aprobar') {
                // Aprobar: cambiar estado a 'pendiente' para que continúe el flujo normal
                $envio->estado = 'pendiente';
                $mensaje = 'Propuesta de vehículos aprobada por Trazabilidad. El envío puede proceder con la asignación del transportista.';
                
                Log::info('✅ [EnvioApiController] Propuesta aprobada por Trazabilidad', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                ]);
            } else {
                // Rechazar: cambiar estado a 'cancelado'
                $envio->estado = 'cancelado';
                $mensaje = 'Propuesta de vehículos rechazada por Trazabilidad. El envío ha sido cancelado.';
                
                Log::info('❌ [EnvioApiController] Propuesta rechazada por Trazabilidad', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                ]);
            }

            // Agregar observaciones si vienen
            if (!empty($validated['observaciones'])) {
                $observacionesActuales = $envio->observaciones ?? '';
                $nuevaObservacion = "\n\nDECISIÓN TRAZABILIDAD (" . now()->format('d/m/Y H:i') . "):\n";
                $nuevaObservacion .= "Acción: " . strtoupper($validated['accion']) . "\n";
                $nuevaObservacion .= "Observaciones: " . $validated['observaciones'];
                $envio->observaciones = $observacionesActuales . $nuevaObservacion;
            }

            $envio->save();

            // Sincronizar estado con Node.js
            $this->sincronizarEstadoConNodeJS($envio);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                    'estado' => $envio->estado,
                    'accion' => $validated['accion']
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar aprobación/rechazo de Trazabilidad', [
                'envio_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }
}


