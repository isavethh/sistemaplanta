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

// Rutas de envíos (API)
Route::prefix('envios')->group(function () {
    Route::get('/', [EnvioApiController::class, 'index']);
    Route::post('/', [EnvioApiController::class, 'store']);
    Route::get('/{id}', [EnvioApiController::class, 'show']);
    Route::put('/{id}/estado', [EnvioApiController::class, 'updateEstado']);
    Route::post('/{id}/iniciar', [EnvioApiController::class, 'iniciar']);
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

