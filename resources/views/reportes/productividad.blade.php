@extends('adminlte::page')

@section('title', 'Productividad de Transportistas')

@section('adminlte_css_pre')
    @include('layouts.preloader-killer')
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-users-cog text-info"></i> Productividad de Transportistas</h1>
            <small class="text-muted">Período: {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</small>
        </div>
        <a href="{{ route('reportes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@endsection

@section('content')
<!-- Filtros -->
<div class="card card-outline card-info mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.productividad') }}" class="form-inline">
            <div class="form-group mr-3">
                <label class="mr-2">Desde:</label>
                <input type="date" name="fecha_inicio" class="form-control" value="{{ $filtros['fecha_inicio'] }}">
            </div>
            <div class="form-group mr-3">
                <label class="mr-2">Hasta:</label>
                <input type="date" name="fecha_fin" class="form-control" value="{{ $filtros['fecha_fin'] }}">
            </div>
            <button type="submit" class="btn btn-info">
                <i class="fas fa-filter"></i> Aplicar
            </button>
        </form>
    </div>
</div>

<!-- Estadísticas Globales -->
<div class="row">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3>{{ $estadisticasGlobales['total_transportistas'] }}</h3>
                <p>Transportistas</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ $estadisticasGlobales['total_envios_periodo'] }}</h3>
                <p>Envíos Período</p>
            </div>
            <div class="icon"><i class="fas fa-shipping-fast"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ $estadisticasGlobales['total_entregas'] }}</h3>
                <p>Entregas</p>
            </div>
            <div class="icon"><i class="fas fa-check-double"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ $estadisticasGlobales['promedio_por_transportista'] }}</h3>
                <p>Promedio/Transportista</p>
            </div>
            <div class="icon"><i class="fas fa-calculator"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="small-box bg-gradient-teal">
            <div class="inner">
                <h3>{{ $estadisticasGlobales['tasa_efectividad_global'] }}%</h3>
                <p>Efectividad Global</p>
            </div>
            <div class="icon"><i class="fas fa-percentage"></i></div>
        </div>
    </div>
</div>

<!-- Exportar -->
<div class="row mb-3">
    <div class="col-12">
        <div class="btn-group">
            <a href="{{ route('reportes.productividad.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
            <a href="{{ route('reportes.productividad.csv', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
        </div>
    </div>
</div>

<!-- Gráfico de Ranking -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-chart-bar"></i> Ranking de Transportistas</h5>
            </div>
            <div class="card-body">
                @if($transportistas->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                        <p>No hay datos para mostrar el gráfico</p>
                    </div>
                @else
                    <canvas id="chartRanking" height="300"></canvas>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-gradient-success text-white">
                <h5 class="card-title m-0"><i class="fas fa-trophy"></i> Top 5 Transportistas</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($transportistas->take(5) as $index => $t)
                    <li class="list-group-item d-flex align-items-center">
                        <span class="badge badge-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'dark') }} mr-2" style="font-size: 1rem;">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-grow-1">
                            <strong>{{ $t->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $t->total_envios }} envíos | {{ $t->tasa_efectividad }}% efectividad</small>
                        </div>
                        @if($index == 0)
                            <i class="fas fa-crown text-warning fa-lg"></i>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Detallada -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-table"></i> Detalle por Transportista</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Transportista</th>
                        <th>Email</th>
                        <th class="text-center">Total Envíos</th>
                        <th class="text-center">Completados</th>
                        <th class="text-center">En Tránsito</th>
                        <th class="text-center">Incidentes</th>
                        <th class="text-center">Efectividad</th>
                        <th class="text-right">Peso (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transportistas as $index => $t)
                    <tr>
                        <td>
                            @if($index < 3)
                                <span class="badge badge-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'info') }}">
                                    {{ $index + 1 }}
                                </span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </td>
                        <td><strong>{{ $t->name }}</strong></td>
                        <td><small>{{ $t->email }}</small></td>
                        <td class="text-center">
                            <span class="badge badge-primary">{{ $t->total_envios }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-success">{{ $t->entregas_completadas }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $t->en_transito }}</span>
                        </td>
                        <td class="text-center">
                            @if($t->total_incidentes > 0)
                                <span class="badge badge-danger">{{ $t->total_incidentes }}</span>
                            @else
                                <span class="badge badge-secondary">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $t->tasa_efectividad >= 80 ? 'bg-success' : ($t->tasa_efectividad >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                     style="width: {{ $t->tasa_efectividad }}%;">
                                    {{ $t->tasa_efectividad }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-right">{{ number_format($t->total_peso_transportado, 1) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay datos de transportistas en el período seleccionado</h5>
                            <p class="text-muted mt-3">
                                <strong>Para que aparezcan datos, necesitas:</strong><br>
                                1. Tener transportistas creados en el sistema<br>
                                2. Asignar vehículos a los transportistas<br>
                                3. Asignar envíos a esos vehículos<br>
                                4. Que los envíos estén dentro del período seleccionado
                            </p>
                            <a href="{{ route('transportistas.index') }}" class="btn btn-info mt-2">
                                <i class="fas fa-user-plus"></i> Gestionar Transportistas
                            </a>
                            <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary mt-2">
                                <i class="fas fa-truck"></i> Gestionar Vehículos
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartRanking');
    if (ctx) {
        const datos = @json($transportistas->take(10));
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: datos.map(d => d.name),
                datasets: [
                    {
                        label: 'Entregas Completadas',
                        data: datos.map(d => d.entregas_completadas),
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    },
                    {
                        label: 'En Tránsito',
                        data: datos.map(d => d.en_transito),
                        backgroundColor: 'rgba(23, 162, 184, 0.8)',
                        borderColor: '#17a2b8',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
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
    .bg-gradient-teal {
        background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
        color: #fff;
    }
    .progress {
        border-radius: 10px;
    }
    .progress-bar {
        font-size: 0.75rem;
        font-weight: bold;
    }
</style>
@endsection

