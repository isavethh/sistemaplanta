@extends('adminlte::page')

@section('title', 'Dashboard Estadístico')

@section('adminlte_css_pre')
    @include('layouts.preloader-killer')
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-chart-line text-primary"></i> Dashboard Estadístico</h1>
            <small class="text-muted">Panel de control y métricas del sistema logístico</small>
        </div>
        <div>
            <span class="badge badge-primary p-2">
                <i class="fas fa-clock"></i> Actualizado: {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>
@endsection

@section('content')
<!-- KPIs Principales -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3>{{ number_format($kpis['total_envios']) }}</h3>
                <p>Total Envíos</p>
            </div>
            <div class="icon"><i class="fas fa-shipping-fast"></i></div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
                Ver todos <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ number_format($kpis['envios_mes']) }}</h3>
                <p>Envíos Este Mes 
                    @if($kpis['crecimiento_mensual'] > 0)
                        <span class="badge badge-light"><i class="fas fa-arrow-up text-success"></i> {{ $kpis['crecimiento_mensual'] }}%</span>
                    @elseif($kpis['crecimiento_mensual'] < 0)
                        <span class="badge badge-light"><i class="fas fa-arrow-down text-danger"></i> {{ abs($kpis['crecimiento_mensual']) }}%</span>
                    @endif
                </p>
            </div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
            <div class="small-box-footer">&nbsp;</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ number_format($kpis['en_transito']) }}</h3>
                <p>En Tránsito</p>
            </div>
            <div class="icon"><i class="fas fa-truck"></i></div>
            <div class="small-box-footer">&nbsp;</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>{{ number_format($kpis['incidentes_activos']) }}</h3>
                <p>Incidentes Activos</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="{{ route('incidentes.index') }}" class="small-box-footer">
                Ver incidentes <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Segunda fila de KPIs -->
