<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Sistema de Gestión de Almacén y Logística
| Generado con estructura Ibex CRUDs
| Todas las rutas siguen el formato estándar Ibex CRUD
|
*/

// ============================================================================
// AUTENTICACIÓN
// ============================================================================
// Deshabilitar registro público - solo admins pueden crear usuarios
Auth::routes(['register' => false]);

// ============================================================================
// DASHBOARD PRINCIPAL
// ============================================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return view('dashboards.administrador');
        } elseif ($user->hasRole('transportista')) {
            return view('dashboards.transportista');
        } elseif ($user->hasRole('almacen')) {
            return view('dashboards.almacen');
        }

        return view('dashboard');
    })->name('home');

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
});

// ============================================================================
// MÓDULO: GESTIÓN DE USUARIOS (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Usuarios del Sistema - CRUD Completo
    Route::resource('users', App\Http\Controllers\UserController::class);
    
    // Administradores - CRUD Completo
    Route::resource('administradores', App\Http\Controllers\AdministradorController::class);
    
    // Clientes - CRUD Completo
    Route::resource('clientes', App\Http\Controllers\ClienteController::class);
    
    // Transportistas - CRUD Completo
    Route::resource('transportistas', App\Http\Controllers\TransportistaController::class);
});

// ============================================================================
// MÓDULO: GESTIÓN DE VEHÍCULOS Y TRANSPORTE (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Vehículos - CRUD Completo
    Route::resource('vehiculos', App\Http\Controllers\VehiculoController::class);
    
    // Tipos de Vehículo - CRUD Completo
    Route::resource('tiposvehiculo', App\Http\Controllers\TipoVehiculoController::class);
    
    // Estados de Vehículo - CRUD Completo
    Route::resource('estadosvehiculo', App\Http\Controllers\EstadoVehiculoController::class);
    
    // Tipos de Transporte - CRUD Completo
    Route::resource('tipos-transporte', App\Http\Controllers\TipoTransporteController::class);
    
    // Tamaños de Transporte - CRUD Completo
    Route::resource('tamanos-transporte', App\Http\Controllers\TamanoTransporteController::class);
});

// ============================================================================
// MÓDULO: GESTIÓN DE ALMACENES (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Almacenes - CRUD Completo
    Route::resource('almacenes', App\Http\Controllers\AlmacenController::class);
    
    // Rutas adicionales de Almacenes
    Route::get('almacenes/{almacen}/inventario', [App\Http\Controllers\AlmacenController::class, 'inventario'])->name('almacenes.inventario');
    Route::get('almacenes/monitoreo', [App\Http\Controllers\AlmacenController::class, 'monitoreo'])->name('almacenes.monitoreo');
    
    // Inventarios - CRUD Completo
    Route::resource('inventarios', App\Http\Controllers\InventarioAlmacenController::class);
    
    // Rutas adicionales de Inventarios
    Route::get('inventarios/almacen/{almacen}', [App\Http\Controllers\InventarioAlmacenController::class, 'porAlmacen'])->name('inventarios.porAlmacen');
    
    // Inventario del Transportista
    Route::get('inventarios-transportista', [App\Http\Controllers\InventarioTransportistaController::class, 'index'])->name('inventarios-transportista.index');
});

// ============================================================================
// MÓDULO: GESTIÓN DE PRODUCTOS (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Productos - CRUD Completo
    Route::resource('productos', App\Http\Controllers\ProductoController::class);
    
    // Categorías - CRUD Completo
    Route::resource('categorias', App\Http\Controllers\CategoriaController::class);
    
    // Unidades de Medida - CRUD Completo
    Route::resource('unidadesmedida', App\Http\Controllers\UnidadMedidaController::class);
});

