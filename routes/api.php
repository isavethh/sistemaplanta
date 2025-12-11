<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EnvioApiController;
use App\Http\Controllers\Api\AlmacenApiController;
use App\Http\Controllers\Api\UsuarioApiController;
use App\Http\Controllers\Api\RutaApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas API para integración con app móvil y otros sistemas
|
*/

// Ruta de prueba
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'Laravel API funcionando correctamente',
        'timestamp' => now()
    ]);
});

// Rutas públicas para Node.js - API Resources completas
// Nota: Usamos nombres diferentes para evitar conflictos con rutas web
Route::apiResource('almacenes', AlmacenApiController::class)
    ->names([
        'index' => 'api.almacenes.index',
        'show' => 'api.almacenes.show',
        'store' => 'api.almacenes.store',
        'update' => 'api.almacenes.update',
        'destroy' => 'api.almacenes.destroy'
    ]);
    
Route::apiResource('usuarios', UsuarioApiController::class)
    ->names([
        'index' => 'api.usuarios.index',
        'show' => 'api.usuarios.show',
        'store' => 'api.usuarios.store',
        'update' => 'api.usuarios.update',
        'destroy' => 'api.usuarios.destroy'
    ]);

// Rutas públicas (sin autenticación para la app móvil)
Route::prefix('public')->group(function () {
    // Login y lista de transportistas
    Route::get('/transportistas-login', [\App\Http\Controllers\Api\TransportistaController::class, 'getTransportistasLogin']);
    Route::post('/login-transportista', [\App\Http\Controllers\Api\TransportistaController::class, 'loginTransportista']);
});

// Rutas de transportistas (sin prefix para que funcione con /api/transportista/{id}/envios)
Route::get('/transportista/{id}/envios', [\App\Http\Controllers\Api\TransportistaController::class, 'getEnviosAsignados']);

// Rutas de notas de entrega (compatibles con Node.js backend)
Route::prefix('notas-venta')->group(function () {
    // Ruta para ver HTML de nota de entrega (compatible con Node.js: /api/notas-venta/{id}/html)
    Route::get('/{id}/html', [\App\Http\Controllers\NotaEntregaController::class, 'verHTML']);
});

Route::prefix('notas-entrega')->group(function () {
    // Ruta alternativa para ver HTML de nota de entrega
    Route::get('/{id}/html', [\App\Http\Controllers\NotaEntregaController::class, 'verHTML']);
});

// Rutas personalizadas de envíos (deben ir ANTES de apiResource para evitar conflictos)
Route::prefix('envios')->group(function () {
    // Ruta especial para QR (debe ir antes de /{id} para evitar conflictos)
    Route::get('/qr/{codigo}', [EnvioApiController::class, 'getByQrCode']);
    
    // Rutas de acciones específicas (usando {id} para diferenciarlas de {envio} de apiResource)
    Route::put('/{id}/estado', [EnvioApiController::class, 'updateEstado']);
    Route::post('/{id}/aceptar', [\App\Http\Controllers\Api\EnvioController::class, 'aceptar']);
    Route::post('/{id}/rechazar', [\App\Http\Controllers\Api\EnvioController::class, 'rechazar']);
    Route::post('/{id}/iniciar', [\App\Http\Controllers\Api\EnvioController::class, 'iniciar']);
    Route::post('/{id}/entregado', [\App\Http\Controllers\Api\EnvioController::class, 'marcarEntregado']);
    Route::post('/{id}/simular-movimiento', [\App\Http\Controllers\Api\EnvioController::class, 'simularMovimiento']);
    Route::get('/{id}/seguimiento', [\App\Http\Controllers\Api\EnvioController::class, 'getSeguimiento']);
    Route::get('/{id}/documento', [\App\Http\Controllers\Api\DocumentoController::class, 'generarDocumento']);
});

// Rutas de envíos (API) - Usando apiResource para rutas estándar REST (debe ir DESPUÉS de rutas personalizadas)
// Nota: Usamos nombres diferentes para evitar conflicto con rutas web
Route::apiResource('envios', EnvioApiController::class)
    ->only(['index', 'store', 'show'])
    ->names([
        'index' => 'api.envios.index',
        'store' => 'api.envios.store',
        'show' => 'api.envios.show'
    ]);

// Ruta para recibir actualizaciones desde Node.js
Route::post('/sync/envio-estado', function (Request $request) {
    $validated = $request->validate([
        'codigo' => 'required|string',
        'estado' => 'required|string',
    ]);

    $envio = \App\Models\Envio::where('codigo', $validated['codigo'])->first();
    
    if ($envio) {
        $envio->update(['estado' => $validated['estado']]);
        
        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado'
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Envío no encontrado'
    ], 404);
});

