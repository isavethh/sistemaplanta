@extends('adminlte::page')

@section('title', 'Mi Productividad')

@section('adminlte_css_pre')
    @include('layouts.preloader-killer')
@endsection

@section('content_header')
    <h1 class="m-0"><i class="fas fa-chart-line text-info"></i> Mi Productividad</h1>
    <small class="text-muted">Período: {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</small>
@endsection

@section('content')
<!-- Filtros -->
<div class="card card-outline card-info mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.mi-productividad') }}" class="form-inline">
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

<!-- Exportar -->
<div class="row mb-3">
    <div class="col-12">
        <div class="btn-group">
            <a href="{{ route('reportes.mi-productividad.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
            <a href="{{ route('reportes.mi-productividad.csv', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
        </div>
    </div>
</div>

<!-- Estadísticas Principales -->
<div class="row">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3>{{ $estadisticas->total_envios }}</h3>
                <p>Envíos Asignados</p>
            </div>
            <div class="icon"><i class="fas fa-shipping-fast"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ $estadisticas->entregas_completadas }}</h3>
                <p>Completados</p>
            </div>
            <div class="icon"><i class="fas fa-check-double"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ $estadisticas->en_transito }}</h3>
                <p>En Tránsito</p>
            </div>
            <div class="icon"><i class="fas fa-truck"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ $estadisticas->tasa_efectividad }}%</h3>
                <p>Efectividad</p>
            </div>
            <div class="icon"><i class="fas fa-percentage"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-gradient-teal">
            <div class="inner">
                <h3>{{ number_format($estadisticas->total_peso_transportado, 0) }}</h3>
                <p>Kg Transportados</p>
            </div>
            <div class="icon"><i class="fas fa-weight"></i></div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>{{ $estadisticas->total_incidentes }}</h3>
                <p>Incidentes</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

<!-- Gráfico de Tendencia -->
<div class="row cards-equal-height">
    <div class="col-lg-8">
        <div class="card card-outline card-success h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Mi Tendencia ({{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('M Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('M Y') }})</h3>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="flex-grow-1">
                    <canvas id="chartTendencia" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-purple h-100">
            <div class="card-header bg-gradient-purple text-white">
                <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Mi Rendimiento</h3>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-check-circle text-success"></i> Tasa de Entrega</span>
                        <strong>{{ $estadisticas->tasa_efectividad }}%</strong>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar {{ $estadisticas->tasa_efectividad >= 80 ? 'bg-success' : ($estadisticas->tasa_efectividad >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                             style="width: {{ $estadisticas->tasa_efectividad }}%;">
                            {{ $estadisticas->tasa_efectividad }}%
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row text-center mt-auto">
                    <div class="col-6 border-right">
                        <h4 class="text-success">{{ $estadisticas->entregas_completadas }}</h4>
                        <small class="text-muted">Entregas</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">{{ $estadisticas->total_incidentes }}</h4>
                        <small class="text-muted">Incidentes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mensaje motivacional -->
<div class="row">
    <div class="col-12">
        <div class="callout {{ $estadisticas->tasa_efectividad >= 80 ? 'callout-success' : 'callout-info' }}">
            @if($estadisticas->tasa_efectividad >= 90)
                <h5><i class="fas fa-star text-warning"></i> ¡Excelente Trabajo!</h5>
                <p>Tu tasa de efectividad es excepcional. ¡Sigue así!</p>
            @elseif($estadisticas->tasa_efectividad >= 80)
                <h5><i class="fas fa-thumbs-up"></i> ¡Muy Bien!</h5>
                <p>Estás haciendo un gran trabajo. Tu desempeño es muy bueno.</p>
            @elseif($estadisticas->tasa_efectividad >= 70)
                <h5><i class="fas fa-smile"></i> Buen Trabajo</h5>
                <p>Tu rendimiento es bueno, pero hay margen de mejora.</p>
            @else
                <h5><i class="fas fa-info-circle"></i> Oportunidad de Mejora</h5>
                <p>Trabajemos juntos para mejorar tu tasa de entregas exitosas.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const datosTendencia = @json($enviosPorMes);
    
    new Chart(document.getElementById('chartTendencia'), {
        type: 'line',
        data: {
            labels: datosTendencia.map(d => d.mes),
            datasets: [
                {
                    label: 'Total Envíos',
                    data: datosTendencia.map(d => d.total),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Entregados',
                    data: datosTendencia.map(d => d.entregados),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });
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
    .cards-equal-height {
        display: flex;
        align-items: stretch;
    }
    .cards-equal-height > [class*='col-'] {
        display: flex;
        flex-direction: column;
    }
    .cards-equal-height .card {
        display: flex;
        flex-direction: column;
        height: 100%;
        margin-bottom: 0;
    }
    .cards-equal-height .card-header {
        flex-shrink: 0;
        min-height: 50px;
        display: flex;
        align-items: center;
    }
    .cards-equal-height .card-body {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .cards-equal-height .card-body > * {
        flex-shrink: 0;
    }
    .cards-equal-height .card-body > .flex-grow-1 {
        flex: 1 1 auto;
        min-height: 0;
    }
</style>
@endsection