<div class="row">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-box-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Hoy</span>
                <span class="info-box-number">{{ $kpis['envios_hoy'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box bg-gradient-olive">
            <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Entregados/Mes</span>
                <span class="info-box-number">{{ $kpis['entregados_mes'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box bg-gradient-secondary">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pendientes</span>
                <span class="info-box-number">{{ $kpis['pendientes'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box bg-gradient-purple">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Transportistas</span>
                <span class="info-box-number">{{ $kpis['total_transportistas'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box bg-gradient-teal">
            <span class="info-box-icon"><i class="fas fa-weight"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Peso/Mes (kg)</span>
                <span class="info-box-number">{{ number_format($kpis['peso_total_mes'], 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box bg-gradient-orange">
            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor/Mes (Bs)</span>
                <span class="info-box-number">{{ number_format($kpis['valor_total_mes'], 0) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Envíos por Estado (Dona) -->
    <div class="col-lg-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Envíos por Estado</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3"><i class="fas fa-info-circle"></i> <strong>¿Qué muestra?</strong> Distribución porcentual de todos los envíos según su estado actual (pendiente, asignado, en tránsito, entregado, cancelado). Te ayuda a identificar rápidamente cuellos de botella en el proceso logístico.</p>
                <canvas id="chartEstados" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico de Tendencia (Últimos 6 meses) -->
    <div class="col-lg-8">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Tendencia de Envíos (Últimos 6 Meses)</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3"><i class="fas fa-info-circle"></i> <strong>¿Qué muestra?</strong> Evolución temporal de envíos totales vs. envíos entregados en los últimos 6 meses. Permite identificar tendencias de crecimiento o decrecimiento, estacionalidad, y comparar el volumen total con las entregas exitosas mes a mes.</p>
                <canvas id="chartTendencia" height="125"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top 5 Almacenes -->
    <div class="col-lg-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-warehouse"></i> Top 5 Almacenes con Más Envíos</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3"><i class="fas fa-info-circle"></i> <strong>¿Qué muestra?</strong> Los 5 almacenes que más envíos reciben. Útil para planificación de rutas, asignación de recursos, y priorización de almacenes estratégicos. Identifica los destinos con mayor demanda logística.</p>
                <canvas id="chartAlmacenes" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Top 5 Transportistas -->
    <div class="col-lg-6">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy"></i> Top 5 Transportistas</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3"><i class="fas fa-info-circle"></i> <strong>¿Qué muestra?</strong> Los 5 transportistas con más entregas exitosas completadas. Permite identificar a los conductores más productivos y confiables, facilitando decisiones de asignación de envíos y reconocimiento de desempeño.</p>
                <canvas id="chartTransportistas" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Métricas de Rendimiento -->
    <div class="col-lg-4">
        <div class="card card-outline card-purple">
            <div class="card-header bg-gradient-purple text-white">
                <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> Métricas de Rendimiento</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3"><i class="fas fa-info-circle"></i> <strong>¿Qué muestra?</strong> KPIs operacionales clave: % de entregas exitosas, tiempo promedio de entrega, % de incidentes, y % de resolución de problemas. Indicadores esenciales para evaluar la eficiencia global del sistema logístico.</p>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-check-circle text-success"></i> Tasa de Entrega</span>
                        <strong>{{ $rendimiento['tasa_entrega'] }}%</strong>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" style="width: {{ $rendimiento['tasa_entrega'] }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-clock text-info"></i> Tiempo Promedio</span>
                        <strong>{{ $rendimiento['tiempo_promedio_texto'] }}</strong>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-info" style="width: {{ min(($rendimiento['tiempo_promedio_horas'] / 48) * 100, 100) }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-exclamation-triangle text-warning"></i> Tasa de Incidentes</span>
                        <strong>{{ $rendimiento['tasa_incidentes'] }}%</strong>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-warning" style="width: {{ $rendimiento['tasa_incidentes'] }}%"></div>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-wrench text-primary"></i> Tasa de Resolución</span>
                        <strong>{{ $rendimiento['tasa_resolucion'] }}%</strong>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-primary" style="width: {{ $rendimiento['tasa_resolucion'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incidentes por Tipo -->
    <div class="col-lg-4">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-circle"></i> Incidentes por Tipo</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3"><i class="fas fa-info-circle"></i> <strong>¿Qué muestra?</strong> Clasificación de incidentes por categoría (accidente, demora, robo, daño producto, clima, otro). Permite identificar los problemas más frecuentes y enfocar acciones preventivas específicas.</p>
                @if($incidentesPorTipo->count() > 0)
                <canvas id="chartIncidentes" height="200"></canvas>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <p class="text-muted">No hay incidentes registrados</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actividad Semanal -->
    <div class="col-lg-4">
        <div class="card card-outline card-teal">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-week"></i> Actividad por Día</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3"><i class="fas fa-info-circle"></i> <strong>¿Qué muestra?</strong> Volumen de envíos creados según el día de la semana (Lun-Dom). Ayuda a identificar patrones semanales de demanda, optimizar asignación de personal, y planificar capacidad logística por día.</p>
                <canvas id="chartSemanal" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Envíos Recientes -->
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Envíos Recientes</h3>
                <div class="card-tools">
                    <a href="{{ route('envios.index') }}" class="btn btn-sm btn-primary">
                        Ver Todos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Almacén Destino</th>
                            <th>Estado</th>
                            <th class="text-right">Peso</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enviosRecientes as $envio)
                        <tr>
                            <td><strong>{{ $envio->codigo }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($envio->fecha_creacion)->format('d/m/Y') }}</td>
                            <td>{{ $envio->almacen_nombre ?? 'N/A' }}</td>
                            <td>
                                @switch($envio->estado)
                                    @case('pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                        @break
                                    @case('asignado')
                                        <span class="badge badge-info">Asignado</span>
                                        @break
                                    @case('en_transito')
                                        <span class="badge badge-primary">En Tránsito</span>
                                        @break
                                    @case('entregado')
                                        <span class="badge badge-success">Entregado</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $envio->estado }}</span>
                                @endswitch
                            </td>
                            <td class="text-right">{{ number_format($envio->total_peso, 1) }} kg</td>
                            <td class="text-right">Bs {{ number_format($envio->total_precio, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No hay envíos recientes</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Acceso Rápido a Reportes -->
<div class="row">
    <div class="col-12">
        <div class="callout callout-info">
            <h5><i class="fas fa-file-alt"></i> Reportes Disponibles</h5>
            <div class="btn-group mt-2">
                <a href="{{ route('reportes.operaciones') }}" class="btn btn-primary">
                    <i class="fas fa-truck-loading"></i> Operaciones
                </a>
                <a href="{{ route('reportes.nota-entrega') }}" class="btn btn-success">
                    <i class="fas fa-file-signature"></i> Notas de Entrega
                </a>
                <a href="{{ route('reportes.incidentes') }}" class="btn btn-danger">
                    <i class="fas fa-exclamation-triangle"></i> Incidentes
                </a>
                <a href="{{ route('reportes.productividad') }}" class="btn btn-info">
                    <i class="fas fa-users-cog"></i> Productividad
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Colores del tema
    const colores = {
        primary: '#007bff',
        success: '#28a745',
        warning: '#ffc107',
        danger: '#dc3545',
        info: '#17a2b8',
        purple: '#6f42c1',
        teal: '#20c997',
        orange: '#fd7e14'
    };

    // 1. Gráfico de Estados (Dona)
    const datosEstados = @json($enviosPorEstado);
    const estadoColores = {
        'pendiente': colores.warning,
        'asignado': colores.info,
        'en_transito': colores.primary,
        'entregado': colores.success,
        'cancelado': colores.danger
    };
    
    new Chart(document.getElementById('chartEstados'), {
        type: 'doughnut',
        data: {
            labels: datosEstados.map(d => d.estado.charAt(0).toUpperCase() + d.estado.slice(1).replace('_', ' ')),
            datasets: [{
                data: datosEstados.map(d => d.total),
                backgroundColor: datosEstados.map(d => estadoColores[d.estado] || '#6c757d'),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } }
            }
        }
    });

    // 2. Gráfico de Tendencia (Línea)
    const datosTendencia = @json($tendenciaEnvios);
    
    new Chart(document.getElementById('chartTendencia'), {
        type: 'line',
        data: {
            labels: datosTendencia.map(d => d.mes),
            datasets: [
                {
                    label: 'Total Envíos',
                    data: datosTendencia.map(d => d.total),
                    borderColor: colores.primary,
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Entregados',
                    data: datosTendencia.map(d => d.entregados),
                    borderColor: colores.success,
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

    // 3. Gráfico de Almacenes (Barras horizontales)
    const datosAlmacenes = @json($topAlmacenes);
    
    new Chart(document.getElementById('chartAlmacenes'), {
        type: 'bar',
        data: {
            labels: datosAlmacenes.map(d => d.nombre),
            datasets: [{
                label: 'Envíos',
                data: datosAlmacenes.map(d => d.total),
                backgroundColor: [colores.primary, colores.info, colores.teal, colores.purple, colores.success],
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    // 4. Gráfico de Transportistas (Barras)
    const datosTransportistas = @json($topTransportistas);
    
    new Chart(document.getElementById('chartTransportistas'), {
        type: 'bar',
        data: {
            labels: datosTransportistas.map(d => d.name),
            datasets: [{
                label: 'Entregas',
                data: datosTransportistas.map(d => d.entregas),
                backgroundColor: [colores.warning, '#c0c0c0', '#cd7f32', colores.info, colores.purple],
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    // 5. Gráfico de Incidentes (Polar)
    @if($incidentesPorTipo->count() > 0)
    const datosIncidentes = @json($incidentesPorTipo);
    
    new Chart(document.getElementById('chartIncidentes'), {
        type: 'polarArea',
        data: {
            labels: datosIncidentes.map(d => d.tipo_incidente.replace('_', ' ')),
            datasets: [{
                data: datosIncidentes.map(d => d.total),
                backgroundColor: [
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(253, 126, 20, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(111, 66, 193, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }
        }
    });
    @endif

    // 6. Gráfico Semanal (Barras)
    const datosSemanal = @json($actividadSemanal);
    
    new Chart(document.getElementById('chartSemanal'), {
        type: 'bar',
        data: {
            labels: datosSemanal.map(d => d.dia.substring(0, 3)),
            datasets: [{
                label: 'Envíos',
                data: datosSemanal.map(d => d.total),
                backgroundColor: colores.teal,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endsection

@section('css')
<style>
    .small-box { border-radius: 10px; }
    .info-box { border-radius: 10px; }
    .card-outline { border-top-width: 3px; }
    .progress { border-radius: 10px; height: 25px; }
    .progress-bar { border-radius: 10px; }
</style>
@endsection