// ============================================================================
// MÓDULO: GESTIÓN DE EMPAQUES (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Tipos de Empaque - CRUD Completo
    Route::resource('tiposempaque', App\Http\Controllers\TipoEmpaqueController::class);
    
    // Rutas adicionales de Tipos de Empaque
    Route::get('tiposempaque-calculador', [App\Http\Controllers\TipoEmpaqueController::class, 'calculador'])->name('tiposempaque.calculador');
    Route::post('tiposempaque-calcular', [App\Http\Controllers\TipoEmpaqueController::class, 'calcularEmpaques'])->name('tiposempaque.calcular');
});

// ============================================================================
// MÓDULO: GESTIÓN DE ENVÍOS (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Envíos - CRUD Completo
    Route::resource('envios', App\Http\Controllers\EnvioController::class);
    
    // Rutas adicionales de Envíos
    Route::post('envios/asignacion-multiple', [App\Http\Controllers\EnvioController::class, 'asignacionMultiple'])->name('envios.asignacionMultiple');
    Route::get('envios/{envio}/tracking', [App\Http\Controllers\EnvioController::class, 'tracking'])->name('envios.tracking');
    Route::post('envios/{envio}/actualizar-estado', [App\Http\Controllers\EnvioController::class, 'actualizarEstado'])->name('envios.actualizarEstado');
});

// ============================================================================
// MÓDULO: ASIGNACIÓN DE TRANSPORTISTAS (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Asignaciones - CRUD Completo
    Route::get('asignaciones', [App\Http\Controllers\AsignacionController::class, 'index'])->name('asignaciones.index');
    Route::post('asignaciones/asignar', [App\Http\Controllers\AsignacionController::class, 'asignar'])->name('asignaciones.asignar');
    Route::post('asignaciones/asignar-multiple', [App\Http\Controllers\AsignacionController::class, 'asignarMultiple'])->name('asignaciones.asignar-multiple');
    Route::delete('asignaciones/{envio}/remover', [App\Http\Controllers\AsignacionController::class, 'remover'])->name('asignaciones.remover');
    
    // Asignación Múltiple por Fecha - CRUD Completo
    Route::get('asignacion-multiple', [App\Http\Controllers\AsignacionMultipleController::class, 'index'])->name('asignacion-multiple.index');
    Route::post('asignacion-multiple/asignar', [App\Http\Controllers\AsignacionMultipleController::class, 'asignar'])->name('asignacion-multiple.asignar');
});

// ============================================================================
// MÓDULO: GESTIÓN DE INCIDENTES (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Incidentes - CRUD Completo
    Route::resource('incidentes', App\Http\Controllers\IncidenteController::class);
    
    // Rutas adicionales de Incidentes
    Route::put('incidentes/{incidente}/estado', [App\Http\Controllers\IncidenteController::class, 'cambiarEstado'])->name('incidentes.cambiarEstado');
    Route::post('incidentes/{incidente}/nota', [App\Http\Controllers\IncidenteController::class, 'agregarNota'])->name('incidentes.agregarNota');
});

// ============================================================================
// MÓDULO: RUTAS Y NAVEGACIÓN (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Rutas en Tiempo Real - CRUD Completo
    Route::resource('rutas', App\Http\Controllers\RutaTiempoRealController::class);
    
    // Códigos QR - CRUD Completo
    Route::resource('codigosqr', App\Http\Controllers\CodigoQRController::class);
    
    // Direcciones - ELIMINADO (tabla redundante con doble conexión a almacenes)
    // Route::resource('direcciones', App\Http\Controllers\DireccionController::class);
    
    // Tamaños de Vehículo - CRUD Completo
    Route::resource('tamanos-vehiculo', App\Http\Controllers\TamanoVehiculoController::class);
    
    // Estados de Vehículo - CRUD Completo (ya existe en vehículos, pero agregamos aquí también)
    // Route::resource('estados-vehiculo', App\Http\Controllers\EstadoVehiculoController::class);
});

