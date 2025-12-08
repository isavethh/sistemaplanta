@extends('adminlte::page')

@section('title', 'Rutas Multi-Entrega')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-route text-primary"></i> Rutas Multi-Entrega</h1>
        <div>
            <a href="{{ route('rutas-multi.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nueva Ruta
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row g-3">
    <!-- Filtros -->
    <div class="col-12 mb-2">
        <div class="card card-outline card-primary">
            <div class="card-header py-2">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <label class="mr-2">Estado:</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="programada" {{ request('estado') == 'programada' ? 'selected' : '' }}>Programada</option>
                            <option value="aceptada" {{ request('estado') == 'aceptada' ? 'selected' : '' }}>Aceptada</option>
                            <option value="rechazada" {{ request('estado') == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                            <option value="en_transito" {{ request('estado') == 'en_transito' ? 'selected' : '' }}>En Tránsito</option>
                            <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completada</option>
                        </select>
                    </div>
                    <div class="form-group mr-3">
                        <label class="mr-2">Fecha:</label>
                        <input type="date" name="fecha" class="form-control form-control-sm" 
                               value="{{ request('fecha') }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('rutas-multi.index') }}" class="btn btn-sm btn-secondary ml-2">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </form>
            </div>
        </div>
    </div>

    <!-- Rutas en tránsito -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck-loading text-warning"></i> 
                    Rutas en Tránsito
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Transportista</th>
                            <th>Vehículo</th>
                            <th>Envíos</th>
                            <th>Progreso</th>
                            <th>Hora Salida</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rutasEnTransito = collect($rutas)->where('estado', 'en_transito');
                        @endphp
                        @forelse($rutasEnTransito as $ruta)
                            @php
                                $progreso = $ruta['total_paradas'] > 0 
                                    ? round(($ruta['paradas_completadas'] / $ruta['total_paradas']) * 100) 
                                    : 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $ruta['codigo'] }}</strong></td>
                                <td>{{ $ruta['transportista_nombre'] ?? 'N/A' }}</td>
                                <td>{{ $ruta['vehiculo_placa'] ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $ruta['total_envios'] ?? 0 }} envíos</span>
                                </td>
                                <td style="min-width: 150px;">
                                    <div class="progress">
                                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                             style="width: {{ $progreso }}%">
                                            {{ $ruta['paradas_completadas'] ?? 0 }}/{{ $ruta['total_paradas'] ?? 0 }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if(isset($ruta['hora_salida']))
                                        {{ date('H:i', strtotime($ruta['hora_salida'])) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-truck fa-spin"></i> En Tránsito
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('rutas-multi.show', $ruta['id']) }}" 
                                       class="btn btn-sm btn-info" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('rutas-multi.documentos', $ruta['id']) }}" 
                                       class="btn btn-sm btn-success" title="Documentos de entrega">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    <a href="{{ route('rutas-multi.resumen', $ruta['id']) }}" 
                                       class="btn btn-sm btn-secondary" title="Ver resumen">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-truck fa-2x mb-2"></i><br>
                                    No hay rutas en tránsito
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Rutas aceptadas (listas para iniciar) -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-thumbs-up"></i> Aceptadas (Listas para iniciar)
                </h3>
                <span class="badge badge-light float-right">
                    {{ collect($rutas)->where('estado', 'aceptada')->count() }}
                </span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @php
                        $rutasAceptadas = collect($rutas)->where('estado', 'aceptada')->take(10);
                    @endphp
                    @forelse($rutasAceptadas as $ruta)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $ruta['codigo'] }}</strong>
                                <span class="badge badge-primary ml-2">ACEPTADA</span><br>
                                <small class="text-muted">
                                    {{ $ruta['transportista_nombre'] ?? 'Sin asignar' }} • 
                                    {{ $ruta['total_envios'] ?? 0 }} envíos
                                </small>
                            </div>
                            <a href="{{ route('rutas-multi.show', $ruta['id']) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">
                            Sin rutas aceptadas
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Rutas rechazadas -->
    <div class="col-md-6">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-times-circle"></i> Rechazadas
                </h3>
                <span class="badge badge-light float-right">
                    {{ collect($rutas)->where('estado', 'rechazada')->count() }}
                </span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @php
                        $rutasRechazadas = collect($rutas)->where('estado', 'rechazada')->take(10);
                    @endphp
                    @forelse($rutasRechazadas as $ruta)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $ruta['codigo'] }}</strong>
                                <span class="badge badge-danger ml-2">RECHAZADA</span><br>
                                <small class="text-muted">
                                    {{ $ruta['observaciones'] ?? 'Sin motivo especificado' }}
                                </small>
                            </div>
                            <a href="{{ route('rutas-multi.show', $ruta['id']) }}" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-eye"></i> Reasignar
                            </a>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">
                            Sin rutas rechazadas
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Rutas programadas (esperando respuesta) -->
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i> Programadas (Esperando respuesta)
                </h3>
                <span class="badge badge-light float-right">
                    {{ collect($rutas)->where('estado', 'programada')->count() }}
                </span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @php
                        $rutasProgramadas = collect($rutas)->where('estado', 'programada')->take(10);
                    @endphp
                    @forelse($rutasProgramadas as $ruta)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $ruta['codigo'] }}</strong>
                                <span class="badge badge-warning ml-2">ESPERANDO</span><br>
                                <small class="text-muted">
                                    {{ $ruta['transportista_nombre'] ?? 'Sin asignar' }} • 
                                    {{ $ruta['total_envios'] ?? 0 }} envíos
                                </small>
                            </div>
                            <a href="{{ route('rutas-multi.show', $ruta['id']) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-eye"></i>
                            </a>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">
                            Sin rutas programadas
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Rutas completadas -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-check-circle"></i> Completadas
                </h3>
                <span class="badge badge-light float-right">
                    {{ collect($rutas)->where('estado', 'completada')->count() }}
                </span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @php
                        $rutasCompletadas = collect($rutas)->where('estado', 'completada')->take(10);
                    @endphp
                    @forelse($rutasCompletadas as $ruta)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $ruta['codigo'] }}</strong><br>
                                <small class="text-muted">
                                    {{ $ruta['transportista_nombre'] ?? 'N/A' }} • 
                                    {{ $ruta['total_envios'] ?? 0 }} envíos
                                </small>
                            </div>
                            <div>
                                <a href="{{ route('rutas-multi.documentos', $ruta['id']) }}" class="btn btn-sm btn-success" title="Documentos de entrega">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                                <a href="{{ route('rutas-multi.resumen', $ruta['id']) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-file-pdf"></i> Resumen
                                </a>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">
                            Sin rutas completadas
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .progress {
        height: 20px;
        border-radius: 10px;
    }
    .progress-bar {
        font-size: 11px;
        line-height: 20px;
    }
    /* Eliminar espacios en blanco entre tarjetas */
    .row {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    .card {
        margin-bottom: 15px !important;
    }
    .col-12, .col-md-6 {
        padding-top: 0 !important;
        padding-bottom: 5px !important;
    }
    /* Asegurar que no haya espacios vacíos grandes */
    .content-wrapper {
        padding-bottom: 15px !important;
    }
</style>
@stop
