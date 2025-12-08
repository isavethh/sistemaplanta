<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EnvioApiController;

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

// Rutas públicas para Node.js
Route::get('/almacenes', function () {
    $almacenes = \App\Models\Almacen::where('activo', true)
        ->select('id', 'nombre', 'direccion_completa as direccion', 'latitud', 'longitud', 'activo')
        ->orderBy('nombre')
        ->get();
    
    return response()->json([
        'success' => true,
        'data' => $almacenes
    ]);
});

Route::get('/usuarios', function () {
    $usuarios = \App\Models\User::select('id', 'name as nombre', 'email', 'role as rol_nombre')
        ->orderBy('name')
        ->get();
    
    return response()->json([
        'success' => true,
        'data' => $usuarios
    ]);
});

// Rutas públicas (sin autenticación para la app móvil)
Route::prefix('public')->group(function () {
    // Login y lista de transportistas
    Route::get('/transportistas-login', [\App\Http\Controllers\Api\TransportistaController::class, 'getTransportistasLogin']);
    Route::post('/login-transportista', [\App\Http\Controllers\Api\TransportistaController::class, 'loginTransportista']);
});

// Rutas de transportistas (sin prefix para que funcione con /api/transportista/{id}/envios)
Route::get('/transportista/{id}/envios', [\App\Http\Controllers\Api\TransportistaController::class, 'getEnviosAsignados']);

// Rutas de envíos (API) - NUEVAS PARA APP MÓVIL
Route::prefix('envios')->group(function () {
    Route::get('/', [EnvioApiController::class, 'index']);
    Route::post('/', [EnvioApiController::class, 'store']);
    // NOTA: La ruta de transportista está FUERA de este grupo (línea 57)
    Route::get('/{id}', [\App\Http\Controllers\Api\EnvioController::class, 'show']);
    Route::get('/{id}/documento', [\App\Http\Controllers\Api\DocumentoController::class, 'generarDocumento']);
    Route::put('/{id}/estado', [EnvioApiController::class, 'updateEstado']);
    Route::post('/{id}/aceptar', [\App\Http\Controllers\Api\EnvioController::class, 'aceptar']);
    Route::post('/{id}/rechazar', [\App\Http\Controllers\Api\EnvioController::class, 'rechazar']);
    Route::post('/{id}/iniciar', [\App\Http\Controllers\Api\EnvioController::class, 'iniciar']);
    Route::post('/{id}/entregado', [\App\Http\Controllers\Api\EnvioController::class, 'marcarEntregado']);
    Route::post('/{id}/simular-movimiento', [\App\Http\Controllers\Api\EnvioController::class, 'simularMovimiento']);
    Route::get('/{id}/seguimiento', [\App\Http\Controllers\Api\EnvioController::class, 'getSeguimiento']);
    Route::get('/qr/{codigo}', [EnvioApiController::class, 'getByQrCode']);
});

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

// ========== RUTAS EN TIEMPO REAL ==========
Route::prefix('rutas')->group(function () {
    // Obtener envíos activos para el mapa en tiempo real
    Route::get('/envios-activos', function () {
        // Envíos en tránsito
        $enTransito = \Illuminate\Support\Facades\DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                e.fecha_inicio_transito,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                a.direccion_completa,
                u.name as transportista_nombre,
                v.placa as vehiculo_placa
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN users u ON ea.transportista_id = u.id
            LEFT JOIN vehiculos v ON ea.vehiculo_id = v.id
            WHERE e.estado = 'en_transito'
            ORDER BY e.fecha_inicio_transito DESC
        ");
        
        // Envíos esperando inicio (asignados o aceptados)
        $esperando = \Illuminate\Support\Facades\DB::select("
            SELECT 
                e.id,
                e.codigo,
                e.estado,
                a.nombre as almacen_nombre,
                a.latitud as destino_lat,
                a.longitud as destino_lng,
                u.name as transportista_nombre
            FROM envios e
            LEFT JOIN almacenes a ON e.almacen_destino_id = a.id
            LEFT JOIN envio_asignaciones ea ON e.id = ea.envio_id
            LEFT JOIN users u ON ea.transportista_id = u.id
            WHERE e.estado IN ('asignado', 'aceptado')
            ORDER BY e.created_at DESC
        ");
        
        return response()->json([
            'success' => true,
            'en_transito' => $enTransito,
            'esperando' => $esperando,
            'timestamp' => now()->toIso8601String()
        ]);
    });
    
    // Obtener seguimiento de un envío específico
    Route::get('/seguimiento/{id}', function ($id) {
        $seguimiento = \Illuminate\Support\Facades\DB::select("
            SELECT latitud, longitud, velocidad, timestamp as created_at
            FROM seguimiento_envio
            WHERE envio_id = ?
            ORDER BY timestamp ASC
        ", [$id]);
        
        return response()->json([
            'success' => true,
            'data' => $seguimiento
        ]);
    });
});

