@extends('adminlte::page')

@section('title', 'Trazabilidad - ' . $envio->codigo)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-route text-primary"></i> Trazabilidad Completa - {{ $envio->codigo }}</h1>
        <div>
            <a href="{{ route('reportes.trazabilidad.pdf', $envio->id) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
            <a href="{{ route('envios.show', $envio->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="card shadow-lg">
    <div class="card-header bg-gradient-primary text-white">
        <h3 class="card-title"><i class="fas fa-clipboard-list"></i> <strong>REPORTE DE TRAZABILIDAD COMPLETA</strong></h3>
    </div>
    <div class="card-body">
        <!-- Encabezado del Envío -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h4 class="text-primary mb-3"><i class="fas fa-box"></i> {{ $envio->codigo }}</h4>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="200"><strong><i class="fas fa-industry"></i> Origen:</strong></td>
                        <td>{{ $planta->nombre ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong><i class="fas fa-warehouse"></i> Destino:</strong></td>
                        <td>{{ $envio->almacenDestino->nombre ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong><i class="fas fa-calendar"></i> Fecha Creación:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($envio->fecha_creacion)->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @if($envio->fecha_entrega)
                    <tr>
                        <td><strong><i class="fas fa-calendar-check"></i> Fecha Entrega:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($envio->fecha_entrega)->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="col-md-4 text-center">
                <div class="card bg-{{ $envio->estado == 'entregado' ? 'success' : ($envio->estado == 'en_transito' ? 'warning' : 'secondary') }}">
                    <div class="card-body">
                        <h3 class="text-white mb-0">
                            @if($envio->estado == 'pendiente') <i class="fas fa-clock"></i> PENDIENTE
                            @elseif($envio->estado == 'asignado') <i class="fas fa-user-check"></i> ASIGNADO
                            @elseif($envio->estado == 'en_transito') <i class="fas fa-truck"></i> EN TRÁNSITO
                            @elseif($envio->estado == 'entregado') <i class="fas fa-check-circle"></i> ENTREGADO
                            @else {{ strtoupper($envio->estado) }}
                            @endif
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- LÍNEA DE TIEMPO -->
        <h4 class="text-center mb-4"><i class="fas fa-history"></i> LÍNEA DE TIEMPO</h4>

        <div class="timeline">
            @forelse($envio->historial as $evento)
            <div class="time-label">
                <span class="bg-{{ $evento->color }}">
                    {{ $evento->icono }} {{ \Carbon\Carbon::parse($evento->fecha_hora)->format('d/m/Y H:i:s') }}
                </span>
            </div>
            <div>
                <i class="fas fa-circle bg-{{ $evento->color }}"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header">
                        <strong>{{ strtoupper(str_replace('_', ' ', $evento->evento)) }}</strong>
                    </h3>
                    <div class="timeline-body">
                        @if($evento->descripcion)
                        <p class="mb-2">{{ $evento->descripcion }}</p>
                        @endif
                        
                        @if($evento->usuario)
                        <small class="text-muted">
                            <i class="fas fa-user"></i> Por: {{ $evento->usuario->name }}
                        </small>
                        @endif

                        @if($evento->datos_extra)
                        <div class="mt-2">
                            @if(isset($evento->datos_extra['latitud']) && isset($evento->datos_extra['longitud']))
                            <small class="text-info">
                                <i class="fas fa-map-marker-alt"></i> 
                                Ubicación: {{ $evento->datos_extra['latitud'] }}, {{ $evento->datos_extra['longitud'] }}
                            </small>
                            @endif
                            
                            @if(isset($evento->datos_extra['vehiculo']))
                            <br><small class="text-secondary">
                                <i class="fas fa-truck"></i> Vehículo: {{ $evento->datos_extra['vehiculo'] }}
                            </small>
                            @endif

                            @if(isset($evento->datos_extra['ip']))
                            <br><small class="text-muted">
                                <i class="fas fa-network-wired"></i> IP: {{ $evento->datos_extra['ip'] }}
                            </small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No hay eventos registrados en el historial aún.
            </div>
            @endforelse

            <div>
                <i class="fas fa-clock bg-gray"></i>
            </div>
        </div>

        <!-- Incidentes (si existen) -->
        @if($incidentes->count() > 0)
        <hr class="my-4">
        <h4 class="text-danger mb-3"><i class="fas fa-exclamation-triangle"></i> Incidentes Reportados</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incidentes as $inc)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($inc->created_at)->format('d/m/Y H:i') }}</td>
                        <td><span class="badge badge-warning">{{ ucfirst($inc->tipo_incidente) }}</span></td>
                        <td>{{ $inc->descripcion }}</td>
                        <td>
                            @if($inc->estado == 'resuelto')
                            <span class="badge badge-success">Resuelto</span>
                            @else
                            <span class="badge badge-danger">{{ ucfirst($inc->estado) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Resumen Final -->
        <hr class="my-4">
        <h4 class="text-center mb-3"><i class="fas fa-chart-bar"></i> RESUMEN</h4>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card bg-info">
                    <div class="card-body">
                        <h5 class="text-white mb-0">{{ $tiempoTotal ?? 'N/A' }}</h5>
                        <small class="text-white">Tiempo Total</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning">
                    <div class="card-body">
                        <h5 class="text-white mb-0">{{ $tiempoTransito ?? 'N/A' }}</h5>
                        <small class="text-white">En Tránsito</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger">
                    <div class="card-body">
                        <h5 class="text-white mb-0">{{ $incidentes->count() }}</h5>
                        <small class="text-white">Incidentes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success">
                    <div class="card-body">
                        <h5 class="text-white mb-0">
                            @if($envio->estado == 'entregado') ✅ COMPLETADO
                            @else 🔄 EN PROCESO
                            @endif
                        </h5>
                        <small class="text-white">Estado Final</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <hr class="my-4">
        <h4 class="mb-3"><i class="fas fa-boxes"></i> Productos del Envío</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Peso Unit.</th>
                        <th>Total Peso</th>
                        <th>Precio Unit.</th>
                        <th>Total Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($envio->productos as $prod)
                    <tr>
                        <td>{{ $prod->producto_nombre }}</td>
                        <td>{{ $prod->cantidad }}</td>
                        <td>{{ number_format($prod->peso_unitario, 2) }} kg</td>
                        <td><strong>{{ number_format($prod->total_peso, 2) }} kg</strong></td>
                        <td>Bs {{ number_format($prod->precio_unitario, 2) }}</td>
                        <td><strong>Bs {{ number_format($prod->total_precio, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="thead-dark">
                    <tr>
                        <td colspan="3" class="text-right"><strong>TOTALES:</strong></td>
                        <td><strong>{{ number_format($envio->total_peso, 2) }} kg</strong></td>
                        <td></td>
                        <td><strong>Bs {{ number_format($envio->total_precio, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > div {
    margin-bottom: 15px;
    position: relative;
}

.timeline > div > .timeline-item {
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    border-radius: 3px;
    margin-top: 0;
    background: #fff;
    color: #444;
    margin-left: 60px;
    margin-right: 15px;
    padding: 0;
    position: relative;
}

.timeline > div > .fa, .timeline > div > .fas, .timeline > div > .far, .timeline > div > .fab, .timeline > div > .fal, .timeline > div > .fad, .timeline > div > .svg-inline--fa, .timeline > div > .ion {
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    position: absolute;
    color: #666;
    background: #d2d6de;
    border-radius: 50%;
    text-align: center;
    left: 18px;
    top: 0;
}

.timeline > .time-label > span {
    font-weight: 600;
    padding: 5px;
    display: inline-block;
    background-color: #fff;
    border-radius: 4px;
}

.timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-size: 16px;
    line-height: 1.1;
}

.timeline-body {
    padding: 10px;
}
</style>
@endsection

