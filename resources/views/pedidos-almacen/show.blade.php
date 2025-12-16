@extends('adminlte::page')
@section('title', 'Pedido - ' . $pedido->codigo)
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-shopping-cart"></i> Pedido {{ $pedido->codigo }}</h1>
        <div>
            <a href="{{ route('pedidos-almacen.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Información Principal -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-info-circle"></i> Información del Pedido</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-barcode"></i> Código:</strong></div>
                    <div class="col-md-8">{{ $pedido->codigo }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-warehouse"></i> Almacén:</strong></div>
                    <div class="col-md-8">{{ $pedido->almacen->nombre ?? 'N/A' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-calendar"></i> Fecha Requerida:</strong></div>
                    <div class="col-md-8">{{ $pedido->fecha_requerida->format('d/m/Y') }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-clock"></i> Hora Requerida:</strong></div>
                    <div class="col-md-8">{{ $pedido->hora_requerida ?? 'N/A' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-map-marker-alt"></i> Ubicación:</strong></div>
                    <div class="col-md-8">{{ $pedido->direccion_completa ?? 'N/A' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-info-circle"></i> Estado:</strong></div>
                    <div class="col-md-8">
                        @php
                            $estadoColors = [
                                'pendiente' => 'secondary',
                                'enviado_trazabilidad' => 'info',
                                'aceptado_trazabilidad' => 'primary',
                                'propuesta_enviada' => 'warning',
                                'propuesta_aceptada' => 'success',
                                'cancelado' => 'danger',
                                'entregado' => 'success',
                            ];
                            $color = $estadoColors[$pedido->estado] ?? 'secondary';
                        @endphp
                        <span class="badge badge-{{ $color }} badge-lg">
                            {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                        </span>
                    </div>
                </div>

                @if($pedido->observaciones)
                <hr>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong><i class="fas fa-comment"></i> Observaciones:</strong>
                        <p class="mt-2">{{ $pedido->observaciones }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Productos -->
        <div class="card shadow mt-3">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title text-white"><i class="fas fa-boxes"></i> Productos del Pedido</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Peso Unit. (kg)</th>
                            <th>Precio Unit.</th>
                            <th>Total Peso (kg)</th>
                            <th>Total Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->productos as $producto)
                        <tr>
                            <td>{{ $producto->producto_nombre }}</td>
                            <td>{{ $producto->cantidad }}</td>
                            <td>{{ number_format($producto->peso_unitario, 2) }}</td>
                            <td>Bs {{ number_format($producto->precio_unitario, 2) }}</td>
                            <td>{{ number_format($producto->total_peso, 2) }}</td>
                            <td>Bs {{ number_format($producto->total_precio, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold bg-light">
                            <td colspan="4" class="text-right">TOTALES:</td>
                            <td>{{ number_format($pedido->productos->sum('total_peso'), 2) }} kg</td>
                            <td>Bs {{ number_format($pedido->productos->sum('total_precio'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Envío Asociado -->
        @if($pedido->envio)
        <div class="card shadow mb-3">
            <div class="card-header bg-gradient-success">
                <h5 class="card-title text-white"><i class="fas fa-shipping-fast"></i> Envío Asociado</h5>
            </div>
            <div class="card-body">
                <p><strong>Código:</strong> {{ $pedido->envio->codigo }}</p>
                <p><strong>Estado:</strong> 
                    @if($pedido->envio->estado == 'pendiente_aprobacion_trazabilidad')
                        <span class="badge badge-purple">
                            <i class="fas fa-hourglass-half"></i> Pendiente Aprobación Trazabilidad
                        </span>
                    @elseif($pedido->envio->estado == 'entregado')
                        <span class="badge badge-success">
                            {{ ucfirst(str_replace('_', ' ', $pedido->envio->estado)) }}
                        </span>
                    @else
                        <span class="badge badge-warning">
                            {{ ucfirst(str_replace('_', ' ', $pedido->envio->estado)) }}
                        </span>
                    @endif
                </p>
                <a href="{{ route('envios.show', $pedido->envio->id) }}" class="btn btn-sm btn-info btn-block">
                    <i class="fas fa-eye"></i> Ver Envío
                </a>
                @if($pedido->envio->estado == 'pendiente_aprobacion_trazabilidad' && auth()->user()->hasRole('admin'))
                <form action="{{ route('envios.aprobarTrazabilidad', $pedido->envio->id) }}" method="POST" class="mt-2" onsubmit="return confirm('¿Está seguro de aprobar este envío de Trazabilidad? Una vez aprobado, podrá ser asignado a un transportista.')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success btn-block">
                        <i class="fas fa-check"></i> Aprobar Envío
                    </button>
                </form>
                @endif
                @if($pedido->envio->estado == 'en_transito' || $pedido->envio->estado == 'asignado')
                <a href="{{ route('pedidos-almacen.seguimiento', $pedido->id) }}" class="btn btn-sm btn-primary btn-block mt-2">
                    <i class="fas fa-map-marked-alt"></i> Seguimiento
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Timeline -->
        <div class="card shadow">
            <div class="card-header bg-gradient-info">
                <h5 class="card-title text-white"><i class="fas fa-history"></i> Historial</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-primary">{{ $pedido->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-shopping-cart bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ $pedido->created_at->format('H:i') }}</span>
                            <h3 class="timeline-header">Pedido Creado</h3>
                        </div>
                    </div>
                    
                    @if($pedido->fecha_envio_trazabilidad)
                    <div>
                        <i class="fas fa-paper-plane bg-info"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ $pedido->fecha_envio_trazabilidad->format('H:i') }}</span>
                            <h3 class="timeline-header">Enviado a Trazabilidad</h3>
                        </div>
                    </div>
                    @endif
                    
                    @if($pedido->fecha_aceptacion_trazabilidad)
                    <div>
                        <i class="fas fa-check bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ $pedido->fecha_aceptacion_trazabilidad->format('H:i') }}</span>
                            <h3 class="timeline-header">Aceptado por Trazabilidad</h3>
                        </div>
                    </div>
                    @endif
                    
                    @if($pedido->fecha_propuesta_enviada)
                    <div>
                        <i class="fas fa-file-pdf bg-warning"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ $pedido->fecha_propuesta_enviada->format('H:i') }}</span>
                            <h3 class="timeline-header">Propuesta Enviada</h3>
                        </div>
                    </div>
                    @endif
                    
                    @if($pedido->fecha_propuesta_aceptada)
                    <div>
                        <i class="fas fa-check-double bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ $pedido->fecha_propuesta_aceptada->format('H:i') }}</span>
                            <h3 class="timeline-header">Propuesta Aceptada</h3>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .badge-purple {
        background-color: #6f42c1 !important;
        color: white !important;
    }
</style>
<style>
    .badge-lg {
        font-size: 1em;
        padding: 0.5em 1em;
    }
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }
    .timeline-item {
        padding: 10px 0;
        border-left: 2px solid #dee2e6;
        margin-left: 20px;
        padding-left: 20px;
    }
    .timeline-item i {
        position: absolute;
        left: -10px;
        top: 10px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        text-align: center;
        line-height: 20px;
        color: white;
    }
</style>
@endsection