// ============================================================================
// MÓDULO: RUTAS MULTI-ENTREGA (Ibex CRUD)
// ============================================================================
Route::prefix('rutas-multi')->name('rutas-multi.')->middleware(['auth'])->group(function () {
    // Rutas Multi-Entrega - CRUD Completo
    Route::get('/', [App\Http\Controllers\RutaMultiEntregaController::class, 'index'])->name('index');
    Route::get('/crear', [App\Http\Controllers\RutaMultiEntregaController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\RutaMultiEntregaController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\RutaMultiEntregaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\RutaMultiEntregaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\RutaMultiEntregaController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\RutaMultiEntregaController::class, 'destroy'])->name('destroy');
    
    // Rutas adicionales de Rutas Multi-Entrega
    Route::get('/monitoreo', [App\Http\Controllers\RutaMultiEntregaController::class, 'monitoreo'])->name('monitoreo');
    Route::get('/{id}/resumen', [App\Http\Controllers\RutaMultiEntregaController::class, 'resumen'])->name('resumen');
    Route::get('/{id}/documentos', [App\Http\Controllers\RutaMultiEntregaController::class, 'documentos'])->name('documentos');
    Route::put('/{id}/reordenar', [App\Http\Controllers\RutaMultiEntregaController::class, 'reordenarParadas'])->name('reordenar');
    Route::get('/api/envios-pendientes', [App\Http\Controllers\RutaMultiEntregaController::class, 'enviosPendientesParaMapa'])->name('api.envios-pendientes');
});

// ============================================================================
// MÓDULO: NOTAS DE ENTREGA (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Notas de Entrega - CRUD Completo
    Route::get('notas-entrega', [App\Http\Controllers\NotaEntregaController::class, 'index'])->name('notas-entrega.index');
    Route::get('notas-entrega/create', [App\Http\Controllers\NotaEntregaController::class, 'create'])->name('notas-entrega.create');
    Route::post('notas-entrega', [App\Http\Controllers\NotaEntregaController::class, 'store'])->name('notas-entrega.store');
    Route::get('notas-entrega/{id}', [App\Http\Controllers\NotaEntregaController::class, 'show'])->name('notas-entrega.show');
    Route::get('notas-entrega/{id}/edit', [App\Http\Controllers\NotaEntregaController::class, 'edit'])->name('notas-entrega.edit');
    Route::put('notas-entrega/{id}', [App\Http\Controllers\NotaEntregaController::class, 'update'])->name('notas-entrega.update');
    Route::delete('notas-entrega/{id}', [App\Http\Controllers\NotaEntregaController::class, 'destroy'])->name('notas-entrega.destroy');
    
    // Rutas adicionales de Notas de Entrega
    Route::get('notas-entrega/{id}/html', [App\Http\Controllers\NotaEntregaController::class, 'verHTML'])->name('notas-entrega.html');
});

