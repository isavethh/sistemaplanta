@extends('adminlte::page')

@section('title', 'Mis Incidentes')

@section('adminlte_css_pre')
    @include('layouts.preloader-killer')
@endsection

@section('content_header')
    <h1 class="m-0"><i class="fas fa-exclamation-triangle text-danger"></i> Mis Incidentes Reportados</h1>
    <small class="text-muted">Período: {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</small>
@endsection

@section('content')
<!-- Filtros -->
<div class="card card-outline card-danger mb-4">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-filter"></i> Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.mis-incidentes') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $filtros['fecha_inicio'] }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $filtros['fecha_fin'] }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo" class="form-control">
                            <option value="">Todos</option>
                            @foreach($tiposIncidente as $tipo)
                                <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $tipo)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                            <option value="resuelto" {{ request('estado') == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Estadísticas -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>{{ $estadisticas['total'] }}</h3>
                <p>Total Incidentes</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ $estadisticas['pendientes'] }}</h3>
                <p>Pendientes</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ $estadisticas['en_proceso'] }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon"><i class="fas fa-spinner"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ $estadisticas['resueltos'] }}</h3>
                <p>Resueltos</p>
            </div>
            <div class="icon"><i class="fas fa-check"></i></div>
        </div>
    </div>
</div>

<!-- Acciones -->
<div class="row mb-3">
    <div class="col-12">
        <div class="btn-group">
            <a href="{{ route('reportes.mis-incidentes.create') }}" class="btn btn-danger">
                <i class="fas fa-exclamation-triangle"></i> Reportar Nuevo Incidente
            </a>
            <a href="{{ route('reportes.mis-incidentes.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
            <a href="{{ route('reportes.mis-incidentes.csv', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
        </div>
    </div>
</div>

<!-- Tabla de Incidentes -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-list"></i> Detalle de Mis Incidentes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Envío</th>
                        <th>Almacén</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidentes as $inc)
                    <tr class="{{ ($inc->solicitar_ayuda ?? false) ? 'table-warning' : '' }}">
                        <td>{{ \Carbon\Carbon::parse($inc->fecha_reporte)->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge badge-danger">
                                {{ ucfirst(str_replace('_', ' ', $inc->tipo_incidente)) }}
                            </span>
                            @if($inc->solicitar_ayuda ?? false)
                                <br><small class="badge badge-warning mt-1">
                                    <i class="fas fa-exclamation-triangle"></i> Solicita Ayuda
                                </small>
                            @endif
                        </td>
                        <td>{{ $inc->envio_codigo ?? 'N/A' }}</td>
                        <td>{{ $inc->almacen_nombre ?? 'N/A' }}</td>
                        <td>
                            {{ Str::limit($inc->descripcion, 50) }}
                            @if($inc->solicitar_ayuda ?? false)
                                <br><small class="text-warning">
                                    <i class="fas fa-bell"></i> Ayuda solicitada al administrador
                                </small>
                            @endif
                        </td>
                        <td>
                            @switch($inc->estado)
                                @case('pendiente')
                                    <span class="badge badge-warning">Pendiente</span>
                                    @break
                                @case('en_proceso')
                                    <span class="badge badge-info">En Proceso</span>
                                    @break
                                @case('resuelto')
                                    <span class="badge badge-success">Resuelto</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            @if($inc->estado === 'resuelto')
                                <a href="{{ route('reportes.resolucion-incidente', $inc->id) }}" 
                                   class="btn btn-success btn-sm" title="Ver Resolución">
                                    <i class="fas fa-file-contract"></i>
                                </a>
                                <a href="{{ route('reportes.resolucion-incidente.pdf', $inc->id) }}" 
                                   class="btn btn-danger btn-sm" title="PDF Resolución" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            @else
                                <span class="text-muted"><small>Sin resolución</small></span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">¡Excelente! No has reportado incidentes en este período</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($incidentes->hasPages())
    <div class="card-footer">
        {{ $incidentes->appends(request()->all())->links() }}
    </div>
    @endif
</div>
@endsection

