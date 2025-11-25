@extends('adminlte::page')
@section('title', 'Dashboard')
@section('content_header')
    <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard - Sistema de Gestión de Planta</h1>
@endsection

@section('content')
<!-- Tarjetas de Estadísticas Principales -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>Usuarios Registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('users.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ \App\Models\Almacen::count() }}</h3>
                <p>Almacenes Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <a href="{{ route('almacenes.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ \App\Models\Producto::count() }}</h3>
                <p>Productos Registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-box-open"></i>
            </div>
            <a href="{{ route('productos.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>{{ \App\Models\Envio::count() }}</h3>
                <p>Envíos Totales</p>
            </div>
            <div class="icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Segunda fila de estadísticas -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-primary">
            <span class="info-box-icon"><i class="fas fa-folder"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Categorías</span>
                <span class="info-box-number">{{ \App\Models\Categoria::count() }}</span>
                <a href="{{ route('categorias.index') }}" class="text-white"><small>Ver detalles <i class="fas fa-arrow-right"></i></small></a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Inventario Total</span>
                <span class="info-box-number">{{ \App\Models\InventarioAlmacen::sum('cantidad') }}</span>
                <a href="{{ route('inventarios.index') }}" class="text-white"><small>Ver inventario <i class="fas fa-arrow-right"></i></small></a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-success">
            <span class="info-box-icon"><i class="fas fa-truck"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Vehículos</span>
                <span class="info-box-number">{{ \App\Models\Vehiculo::count() }}</span>
                <a href="{{ route('vehiculos.index') }}" class="text-white"><small>Ver vehículos <i class="fas fa-arrow-right"></i></small></a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-warning">
            <span class="info-box-icon"><i class="fas fa-users-cog"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Clientes</span>
                <span class="info-box-number">
                    @php
                        try {
                            echo \App\Models\User::where('tipo', 'cliente')->count();
                        } catch (\Exception $e) {
                            echo \App\Models\User::where('role', 'cliente')->count();
                        }
                    @endphp
                </span>
                <a href="{{ route('clientes.index') }}" class="text-white"><small>Ver clientes <i class="fas fa-arrow-right"></i></small></a>
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-rocket"></i> Accesos Rápidos</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Gestión de Inventario -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-light hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-warehouse fa-3x text-primary mb-3"></i>
                                <h5>Gestión de Inventario</h5>
                                <div class="btn-group-vertical w-100">
                                    <a href="{{ route('productos.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-box"></i> Productos
                                    </a>
                                    <a href="{{ route('categorias.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-folder"></i> Categorías
                                    </a>
                                    <a href="{{ route('inventarios.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-cubes"></i> Inventario
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gestión de Envíos -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-light hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-shipping-fast fa-3x text-success mb-3"></i>
                                <h5>Gestión de Envíos</h5>
                                <div class="btn-group-vertical w-100">
                                    <a href="{{ route('envios.index') }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-box-open"></i> Envíos
                                    </a>
                                    <a href="{{ route('rutas.index') }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-route"></i> Rutas
                                    </a>
                                    <a href="{{ route('codigosqr.index') }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-qrcode"></i> Códigos QR
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gestión de Vehículos -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-light hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-truck fa-3x text-warning mb-3"></i>
                                <h5>Gestión de Vehículos</h5>
                                <div class="btn-group-vertical w-100">
                                    <a href="{{ route('vehiculos.index') }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-truck"></i> Vehículos
                                    </a>
                                    <a href="{{ route('tiposvehiculo.index') }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-list"></i> Tipos
                                    </a>
                                    <a href="{{ route('estadosvehiculo.index') }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-info-circle"></i> Estados
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gestión de Personal -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card bg-light hover-shadow">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-danger mb-3"></i>
                                <h5>Gestión de Personal</h5>
                                <div class="btn-group-vertical w-100">
                                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-user"></i> Usuarios
                                    </a>
                                    <a href="{{ route('transportistas.index') }}" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-id-card"></i> Transportistas
                                    </a>
                                    <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-user-tie"></i> Clientes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración y Catálogos -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h5><i class="fas fa-cog"></i> Configuración y Catálogos</h5>
                        <hr>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('almacenes.index') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-warehouse"></i><br>Almacenes
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('direcciones.index') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-map-marker-alt"></i><br>Direcciones
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('tiposempaque.index') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-box"></i><br>Tipos Empaque
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('unidadesmedida.index') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-ruler"></i><br>Unidades Medida
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('administradores.index') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-user-shield"></i><br>Administradores
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bienvenida -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Bienvenido al Sistema de Gestión de Planta</h3>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    <i class="fas fa-check-circle text-success"></i> Sistema de gestión integral para control de inventarios, envíos y logística.
                    <br>
                    <i class="fas fa-chart-line text-info"></i> Accede a los diferentes módulos desde el menú lateral o utiliza los accesos rápidos arriba.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .small-box, .info-box, .card {
        border-radius: 10px;
    }
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        transition: box-shadow 0.3s ease-in-out;
    }
    .btn-group-vertical .btn {
        margin-bottom: 5px;
    }
</style>
@endsection