// ============================================================================
// MÓDULO: REPORTES Y ANÁLISIS (Ibex CRUD)
// ============================================================================
Route::prefix('reportes')->name('reportes.')->middleware(['auth'])->group(function () {
    // Centro de Reportes - CRUD Completo
    Route::get('/', [App\Http\Controllers\ReporteController::class, 'index'])->name('index');
    
    // ────────────────────────────────────────────────────────────────────────
    // REPORTE: Operaciones
    // ────────────────────────────────────────────────────────────────────────
    Route::get('/operaciones', [App\Http\Controllers\ReporteController::class, 'operaciones'])->name('operaciones');
    
    // Rutas de descarga directa (DEBEN ir ANTES de las rutas con {id} para evitar conflictos)
    Route::get('/operaciones/pdf', [App\Http\Controllers\ReporteController::class, 'operacionesPdf'])->name('operaciones.pdf');
    Route::get('/operaciones/csv', [App\Http\Controllers\ReporteController::class, 'operacionesCsv'])->name('operaciones.csv');
    
    // ────────────────────────────────────────────────────────────────────────
    // REPORTE: Notas de Entrega - CRUD Completo
    // ────────────────────────────────────────────────────────────────────────
    Route::get('/nota-entrega', [App\Http\Controllers\ReporteController::class, 'notaEntrega'])->name('nota-entrega');
    Route::get('/nota-entrega/create', [App\Http\Controllers\ReporteController::class, 'notaEntregaCreate'])->name('nota-entrega.create');
    Route::post('/nota-entrega', [App\Http\Controllers\ReporteController::class, 'notaEntregaStore'])->name('nota-entrega.store');
    Route::get('/nota-entrega/{id}', [App\Http\Controllers\ReporteController::class, 'notaEntregaShow'])->name('nota-entrega.show');
    Route::get('/nota-entrega/{id}/edit', [App\Http\Controllers\ReporteController::class, 'notaEntregaEdit'])->name('nota-entrega.edit');
    Route::put('/nota-entrega/{id}', [App\Http\Controllers\ReporteController::class, 'notaEntregaUpdate'])->name('nota-entrega.update');
    Route::delete('/nota-entrega/{id}', [App\Http\Controllers\ReporteController::class, 'notaEntregaDestroy'])->name('nota-entrega.destroy');
    
    // Rutas adicionales de Notas de Entrega
    Route::get('/nota-entrega/{id}/pdf', [App\Http\Controllers\ReporteController::class, 'notaEntregaPdf'])->name('nota-entrega.pdf');
    Route::get('/nota-entrega/{id}/html', [App\Http\Controllers\ReporteController::class, 'notaEntregaHtml'])->name('nota-entrega.html');
    
    // ────────────────────────────────────────────────────────────────────────
    // REPORTE: Incidentes
    // ────────────────────────────────────────────────────────────────────────
    Route::get('/incidentes', [App\Http\Controllers\ReporteController::class, 'incidentes'])->name('incidentes');
    
    // Rutas de descarga directa (DEBEN ir ANTES de las rutas con {id} para evitar conflictos)
    Route::get('/incidentes/pdf', [App\Http\Controllers\ReporteController::class, 'incidentesPdf'])->name('incidentes.pdf');
    Route::get('/incidentes/csv', [App\Http\Controllers\ReporteController::class, 'incidentesCsv'])->name('incidentes.csv');
    
    // ────────────────────────────────────────────────────────────────────────
    // REPORTE: Productividad
    // ────────────────────────────────────────────────────────────────────────
    Route::get('/productividad', [App\Http\Controllers\ReporteController::class, 'productividad'])->name('productividad');
    
    // Rutas de descarga directa (DEBEN ir ANTES de las rutas con {id} para evitar conflictos)
    Route::get('/productividad/pdf', [App\Http\Controllers\ReporteController::class, 'productividadPdf'])->name('productividad.pdf');
    Route::get('/productividad/csv', [App\Http\Controllers\ReporteController::class, 'productividadCsv'])->name('productividad.csv');
    
    // ────────────────────────────────────────────────────────────────────────
    // REPORTE: Mis Incidentes (Transportista)
    // ────────────────────────────────────────────────────────────────────────
    Route::get('/mis-incidentes', [App\Http\Controllers\ReporteController::class, 'misIncidentes'])->name('mis-incidentes');
    Route::get('/mis-incidentes/create', [App\Http\Controllers\ReporteController::class, 'misIncidentesCreate'])->name('mis-incidentes.create');
    Route::post('/mis-incidentes', [App\Http\Controllers\ReporteController::class, 'misIncidentesStore'])->name('mis-incidentes.store');
    
    // Rutas de descarga directa (DEBEN ir ANTES de las rutas con {id} para evitar conflictos)
    Route::get('/mis-incidentes/pdf', [App\Http\Controllers\ReporteController::class, 'misIncidentesPdf'])->name('mis-incidentes.pdf');
    Route::get('/mis-incidentes/csv', [App\Http\Controllers\ReporteController::class, 'misIncidentesCsv'])->name('mis-incidentes.csv');
    
    // ────────────────────────────────────────────────────────────────────────
    // REPORTE: Mi Productividad (Transportista) - CRUD Completo
    // ────────────────────────────────────────────────────────────────────────
    // REPORTE: Mi Productividad (Transportista)
    // ────────────────────────────────────────────────────────────────────────
    Route::get('/mi-productividad', [App\Http\Controllers\ReporteController::class, 'miProductividad'])->name('mi-productividad');
    
    // Rutas de descarga directa (DEBEN ir ANTES de las rutas con {id} para evitar conflictos)
    Route::get('/mi-productividad/pdf', [App\Http\Controllers\ReporteController::class, 'miProductividadPdf'])->name('mi-productividad.pdf');
    Route::get('/mi-productividad/csv', [App\Http\Controllers\ReporteController::class, 'miProductividadCsv'])->name('mi-productividad.csv');
    
    // ────────────────────────────────────────────────────────────────────────
    // REPORTE: Resolución de Incidentes - CRUD Completo
    // ────────────────────────────────────────────────────────────────────────
    Route::get('/resolucion-incidente/{id}', [App\Http\Controllers\ReporteController::class, 'resolucionIncidente'])->name('resolucion-incidente');
    Route::get('/resolucion-incidente/{id}/edit', [App\Http\Controllers\ReporteController::class, 'resolucionIncidenteEdit'])->name('resolucion-incidente.edit');
    Route::put('/resolucion-incidente/{id}', [App\Http\Controllers\ReporteController::class, 'resolucionIncidenteUpdate'])->name('resolucion-incidente.update');
    
    // Rutas adicionales de Resolución de Incidentes
    Route::get('/resolucion-incidente/{id}/pdf', [App\Http\Controllers\ReporteController::class, 'resolucionIncidentePdf'])->name('resolucion-incidente.pdf');
    
    // ────────────────────────────────────────────────────────────────────────
    // REPORTE: Trazabilidad Completa - CRUD Completo
    // ────────────────────────────────────────────────────────────────────────
    Route::get('/trazabilidad/{id}', [App\Http\Controllers\ReporteController::class, 'trazabilidad'])->name('trazabilidad');
    Route::get('/trazabilidad/{id}/edit', [App\Http\Controllers\ReporteController::class, 'trazabilidadEdit'])->name('trazabilidad.edit');
    Route::put('/trazabilidad/{id}', [App\Http\Controllers\ReporteController::class, 'trazabilidadUpdate'])->name('trazabilidad.update');
    
    // Rutas adicionales de Trazabilidad
    Route::get('/trazabilidad/{id}/pdf', [App\Http\Controllers\ReporteController::class, 'trazabilidadPdf'])->name('trazabilidad.pdf');
});

