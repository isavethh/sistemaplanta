@extends('adminlte::page')
@section('title', 'Dashboard Planta')
@section('content_header')
    <h1 class="m-0"><i class="fas fa-industry"></i> Dashboard - Planta</h1>
@endsection

@section('content')
<!-- Mensaje de Bienvenida -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <h5><i class="icon fas fa-info-circle"></i> ¡Bienvenido, {{ auth()->user()->name }}!</h5>
            Desde aquí puedes crear y gestionar tus envíos.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>

<!-- Estadísticas Principales -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ \App\Models\Envio::count() }}</h3>
                <p>Mis Envíos Totales</p>
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
                <h3>{{ \App\Models\Envio::where('estado', 'pendiente')->count() }}</h3>
                <p>Envíos Pendientes</p>
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
                <h3>{{ \App\Models\Envio::where('estado', 'en_transito')->count() }}</h3>
                <p>En Tránsito</p>
            </div>
            <div class="icon">
                <i class="fas fa-truck"></i>
            </div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3>{{ \App\Models\Envio::where('estado', 'entregado')->count() }}</h3>
                <p>Entregados</p>
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
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-plus-circle fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Crear Nuevo Envío</h5>
                                <a href="{{ route('envios.create') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-plus"></i> Crear Envío
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card bg-info hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-list fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Mis Envíos</h5>
                                <a href="{{ route('envios.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-eye"></i> Ver Envíos
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card bg-warning hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-file-invoice-dollar fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Notas de Venta</h5>
                                <a href="{{ route('notas-entrega.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-receipt"></i> Ver Notas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Últimos Envíos -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-secondary">
                <h3 class="card-title"><i class="fas fa-history"></i> Últimos Envíos Creados</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Destino</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\Envio::latest()->take(5)->get() as $envio)
                            <tr>
                                <td><strong>#{{ $envio->id }}</strong></td>
                                <td>{{ $envio->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $envio->direccion_destino ?? 'N/A' }}</td>
                                <td>
                                    @if($envio->estado == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($envio->estado == 'en_transito')
                                        <span class="badge badge-info">En Tránsito</span>
                                    @elseif($envio->estado == 'entregado')
                                        <span class="badge badge-success">Entregado</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $envio->estado }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('envios.show', $envio->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay envíos registrados</td>
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
