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

Route::get('/', function () {
    return view('dashboard');
});

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

Route::resource('users', UserController::class);
Route::resource('almacenes', AlmacenController::class);
Route::get('almacenes/{almacen}/inventario', [AlmacenController::class, 'inventario'])->name('almacenes.inventario');
Route::resource('envios', EnvioController::class);
Route::post('envios/asignacion-multiple', [EnvioController::class, 'asignacionMultiple'])->name('envios.asignacionMultiple');
Route::get('envios/{envio}/tracking', [EnvioController::class, 'tracking'])->name('envios.tracking');
Route::post('envios/{envio}/actualizar-estado', [EnvioController::class, 'actualizarEstado'])->name('envios.actualizarEstado');

// Rutas de Asignaciones
Route::get('asignaciones', [AsignacionController::class, 'index'])->name('asignaciones.index');
Route::post('asignaciones/asignar', [AsignacionController::class, 'asignar'])->name('asignaciones.asignar');
Route::delete('asignaciones/{envio}/remover', [AsignacionController::class, 'remover'])->name('asignaciones.remover');

// Rutas adicionales para gestión completa
Route::resource('productos', App\Http\Controllers\ProductoController::class);
Route::resource('categorias', App\Http\Controllers\CategoriaController::class);
Route::resource('inventarios', App\Http\Controllers\InventarioAlmacenController::class);
Route::get('inventarios/almacen/{almacen}', [App\Http\Controllers\InventarioAlmacenController::class, 'porAlmacen'])->name('inventarios.porAlmacen');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