// ========== PEDIDOS DESDE SISTEMA ALMACÉN ==========
Route::post('/pedido-almacen', function (Request $request) {
    try {
        $validated = $request->validate([
            'codigo_origen' => 'required|string',
            'almacen_destino' => 'required|string',
            'almacen_destino_lat' => 'nullable|numeric',
            'almacen_destino_lng' => 'nullable|numeric',
            'almacen_destino_direccion' => 'nullable|string',
            'solicitante_id' => 'nullable|integer',
            'solicitante_nombre' => 'nullable|string',
            'solicitante_email' => 'nullable|email',
            'fecha_requerida' => 'required|date',
            'hora_requerida' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'total_cantidad' => 'nullable|integer',
            'total_peso' => 'nullable|numeric',
            'total_precio' => 'nullable|numeric',
            'productos' => 'required|array|min:1',
            'webhook_url' => 'nullable|string',
        ]);

        // Buscar o crear almacén destino
        $almacen = \App\Models\Almacen::where('nombre', 'like', '%' . $validated['almacen_destino'] . '%')->first();
        
        if (!$almacen) {
            // Crear almacén automáticamente si no existe
            $almacen = \App\Models\Almacen::create([
                'nombre' => $validated['almacen_destino'],
                'latitud' => $validated['almacen_destino_lat'] ?? -17.7833,
                'longitud' => $validated['almacen_destino_lng'] ?? -63.1821,
                'direccion_completa' => $validated['almacen_destino_direccion'] ?? $validated['almacen_destino'],
                'activo' => true,
                'es_planta' => false,
            ]);
        }

        // Generar código único para el envío
        $ultimoEnvio = \App\Models\Envio::orderBy('id', 'desc')->first();
        $numero = $ultimoEnvio ? $ultimoEnvio->id + 1 : 1;
        $codigo = 'ENV-' . date('Ymd') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);

        // Crear el envío
        $envio = \App\Models\Envio::create([
            'codigo' => $codigo,
            'almacen_destino_id' => $almacen->id,
            'categoria' => 'General',
            'fecha_creacion' => now(),
            'fecha_estimada_entrega' => $validated['fecha_requerida'],
            'hora_estimada' => $validated['hora_requerida'] ?? null,
            'estado' => 'pendiente',
            'total_cantidad' => $validated['total_cantidad'] ?? 0,
            'total_peso' => $validated['total_peso'] ?? 0,
            'total_precio' => $validated['total_precio'] ?? 0,
            'observaciones' => "[Pedido desde Sistema Almacén]" .
                "\nSolicitante: " . ($validated['solicitante_nombre'] ?? 'N/A') .
                "\nCódigo origen: " . $validated['codigo_origen'] .
                ($validated['observaciones'] ?? '' ? "\nNotas: " . $validated['observaciones'] : ''),
        ]);

        // Crear productos del envío
        foreach ($validated['productos'] as $prod) {
            \App\Models\EnvioProducto::create([
                'envio_id' => $envio->id,
                'producto_nombre' => $prod['producto_nombre'] ?? 'Producto',
                'cantidad' => $prod['cantidad'] ?? 0,
                'peso_unitario' => $prod['peso_unitario'] ?? 0,
                'precio_unitario' => $prod['precio_unitario'] ?? 0,
                'total_peso' => $prod['total_peso'] ?? 0,
                'total_precio' => $prod['total_precio'] ?? 0,
            ]);
        }

        // Notificar al sistema de almacén via webhook (si se proporcionó URL)
        if (!empty($validated['webhook_url'])) {
            try {
                \Illuminate\Support\Facades\Http::post($validated['webhook_url'] . '/envio', [
                    'id' => $envio->id,
                    'codigo' => $envio->codigo,
                    'almacen_destino' => $almacen->nombre,
                    'estado' => $envio->estado,
                    'fecha_creacion' => $envio->fecha_creacion,
                    'fecha_estimada_entrega' => $envio->fecha_estimada_entrega,
                    'hora_estimada' => $envio->hora_estimada,
                    'total_cantidad' => $envio->total_cantidad,
                    'total_peso' => $envio->total_peso,
                    'total_precio' => $envio->total_precio,
                    'productos' => $validated['productos'],
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("No se pudo notificar webhook: " . $e->getMessage());
            }
        }

        // Obtener la planta (origen)
        $planta = \App\Models\Almacen::where('es_planta', true)->first();

        return response()->json([
            'success' => true,
            'message' => 'Pedido recibido y envío creado correctamente',
            'envio_id' => $envio->id,
            'codigo' => $envio->codigo,
            'estado' => $envio->estado,
            'fecha_creacion' => $envio->fecha_creacion,
            'fecha_estimada_entrega' => $envio->fecha_estimada_entrega,
            'almacen_destino' => $almacen->nombre,
            'destino_lat' => $almacen->latitud,
            'destino_lng' => $almacen->longitud,
            'destino_direccion' => $almacen->direccion_completa,
            'origen_lat' => $planta->latitud ?? -17.7833,
            'origen_lng' => $planta->longitud ?? -63.1821,
            'origen_direccion' => $planta->direccion_completa ?? 'Planta Principal',
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Error creando pedido: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error interno: ' . $e->getMessage()
        ], 500);
    }
});

// ========== RUTAS EN TIEMPO REAL ==========
Route::prefix('rutas')->group(function () {
    // Obtener envíos activos para el mapa en tiempo real
    Route::get('/envios-activos', [RutaApiController::class, 'enviosActivos']);
    
    // Obtener envíos activos filtrados por almacén
    Route::get('/envios-activos-almacen/{almacenId}', [RutaApiController::class, 'enviosActivosPorAlmacen']);
    
    // Obtener seguimiento de un envío específico
    Route::get('/seguimiento/{id}', [RutaApiController::class, 'seguimiento']);
});

