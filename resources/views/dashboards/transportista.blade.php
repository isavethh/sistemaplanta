@extends('adminlte::page')
@section('title', 'Dashboard Transportista')
@section('content_header')
    <h1 class="m-0"><i class="fas fa-truck"></i> Dashboard - Transportista</h1>
@endsection

@section('content')
<!-- Mensaje de Bienvenida -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5><i class="icon fas fa-steering-wheel"></i> ¡Bienvenido, {{ auth()->user()->name }}!</h5>
            Aquí puedes ver tus envíos asignados, rutas y documentos de entrega.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>

<!-- Estadísticas del Transportista -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3>{{ \App\Models\Envio::where('transportista_id', auth()->id())->count() }}</h3>
                <p>Envíos Asignados</p>
            </div>
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ \App\Models\Envio::where('transportista_id', auth()->id())->where('estado', 'pendiente')->count() }}</h3>
                <p>Por Recoger</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ \App\Models\Envio::where('transportista_id', auth()->id())->where('estado', 'en_transito')->count() }}</h3>
                <p>En Camino</p>
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
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ \App\Models\Envio::where('transportista_id', auth()->id())->where('estado', 'entregado')->whereDate('updated_at', today())->count() }}</h3>
                <p>Entregados Hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Acciones Rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-success">
                <h3 class="card-title text-white"><i class="fas fa-tasks"></i> Mis Acciones</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-list fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Mis Envíos</h5>
                                <a href="{{ route('envios.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-eye"></i> Ver Asignados
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-route fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Mis Rutas</h5>
                                <a href="{{ route('rutas-multi.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-map"></i> Ver Rutas
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-file-invoice fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Documentos</h5>
                                <a href="{{ route('notas-venta.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-receipt"></i> Ver Documentos
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Incidentes</h5>
                                <a href="{{ route('incidentes.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-plus"></i> Reportar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mis Envíos Pendientes -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-warning">
                <h3 class="card-title"><i class="fas fa-clock"></i> Mis Envíos Pendientes</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Fecha Asignación</th>
                                <th>Destino</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\Envio::where('transportista_id', auth()->id())->where('estado', '!=', 'entregado')->latest()->take(10)->get() as $envio)
                            <tr>
                                <td><strong>#{{ $envio->id }}</strong></td>
                                <td>{{ $envio->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $envio->direccion_destino ?? 'N/A' }}</td>
                                <td>
                                    @if($envio->estado == 'pendiente')
                                        <span class="badge badge-warning">Por Recoger</span>
                                    @elseif($envio->estado == 'en_transito')
                                        <span class="badge badge-info">En Camino</span>
                                    @elseif($envio->estado == 'en_ruta')
                                        <span class="badge badge-primary">En Ruta</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $envio->estado }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('envios.show', $envio->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                    <p class="mb-0">¡Excelente! No tienes envíos pendientes.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mis Rutas Activas -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title"><i class="fas fa-route"></i> Mis Rutas Activas</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Ruta</th>
                                <th>Envíos</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\RutaMultiEntrega::where('transportista_id', auth()->id())->latest()->take(5)->get() as $ruta)
                            <tr>
                                <td><strong>{{ $ruta->nombre }}</strong></td>
                                <td><span class="badge badge-primary">{{ $ruta->envios->count() }}</span></td>
                                <td>{{ $ruta->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('rutas-multi.show', $ruta->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No tienes rutas asignadas</td>
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
    .small-box {
        border-radius: 10px;
    }
</style>
@endsection
