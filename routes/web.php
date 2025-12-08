<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TransportistaController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\DireccionController;
use App\Http\Controllers\RutaTiempoRealController;
use App\Http\Controllers\CodigoQRController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\EstadoVehiculoController;
use App\Http\Controllers\TipoEmpaqueController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\EnvioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TipoTransporteController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\IncidenteController;
use App\Http\Controllers\RutaMultiEntregaController;

// Dashboard - Personalizado por rol
Route::get('/', function () {
    $user = auth()->user();

    if ($user->hasRole('planta')) {
        return view('dashboards.planta');
    } elseif ($user->hasRole('administrador')) {
        return view('dashboards.administrador');
    } elseif ($user->hasRole('transportista')) {
        return view('dashboards.transportista');
    } elseif ($user->hasRole('almacen')) {
        return view('dashboards.almacen');
    }

    // Si no tiene rol específico, mostrar dashboard general
    return view('dashboard');
})->middleware('auth');

// Gestión de Usuarios - Solo requiere autenticación
Route::middleware(['auth'])->group(function () {
    Route::resource('administradores', AdministradorController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('transportistas', TransportistaController::class);
    Route::resource('vehiculos', VehiculoController::class);
    Route::resource('direcciones', DireccionController::class);
    Route::resource('rutas', RutaTiempoRealController::class);
    Route::resource('codigosqr', CodigoQRController::class);
    Route::resource('tiposvehiculo', TipoVehiculoController::class);
    Route::resource('estadosvehiculo', EstadoVehiculoController::class);
    Route::resource('tiposempaque', TipoEmpaqueController::class);
    Route::resource('unidadesmedida', UnidadMedidaController::class);
    Route::resource('tipos-transporte', TipoTransporteController::class);
    Route::resource('tamanos-transporte', App\Http\Controllers\TamanoTransporteController::class);
});

// Todas las rutas solo requieren autenticación (sin restricciones de permisos/roles)
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('almacenes', AlmacenController::class);
    Route::get('almacenes/{almacen}/inventario', [AlmacenController::class, 'inventario'])->name('almacenes.inventario');

    Route::resource('envios', EnvioController::class);
    Route::post('envios/asignacion-multiple', [EnvioController::class, 'asignacionMultiple'])->name('envios.asignacionMultiple');
    Route::get('envios/{envio}/tracking', [EnvioController::class, 'tracking'])->name('envios.tracking');
    Route::post('envios/{envio}/actualizar-estado', [EnvioController::class, 'actualizarEstado'])->name('envios.actualizarEstado');
    Route::post('envios/{envio}/aprobar', [EnvioController::class, 'aprobar'])->name('envios.aprobar');

    Route::get('notas-venta', [App\Http\Controllers\NotaVentaController::class, 'index'])->name('notas-venta.index');
    Route::get('notas-venta/{id}', [App\Http\Controllers\NotaVentaController::class, 'show'])->name('notas-venta.show');
    Route::get('notas-venta/{id}/html', [App\Http\Controllers\NotaVentaController::class, 'verHTML'])->name('notas-venta.html');

    Route::get('asignaciones', [AsignacionController::class, 'index'])->name('asignaciones.index');
    Route::post('asignaciones/asignar', [AsignacionController::class, 'asignar'])->name('asignaciones.asignar');
    Route::delete('asignaciones/{envio}/remover', [AsignacionController::class, 'remover'])->name('asignaciones.remover');

    Route::resource('productos', App\Http\Controllers\ProductoController::class);
    Route::resource('categorias', App\Http\Controllers\CategoriaController::class);
    Route::resource('inventarios', App\Http\Controllers\InventarioAlmacenController::class);
    Route::get('inventarios/almacen/{almacen}', [App\Http\Controllers\InventarioAlmacenController::class, 'porAlmacen'])->name('inventarios.porAlmacen');
});

// Rutas de Incidentes - Solo autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('incidentes', [IncidenteController::class, 'index'])->name('incidentes.index');
    Route::get('incidentes/{id}', [IncidenteController::class, 'show'])->name('incidentes.show');
    Route::put('incidentes/{id}/estado', [IncidenteController::class, 'cambiarEstado'])->name('incidentes.cambiarEstado');
    Route::post('incidentes/{id}/nota', [IncidenteController::class, 'agregarNota'])->name('incidentes.agregarNota');
});

// Rutas Multi-Entrega - Solo autenticación
Route::prefix('rutas-multi')->name('rutas-multi.')->middleware(['auth'])->group(function () {
    Route::get('/', [RutaMultiEntregaController::class, 'index'])->name('index');
    Route::get('/crear', [RutaMultiEntregaController::class, 'create'])->name('create');
    Route::post('/', [RutaMultiEntregaController::class, 'store'])->name('store');
    Route::get('/monitoreo', [RutaMultiEntregaController::class, 'monitoreo'])->name('monitoreo');
    Route::get('/{id}', [RutaMultiEntregaController::class, 'show'])->name('show');
    Route::get('/{id}/resumen', [RutaMultiEntregaController::class, 'resumen'])->name('resumen');
    Route::get('/{id}/documentos', [RutaMultiEntregaController::class, 'documentos'])->name('documentos');
    Route::put('/{id}/reordenar', [RutaMultiEntregaController::class, 'reordenarParadas'])->name('reordenar');
    Route::get('/api/envios-pendientes', [RutaMultiEntregaController::class, 'enviosPendientesParaMapa'])->name('api.envios-pendientes');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