// ============================================================================
// MÓDULO: DASHBOARD ESTADÍSTICO (Ibex CRUD)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    // Dashboard Estadístico - CRUD Completo
    Route::get('/dashboard-estadistico', [App\Http\Controllers\DashboardController::class, 'estadistico'])->name('dashboard.estadistico');
    Route::get('/dashboard-estadistico/create', [App\Http\Controllers\DashboardController::class, 'estadisticoCreate'])->name('dashboard.estadistico.create');
    Route::post('/dashboard-estadistico', [App\Http\Controllers\DashboardController::class, 'estadisticoStore'])->name('dashboard.estadistico.store');
    Route::get('/dashboard-estadistico/{id}', [App\Http\Controllers\DashboardController::class, 'estadisticoShow'])->name('dashboard.estadistico.show');
    Route::get('/dashboard-estadistico/{id}/edit', [App\Http\Controllers\DashboardController::class, 'estadisticoEdit'])->name('dashboard.estadistico.edit');
    Route::put('/dashboard-estadistico/{id}', [App\Http\Controllers\DashboardController::class, 'estadisticoUpdate'])->name('dashboard.estadistico.update');
    Route::delete('/dashboard-estadistico/{id}', [App\Http\Controllers\DashboardController::class, 'estadisticoDestroy'])->name('dashboard.estadistico.destroy');
});
