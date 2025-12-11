@extends('adminlte::page')
@section('title', 'Dashboard Administrador')
@section('content_header')
    <h1 class="m-0"><i class="fas fa-user-shield"></i> Dashboard - Administrador</h1>
@endsection

@section('content')
@php
    use Illuminate\Support\Facades\DB;
@endphp
<!-- Mensaje de Bienvenida -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <h5><i class="icon fas fa-crown"></i> ¡Bienvenido Administrador, {{ auth()->user()->name }}!</h5>
            Panel de control completo del sistema. Gestiona envíos, rutas, usuarios y más.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>

<!-- Estadísticas Principales -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>Usuarios Totales</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('transportistas.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
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
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ \App\Models\Vehiculo::count() }}</h3>
                <p>Vehículos Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-truck"></i>
            </div>
            <a href="{{ route('vehiculos.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>{{ \App\Models\Ruta::count() }}</h3>
                <p>Rutas Activas</p>
            </div>
            <div class="icon">
                <i class="fas fa-route"></i>
            </div>
            <a href="{{ route('rutas.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Segunda Fila de Estadísticas -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-primary">
            <span class="info-box-icon"><i class="fas fa-user-tie"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Transportistas</span>
                <span class="info-box-number">{{ \App\Models\User::role('transportista')->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-warning">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Envíos Pendientes</span>
                <span class="info-box-number">{{ \App\Models\Envio::where('estado', 'pendiente')->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-spinner"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">En Tránsito</span>
                <span class="info-box-number">{{ \App\Models\Envio::where('estado', 'en_transito')->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box bg-gradient-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Entregados Hoy</span>
                <span class="info-box-number">{{ \App\Models\Envio::where('estado', 'entregado')->whereDate('updated_at', today())->count() }}</span>
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
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-check fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Asignar Envíos</h5>
                                <a href="{{ route('asignaciones.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-tasks"></i> Ir a Asignaciones
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-purple hover-card" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body text-center">
                                <i class="fas fa-route fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Crear Ruta</h5>
                                <a href="{{ route('rutas.create') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-plus"></i> Nueva Ruta
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-satellite-dish fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Monitoreo</h5>
                                <a href="{{ route('rutas.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-eye"></i> Ver Monitoreo
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Transportistas</h5>
                                <a href="{{ route('transportistas.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-id-card"></i> Gestionar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Últimos Envíos y Rutas -->
<div class="row equal-height-cards">
    <div class="col-md-6 d-flex">
        <div class="card shadow flex-fill d-flex flex-column">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title"><i class="fas fa-shipping-fast"></i> Últimos Envíos</h3>
            </div>
            <div class="card-body d-flex flex-column flex-grow-1">
                <div class="table-responsive flex-grow-1">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estado</th>
                                <th>Transportista</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\Envio::with('asignacion.transportista')->latest()->take(5)->get() as $envio)
                            <tr>
                                <td><strong>#{{ $envio->id }}</strong></td>
                                <td>
                                    @if($envio->estado == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($envio->estado == 'asignado')
                                        <span class="badge badge-primary">Asignado</span>
                                    @elseif($envio->estado == 'en_transito')
                                        <span class="badge badge-info">En Tránsito</span>
                                    @elseif($envio->estado == 'entregado')
                                        <span class="badge badge-success">Entregado</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $envio->estado }}</span>
                                    @endif
                                </td>
                                <td>{{ optional($envio->asignacion)->transportista->name ?? 'Sin asignar' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No hay envíos</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 d-flex">
        <div class="card shadow flex-fill d-flex flex-column">
            <div class="card-header bg-gradient-purple" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);">
                <h3 class="card-title"><i class="fas fa-route"></i> Rutas Activas</h3>
            </div>
            <div class="card-body d-flex flex-column flex-grow-1">
                <div class="table-responsive flex-grow-1">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Ruta</th>
                                <th>Transportista</th>
                                <th>Envíos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                try {
                                    // Usar la tabla rutas que existe
                                    $rutasActivas = DB::table('rutas')
                                        ->orderByDesc('created_at')
                                        ->take(5)
                                        ->get();
                                    
                                    // Obtener número de envíos por ruta
                                    foreach ($rutasActivas as $ruta) {
                                        // Contar envíos que tienen esta ruta asignada
                                        $ruta->num_envios = DB::table('envios')
                                            ->where('ruta_entrega_id', $ruta->id)
                                            ->count();
                                    }
                                } catch (\Exception $e) {
                                    $rutasActivas = collect();
                                }
                            @endphp
                            @forelse($rutasActivas as $ruta)
                            <tr>
                                <td><strong>{{ $ruta->nombre ?? 'Ruta #'.$ruta->id }}</strong></td>
                                <td>N/A</td>
                                <td><span class="badge badge-primary">{{ $ruta->num_envios ?? 0 }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No hay rutas activas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .hover-card:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3) !important;
    }
    .small-box, .info-box {
        border-radius: 10px;
    }
    
    /* Cards de igual altura */
    .equal-height-cards {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
    }
    
    .equal-height-cards > [class*='col-'] {
        display: flex;
        flex-direction: column;
    }
    
    .equal-height-cards .card {
        margin-bottom: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .equal-height-cards .card-header {
        flex-shrink: 0;
    }
    
    .equal-height-cards .card-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    
    .equal-height-cards .table-responsive {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    
    .equal-height-cards table {
        margin-bottom: 0;
    }
</style>
@endsection
