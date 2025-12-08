@extends('adminlte::page')

@section('title', 'Documentos de Entrega - ' . ($ruta['codigo'] ?? 'Ruta'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-file-invoice text-success"></i> 
            Documentos de Entrega
        </h1>
        <a href="{{ route('rutas-multi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<!-- Información de la Ruta -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-route"></i> {{ $ruta['codigo'] ?? 'Sin código' }}
                </h3>
                <div class="card-tools">
                    @php
                        $estadoClases = [
                            'pendiente' => 'badge-secondary',
                            'aceptada' => 'badge-info',
                            'en_transito' => 'badge-warning',
                            'completada' => 'badge-success',
                            'rechazada' => 'badge-danger',
                        ];
                        $estado = $ruta['estado'] ?? 'pendiente';
                    @endphp
                    <span class="badge {{ $estadoClases[$estado] ?? 'badge-secondary' }} badge-lg">
                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong><i class="fas fa-user"></i> Transportista:</strong><br>
                        {{ $ruta['transportista']['name'] ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong><i class="fas fa-truck"></i> Vehículo:</strong><br>
                        {{ $ruta['vehiculo']['placa'] ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong><i class="fas fa-calendar"></i> Fecha:</strong><br>
                        {{ isset($ruta['fecha']) ? date('d/m/Y', strtotime($ruta['fecha'])) : 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong><i class="fas fa-boxes"></i> Total Paradas:</strong><br>
                        {{ count($paradas) }} entregas
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de Progreso -->
<div class="row mb-4">
    <div class="col-12">
        @php
            $completadas = collect($paradas)->whereIn('estado', ['entregado', 'completado', 'completada'])->count();
            $total = count($paradas);
            $porcentaje = $total > 0 ? round(($completadas / $total) * 100) : 0;
        @endphp
        <div class="progress" style="height: 30px;">
            <div class="progress-bar bg-success progress-bar-striped" 
                 role="progressbar" 
                 style="width: {{ $porcentaje }}%;" 
                 aria-valuenow="{{ $porcentaje }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <strong>{{ $completadas }}/{{ $total }} entregas completadas ({{ $porcentaje }}%)</strong>
            </div>
        </div>
    </div>
</div>

<!-- Documentos por Parada -->
<div class="row">
    @forelse($paradas as $index => $parada)
        <div class="col-md-6 mb-4">
            <div class="card {{ in_array($parada['estado'], ['entregado', 'completado', 'completada']) ? 'card-success' : 'card-secondary' }}">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge badge-light mr-2">#{{ $parada['orden'] }}</span>
                        {{ $parada['envio_codigo'] }}
                    </h3>
                    <div class="card-tools">
                        @php
                            $estadoParada = $parada['estado'];
                            $claseEstado = match($estadoParada) {
                                'entregado', 'completado', 'completada' => 'badge-success',
                                'en_camino' => 'badge-warning',
                                'llegada' => 'badge-info',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $claseEstado }}">
                            {{ ucfirst(str_replace('_', ' ', $estadoParada)) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información del destino -->
                    <div class="mb-3">
                        <strong><i class="fas fa-map-marker-alt text-danger"></i> Destino:</strong>
                        <p class="mb-1">{{ $parada['destino'] }}</p>
                        <small class="text-muted">{{ $parada['direccion'] }}</small>
                    </div>

                    <!-- Horarios -->
                    @if($parada['hora_llegada'] || $parada['hora_entrega'])
                        <div class="mb-3">
                            <div class="row">
                                @if($parada['hora_llegada'])
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> Llegada:
                                        </small><br>
                                        {{ date('H:i', strtotime($parada['hora_llegada'])) }}
                                    </div>
                                @endif
                                @if($parada['hora_entrega'])
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-check-circle"></i> Entrega:
                                        </small><br>
                                        {{ date('H:i', strtotime($parada['hora_entrega'])) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Checklist -->
                    <div class="mb-3">
                        <strong><i class="fas fa-tasks text-primary"></i> Checklist de Entrega:</strong>
                        <ul class="list-unstyled mt-2">
                            @foreach($parada['checklist'] as $item)
                                <li class="mb-1">
                                    @if($item['completado'])
                                        <i class="fas {{ $item['icono'] }} text-success"></i>
                                    @else
                                        <i class="far fa-circle text-muted"></i>
                                    @endif
                                    <span class="{{ $item['completado'] ? '' : 'text-muted' }}">
                                        {{ $item['item'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Notas -->
                    @if($parada['notas'])
                        <div class="mb-3">
                            <strong><i class="fas fa-sticky-note text-warning"></i> Notas:</strong>
                            <p class="mb-0 mt-1 p-2 bg-light rounded">{{ $parada['notas'] }}</p>
                        </div>
                    @endif

                    <!-- Fotos de Evidencia -->
                    @if(count($parada['fotos']) > 0)
                        <div class="mb-3">
                            <strong><i class="fas fa-camera text-info"></i> Fotos de Evidencia:</strong>
                            <div class="row mt-2">
                                @foreach($parada['fotos'] as $foto)
                                    <div class="col-4 mb-2">
                                        <a href="{{ $foto['url'] }}" target="_blank" data-toggle="lightbox">
                                            <img src="{{ $foto['url'] }}" 
                                                 class="img-thumbnail" 
                                                 alt="{{ $foto['descripcion'] }}"
                                                 style="width: 100%; height: 80px; object-fit: cover;">
                                        </a>
                                        @if($foto['descripcion'])
                                            <small class="d-block text-muted">{{ $foto['descripcion'] }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mb-3">
                            <strong><i class="fas fa-camera text-info"></i> Fotos de Evidencia:</strong>
                            <p class="text-muted mb-0 mt-1">
                                <i class="fas fa-info-circle"></i> Sin fotos registradas
                            </p>
                        </div>
                    @endif

                    <!-- Firma -->
                    @if($parada['firma'])
                        <div class="mb-3">
                            <strong><i class="fas fa-signature text-success"></i> Firma del Receptor:</strong>
                            <div class="mt-2 text-center bg-light p-2 rounded">
                                <img src="{{ $parada['firma'] }}" 
                                     class="img-fluid" 
                                     alt="Firma"
                                     style="max-height: 100px;">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-center">
                    @if(in_array($parada['estado'], ['entregado', 'completado', 'completada']))
                        <span class="text-success">
                            <i class="fas fa-check-double"></i> Entrega Verificada
                        </span>
                    @else
                        <span class="text-muted">
                            <i class="fas fa-hourglass-half"></i> Pendiente de Entrega
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                No hay paradas registradas para esta ruta.
            </div>
        </div>
    @endforelse
</div>

<!-- Botones de Acción -->
<div class="row mt-4">
    <div class="col-12 text-center">
        <a href="{{ route('rutas-multi.show', $ruta['id']) }}" class="btn btn-info btn-lg mr-2">
            <i class="fas fa-eye"></i> Ver Detalle de Ruta
        </a>
        @if(($ruta['estado'] ?? '') == 'completada')
            <a href="{{ route('rutas-multi.resumen', $ruta['id']) }}" class="btn btn-success btn-lg">
                <i class="fas fa-file-pdf"></i> Generar Resumen PDF
            </a>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .card-header .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    .progress {
        border-radius: 15px;
    }
    .progress-bar {
        border-radius: 15px;
    }
    .img-thumbnail {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .img-thumbnail:hover {
        transform: scale(1.05);
    }
    .list-unstyled li {
        padding: 3px 0;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Lightbox para imágenes
        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            window.open($(this).attr('href'), '_blank');
        });
    });
</script>
@stop
