@extends('adminlte::page')

@section('title', 'Reporte de Incidentes')

@section('adminlte_css_pre')
    @include('layouts.preloader-killer')
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-exclamation-triangle text-danger"></i> Reporte de Incidentes</h1>
            <small class="text-muted">Período: {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</small>
        </div>
        <a href="{{ route('reportes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@endsection

@section('content')
<!-- Filtros -->
<div class="card card-outline card-danger mb-4">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-filter"></i> Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.incidentes') }}">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" 
                               value="{{ $filtros['fecha_inicio'] }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" 
                               value="{{ $filtros['fecha_fin'] }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo de Incidente</label>
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
                <div class="col-md-3">
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

<div class="row">
    <!-- Distribución por Tipo -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-chart-pie"></i> Por Tipo de Incidente</h5>
            </div>
            <div class="card-body">
                <canvas id="chartTipos" height="250"></canvas>
                <hr>
                <ul class="list-group list-group-flush">
                    @forelse($porTipo as $tipo)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ ucfirst(str_replace('_', ' ', $tipo->tipo_incidente)) }}
                        <span class="badge badge-danger badge-pill">{{ $tipo->total }}</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">Sin datos</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Tiempo de Resolución y Exportación -->
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-gradient-purple mb-0">
                            <span class="info-box-icon"><i class="fas fa-stopwatch"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tiempo Promedio de Resolución</span>
                                <span class="info-box-number">{{ $estadisticas['tiempo_promedio_resolucion'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-column h-100 justify-content-center">
                            <strong class="mb-2">Exportar Reporte:</strong>
                            <div class="btn-group">
                                <a href="{{ route('reportes.incidentes.pdf', request()->all()) }}" class="btn btn-danger">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                <a href="{{ route('reportes.incidentes.csv', request()->all()) }}" class="btn btn-success">
                                    <i class="fas fa-file-csv"></i> CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Incidentes -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-list"></i> Detalle de Incidentes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Envío</th>
                                <th>Transportista</th>
                                <th>Estado</th>
                                <th style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incidentes as $inc)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($inc->fecha_reporte)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-danger">
                                        {{ ucfirst(str_replace('_', ' ', $inc->tipo_incidente)) }}
                                    </span>
                                </td>
                                <td>{{ $inc->envio_codigo ?? 'N/A' }}</td>
                                <td>{{ $inc->transportista_nombre ?? 'N/A' }}</td>
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
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <p class="text-muted">No hay incidentes en el período</p>
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
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartTipos');
    if (ctx) {
        const datos = @json($porTipo);
        const labels = datos.map(d => d.tipo_incidente.replace('_', ' ').charAt(0).toUpperCase() + d.tipo_incidente.replace('_', ' ').slice(1));
        const values = datos.map(d => d.total);
        const colors = ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#17a2b8', '#6f42c1', '#e83e8c'];
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors.slice(0, values.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12 }
                    }
                }
            }
        });
    }
});
</script>
@endsection

@section('css')
<style>
    .bg-gradient-purple {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
    }
</style>
@endsection

