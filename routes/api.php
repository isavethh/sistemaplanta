<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EnvioApiController;
use App\Http\Controllers\Api\AlmacenApiController;
use App\Http\Controllers\Api\UsuarioApiController;
use App\Http\Controllers\Api\RutaApiController;
use App\Http\Controllers\Api\DashboardApiController;

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
        'timestamp' => now(),
        'api_base_url' => config('services.app_mobile.api_base_url', env('APP_URL', 'http://localhost') . '/api'),
    ]);
});

// Ruta para obtener configuración de la API (útil para la app móvil)
Route::get('/config', function () {
    $apiBaseUrl = config('services.app_mobile.api_base_url', env('APP_URL', 'http://localhost') . '/api');
    return response()->json([
        'success' => true,
        'api_base_url' => $apiBaseUrl,
        'endpoints' => [
            'transportistas_login' => "{$apiBaseUrl}/public/transportistas-login",
            'login_transportista' => "{$apiBaseUrl}/public/login-transportista",
            'envios_transportista' => "{$apiBaseUrl}/transportista/{id}/envios",
            'aceptar_envio' => "{$apiBaseUrl}/envios/{id}/aceptar",
            'rechazar_envio' => "{$apiBaseUrl}/envios/{id}/rechazar",
            'iniciar_envio' => "{$apiBaseUrl}/envios/{id}/iniciar",
            'marcar_entregado' => "{$apiBaseUrl}/envios/{id}/entregado",
        ],
    ], 200, [
        'Content-Type' => 'application/json',
        'Access-Control-Allow-Origin' => '*',
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
Route::options('/transportista/{id}/envios', function () {
    return response()->json([], 200, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
    ]);
});

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
    // Rutas OPTIONS para CORS preflight
    Route::options('/{id}/aceptar', function () {
        return response()->json([], 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ]);
    });
    Route::options('/{id}/rechazar', function () {
        return response()->json([], 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ]);
    });
    Route::post('/{id}/iniciar', [\App\Http\Controllers\Api\EnvioController::class, 'iniciar']);
    Route::post('/{id}/entregado', [\App\Http\Controllers\Api\EnvioController::class, 'marcarEntregado']);
    Route::post('/{id}/simular-movimiento', [\App\Http\Controllers\Api\EnvioController::class, 'simularMovimiento']);
    Route::get('/{id}/seguimiento', [\App\Http\Controllers\Api\EnvioController::class, 'getSeguimiento']);
    Route::get('/{id}/documento', [\App\Http\Controllers\Api\DocumentoController::class, 'generarDocumento']);
    
    // Rutas para propuesta de vehículos (integración con Trazabilidad)
    Route::get('/{id}/propuesta-vehiculos-pdf', [EnvioApiController::class, 'propuestaVehiculosPdf']);
    Route::post('/{id}/aprobar-rechazar', [EnvioApiController::class, 'aprobarRechazarTrazabilidad']);
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
            'codigo_origen' => 'nullable|string', // Ahora es opcional, se puede usar 'codigo' directamente
            'codigo' => 'nullable|string', // Código directo del pedido de almacenes
            'almacen_destino' => 'required|string',
            'almacen_destino_lat' => 'nullable|numeric',
            'almacen_destino_lng' => 'nullable|numeric',
            'almacen_destino_direccion' => 'nullable|string',
            'origen_lat' => 'nullable|numeric', // Latitud del punto de recogida (planta de Trazabilidad)
            'origen_lng' => 'nullable|numeric', // Longitud del punto de recogida (planta de Trazabilidad)
            'origen_direccion' => 'nullable|string', // Dirección del punto de recogida (planta de Trazabilidad)
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
            'origen' => 'nullable|string', // 'trazabilidad' o null
        ]);

        // Crear o buscar almacén destino usando la información recibida
        // IMPORTANTE: Usar el nombre exacto que viene desde Trazabilidad
        // Buscar por nombre exacto (case-insensitive) para evitar duplicados
        $almacenDestino = \App\Models\Almacen::whereRaw('LOWER(nombre) = LOWER(?)', [$validated['almacen_destino']])
            ->where('es_planta', false)
            ->first();
        
        // Si no existe, crear un almacén con el nombre exacto recibido
        if (!$almacenDestino) {
            $almacenDestino = \App\Models\Almacen::create([
                'nombre' => $validated['almacen_destino'], // Usar el nombre exacto recibido desde Trazabilidad
                'latitud' => $validated['almacen_destino_lat'] ?? null,
                'longitud' => $validated['almacen_destino_lng'] ?? null,
                'direccion_completa' => $validated['almacen_destino_direccion'] ?? $validated['almacen_destino'],
                'activo' => true,
                'es_planta' => false, // Este es un almacén de destino, no una planta
            ]);
            
            \Illuminate\Support\Facades\Log::info('Almacén creado desde Trazabilidad', [
                'nombre' => $validated['almacen_destino'],
                'id' => $almacenDestino->id
            ]);
        } else {
            // Si existe pero el nombre es diferente (case-sensitive), actualizarlo
            if ($almacenDestino->nombre !== $validated['almacen_destino']) {
                $almacenDestino->nombre = $validated['almacen_destino']; // Actualizar al nombre exacto recibido
            }
            
            // Actualizar coordenadas y dirección si están disponibles y diferentes
            $updated = false;
            if ($validated['almacen_destino_lat'] && $validated['almacen_destino_lng']) {
                if ($almacenDestino->latitud != $validated['almacen_destino_lat'] || 
                    $almacenDestino->longitud != $validated['almacen_destino_lng']) {
                    $almacenDestino->latitud = $validated['almacen_destino_lat'];
                    $almacenDestino->longitud = $validated['almacen_destino_lng'];
                    $updated = true;
                }
            }
            if ($validated['almacen_destino_direccion'] && 
                $almacenDestino->direccion_completa != $validated['almacen_destino_direccion']) {
                $almacenDestino->direccion_completa = $validated['almacen_destino_direccion'];
                $updated = true;
            }
            if ($updated || $almacenDestino->isDirty('nombre')) {
                $almacenDestino->save();
            }
        }

        // Obtener la planta (almacén origen)
        // Si viene desde Trazabilidad, usar la dirección de origen enviada
        // Si no viene, buscar o crear la planta por defecto
        if (($validated['origen'] ?? '') === 'trazabilidad' && 
            isset($validated['origen_lat']) && isset($validated['origen_lng'])) {
            // Usar la dirección de origen enviada desde Trazabilidad
            $plantaNombre = $validated['origen_direccion'] ?? 'Planta Trazabilidad';
            $plantaLat = $validated['origen_lat'];
            $plantaLng = $validated['origen_lng'];
            
            // Buscar o crear la planta con la dirección de Trazabilidad
            $planta = \App\Models\Almacen::where('es_planta', true)->first();
            if (!$planta) {
                $planta = \App\Models\Almacen::create([
                    'nombre' => $plantaNombre,
                    'latitud' => $plantaLat,
                    'longitud' => $plantaLng,
                    'direccion_completa' => $plantaNombre,
                    'activo' => true,
                    'es_planta' => true,
                ]);
            } else {
                // Actualizar la planta con la dirección de Trazabilidad si es diferente
                if ($planta->latitud != $plantaLat || $planta->longitud != $plantaLng || 
                    $planta->direccion_completa != $plantaNombre) {
                    $planta->latitud = $plantaLat;
                    $planta->longitud = $plantaLng;
                    $planta->direccion_completa = $plantaNombre;
                    $planta->nombre = $plantaNombre;
                    $planta->save();
                }
            }
        } else {
            // Si no viene desde Trazabilidad, usar la planta por defecto
            $planta = \App\Models\Almacen::where('es_planta', true)->first();
            if (!$planta) {
                $planta = \App\Models\Almacen::firstOrCreate(
                    ['es_planta' => true],
                    [
                        'nombre' => 'Planta Principal',
                        'latitud' => -17.7833,
                        'longitud' => -63.1821,
                        'direccion_completa' => 'Planta Principal',
                        'activo' => true,
                        'es_planta' => true,
                    ]
                );
            }
        }

        // Usar el mismo código del pedido de almacenes si viene en la petición
        // Esto mantiene el mismo código en todos los sistemas (almacenes, Trazabilidad, plantaCruds)
        // Prioridad: codigo > codigo_origen > generar nuevo
        if (!empty($validated['codigo'])) {
            $codigo = $validated['codigo'];
        } elseif (!empty($validated['codigo_origen'])) {
            $codigo = $validated['codigo_origen'];
        } else {
            // Solo generar código nuevo si no viene del almacén o Trazabilidad
            $ultimoEnvio = \App\Models\Envio::orderBy('id', 'desc')->first();
            $numero = $ultimoEnvio ? $ultimoEnvio->id + 1 : 1;
            // Generar código con formato ENV-YYMMDD-XXXXX (5 caracteres aleatorios)
            $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));
            $codigo = 'ENV-' . date('ymd') . '-' . $random;
        }
        
        // Verificar que el código no esté duplicado
        $envioExistente = \App\Models\Envio::where('codigo', $codigo)->first();
        if ($envioExistente) {
            // Si ya existe, agregar un sufijo único
            $codigo = $codigo . '-' . strtoupper(substr(uniqid(), -3));
        }

        // Construir observaciones
        $observacionesAlmacen = $validated['observaciones'] ?? '';
        
        // Agregar webhook_url si viene en la petición
        if (!empty($validated['webhook_url'])) {
            $observacionesAlmacen .= "\nwebhook_url: {$validated['webhook_url']}";
        }

        // Determinar el estado inicial según el origen
        // Si viene de Trazabilidad, debe estar pendiente de aprobación
        $estadoInicial = ($validated['origen'] ?? '') === 'trazabilidad' 
            ? 'pendiente_aprobacion_trazabilidad' 
            : 'pendiente';

        // Crear el envío usando el almacén destino correcto
        $envio = \App\Models\Envio::create([
            'codigo' => $codigo,
            'almacen_destino_id' => $almacenDestino->id, // Usar el almacén destino correcto
            'categoria' => 'General',
            'fecha_creacion' => now(),
            'fecha_estimada_entrega' => $validated['fecha_requerida'],
            'hora_estimada' => $validated['hora_requerida'] ?? null,
            'estado' => $estadoInicial, // Usar estado según origen
            'total_cantidad' => $validated['total_cantidad'] ?? 0,
            'total_peso' => $validated['total_peso'] ?? 0,
            'total_precio' => $validated['total_precio'] ?? 0,
            'observaciones' => $observacionesAlmacen,
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
                    'almacen_destino' => $almacenDestino->nombre,
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

        return response()->json([
            'success' => true,
            'message' => 'Pedido recibido y envío creado correctamente',
            'envio_id' => $envio->id,
            'codigo' => $envio->codigo,
            'estado' => $envio->estado,
            'fecha_creacion' => $envio->fecha_creacion,
            'fecha_estimada_entrega' => $envio->fecha_estimada_entrega,
            'almacen_destino' => $almacenDestino->nombre, // Usar el nombre del almacén destino correcto
            'destino_lat' => $almacenDestino->latitud ?? $validated['almacen_destino_lat'] ?? null,
            'destino_lng' => $almacenDestino->longitud ?? $validated['almacen_destino_lng'] ?? null,
            'destino_direccion' => $almacenDestino->direccion_completa ?? $validated['almacen_destino_direccion'] ?? $validated['almacen_destino'],
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

// ========== RUTAS DASHBOARD INTERACTIVO ==========
Route::prefix('dashboard')->group(function () {
    // Filtrar envíos por criterio (estado, almacén, transportista, etc.)
    Route::get('/filtrar', [DashboardApiController::class, 'filtrar']);
    
    // Obtener detalles de un KPI específico
    Route::get('/kpi', [DashboardApiController::class, 'kpiDetalle']);
});

