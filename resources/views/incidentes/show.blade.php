@extends('adminlte::page')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
@section('title', 'Detalle Incidente #' . $incidente->id)
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-exclamation-triangle text-danger"></i> Incidente #{{ $incidente->id }}</h1>
        <a href="{{ route('incidentes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <!-- Información Principal -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-gradient-danger">
                <h3 class="card-title text-white"><i class="fas fa-info-circle"></i> Detalles del Incidente</h3>
            </div>
            <div class="card-body">
                <!-- Alerta de Solicitud de Ayuda -->
                @if($incidente->solicitar_ayuda ?? false)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h5><i class="fas fa-exclamation-triangle"></i> <strong>Solicitud de Ayuda Urgente</strong></h5>
                    <p class="mb-0">
                        El transportista ha solicitado ayuda urgente del administrador para resolver este incidente.
                    </p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                
                <!-- Estado -->
                <div class="mb-4 text-center">
                    @if($incidente->estado == 'pendiente')
                        <span class="badge badge-danger p-3" style="font-size: 1.2em;">
                            <i class="fas fa-clock fa-lg"></i> PENDIENTE
                        </span>
                    @elseif($incidente->estado == 'en_revision')
                        <span class="badge badge-info p-3" style="font-size: 1.2em;">
                            <i class="fas fa-search fa-lg"></i> EN REVISIÓN
                        </span>
                    @elseif($incidente->estado == 'en_proceso')
                        <span class="badge badge-warning p-3" style="font-size: 1.2em;">
                            <i class="fas fa-spinner fa-spin fa-lg"></i> EN PROCESO
                        </span>
                    @elseif($incidente->estado == 'resuelto')
                        <span class="badge badge-success p-3" style="font-size: 1.2em;">
                            <i class="fas fa-check-circle fa-lg"></i> RESUELTO
                        </span>
                    @else
                        <span class="badge badge-secondary p-3" style="font-size: 1.2em;">
                            {{ strtoupper($incidente->estado) }}
                        </span>
                    @endif
                </div>

                <!-- Tipo de Incidente -->
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-tag"></i> Tipo:</strong></div>
                    <div class="col-md-8">
                        @php
                            $tiposTexto = [
                                'producto_danado' => 'Producto Dañado',
                                'cantidad_incorrecta' => 'Cantidad Incorrecta',
                                'producto_faltante' => 'Producto Faltante',
                                'producto_equivocado' => 'Producto Equivocado',
                                'empaque_malo' => 'Empaque en Mal Estado',
                                'accidente_vehiculo' => 'Accidente de Vehículo',
                                'averia_vehiculo' => 'Avería de Vehículo',
                                'robo' => 'Robo',
                                'perdida_mercancia' => 'Pérdida de Mercancía',
                                'daño_mercancia' => 'Daño de Mercancía',
                                'retraso' => 'Retraso en Entrega',
                                'problema_ruta' => 'Problema en Ruta',
                                'problema_cliente' => 'Problema con Cliente',
                                'otro' => 'Otro Problema',
                            ];
                        @endphp
                        <span class="badge badge-danger p-2">
                            {{ $tiposTexto[$incidente->tipo_incidente] ?? ucfirst(str_replace('_', ' ', $incidente->tipo_incidente)) }}
                        </span>
                    </div>
                </div>

                <!-- Descripción del Incidente -->
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-comment"></i> Descripción:</strong></div>
                    <div class="col-md-8">
                        <div class="alert alert-warning border" style="background: #fff3cd; border-left: 4px solid #ffc107 !important; min-height: 50px; display: block;">
                            <i class="fas fa-quote-left text-muted"></i>
                            <span style="font-size: 1.1em; white-space: pre-wrap; word-wrap: break-word; display: inline-block; width: 100%;">
                                {{ !empty($incidente->descripcion) ? $incidente->descripcion : 'Sin descripción proporcionada' }}
                            </span>
                            <i class="fas fa-quote-right text-muted"></i>
                        </div>
                    </div>
                </div>

                <!-- Fecha de Reporte -->
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-calendar"></i> Fecha de Reporte:</strong></div>
                    <div class="col-md-8">
                        {{ \Carbon\Carbon::parse($incidente->fecha_reporte)->format('d/m/Y H:i:s') }}
                    </div>
                </div>

                @if($incidente->fecha_resolucion)
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-check-double"></i> Fecha de Resolución:</strong></div>
                    <div class="col-md-8">
                        {{ \Carbon\Carbon::parse($incidente->fecha_resolucion)->format('d/m/Y H:i:s') }}
                    </div>
                </div>
                @endif

                <!-- Notas de Resolución -->
                @if($incidente->notas_resolucion)
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-sticky-note"></i> Notas:</strong></div>
                    <div class="col-md-8">
                        <div class="alert alert-success">
                            {!! nl2br(e($incidente->notas_resolucion)) !!}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Información del Envío -->
                <hr>
                <h5><i class="fas fa-box"></i> Información del Envío</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Código:</strong> 
                            <a href="{{ route('envios.show', $incidente->envio_id) }}">{{ $incidente->envio_codigo ?? 'N/A' }}</a>
                        </p>
                        <p><strong>Almacén:</strong> {{ $incidente->almacen_nombre ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Dirección:</strong> {{ $incidente->almacen_direccion ?? 'N/A' }}</p>
                        @if(isset($transportista))
                        <p><strong>Transportista:</strong> 
                            {{ $transportista->name ?? 'N/A' }}
                            @if(isset($transportista->telefono))
                                <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $transportista->telefono }}</small>
                            @endif
                        </p>
                        @endif
                    </div>
                </div>

                <!-- Productos del Envío -->
                @if($productos && $productos->count() > 0)
                <h6 class="mt-3"><i class="fas fa-shopping-basket"></i> Productos del Envío</h6>
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $producto)
                        <tr>
                            <td>{{ $producto->producto_nombre ?? 'N/A' }}</td>
                            <td>{{ $producto->cantidad }}</td>
                            <td>Bs. {{ number_format($producto->total_precio ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    <!-- Panel Lateral -->
    <div class="col-md-4">

        <!-- Acciones -->
        <div class="card shadow mb-3">
            <div class="card-header bg-primary">
                <h5 class="card-title text-white mb-0"><i class="fas fa-cogs"></i> Acciones</h5>
            </div>
            <div class="card-body">
                @if($incidente->estado != 'resuelto')
                    @if($incidente->estado == 'pendiente')
                    <form action="{{ route('incidentes.cambiarEstado', $incidente->id) }}" method="POST" class="mb-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="estado" value="en_revision">
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-search"></i> Marcar En Revisión
                        </button>
                    </form>
                    @endif

                    @if($incidente->estado == 'en_revision' || $incidente->estado == 'pendiente')
                    <form action="{{ route('incidentes.cambiarEstado', $incidente->id) }}" method="POST" class="mb-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="estado" value="en_proceso">
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fas fa-spinner"></i> Marcar En Proceso
                        </button>
                    </form>
                    @endif

                    <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#modalResolver">
                        <i class="fas fa-check-circle"></i> Marcar como Resuelto
                    </button>
                @else
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">Este incidente ya fue resuelto</p>
                    </div>
                @endif

                <hr>

                <a href="{{ route('envios.show', $incidente->envio_id) }}" class="btn btn-info btn-block">
                    <i class="fas fa-box"></i> Ver Envío Completo
                </a>
            </div>
        </div>

        <!-- Agregar Nota -->
        @if($incidente->estado != 'resuelto')
        <div class="card shadow">
            <div class="card-header bg-secondary">
                <h5 class="card-title text-white mb-0"><i class="fas fa-sticky-note"></i> Agregar Nota</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('incidentes.agregarNota', $incidente->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <textarea name="nota" class="form-control" rows="3" placeholder="Escribe una nota..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-block">
                        <i class="fas fa-plus"></i> Agregar Nota
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal Resolver -->
<div class="modal fade" id="modalResolver" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('incidentes.cambiarEstado', $incidente->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle"></i> Resolver Incidente</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="estado" value="resuelto">
                    <div class="form-group">
                        <label><strong>Notas de Resolución</strong></label>
                        <textarea name="notas" class="form-control" rows="4" placeholder="Describe cómo se resolvió el incidente, qué acciones se tomaron, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Marcar como Resuelto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .img-thumbnail {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .img-thumbnail:hover {
        transform: scale(1.02);
    }
    
    /* SOLUCIÓN COMPLETA: Prevenir cualquier movimiento del modal */
    body.modal-open .modal[id^="modalResolver"] {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 1055 !important;
        overflow: hidden !important;
    }
    
    body.modal-open .modal[id^="modalResolver"] .modal-dialog {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        margin: 0 !important;
        transform: translate(-50%, -50%) !important;
        max-width: 500px !important;
        width: 90% !important;
        pointer-events: auto !important;
    }
    
    body.modal-open .modal[id^="modalResolver"].show .modal-dialog {
        transform: translate(-50%, -50%) !important;
    }
    
    body.modal-open .modal[id^="modalResolver"].fade .modal-dialog {
        transition: opacity .15s linear !important;
        transform: translate(-50%, -50%) !important;
    }
    
    /* Prevenir TODAS las transformaciones en hover - aplica a TODO dentro del modal */
    body.modal-open .modal[id^="modalResolver"] *,
    body.modal-open .modal[id^="modalResolver"] *:hover,
    body.modal-open .modal[id^="modalResolver"] *:focus,
    body.modal-open .modal[id^="modalResolver"] *:active {
        transform: inherit !important;
        transition: none !important;
        will-change: auto !important;
    }
    
    /* Override específico para elementos comunes que pueden tener transform */
    body.modal-open .modal[id^="modalResolver"] .btn,
    body.modal-open .modal[id^="modalResolver"] .btn:hover,
    body.modal-open .modal[id^="modalResolver"] .btn:focus,
    body.modal-open .modal[id^="modalResolver"] .card,
    body.modal-open .modal[id^="modalResolver"] .card:hover {
        transform: none !important;
        translate: none !important;
        scale: none !important;
        rotate: none !important;
    }
    
    /* Asegurar que el modal-dialog específicamente NO se mueva */
    body.modal-open .modal[id^="modalResolver"] .modal-dialog,
    body.modal-open .modal[id^="modalResolver"] .modal-dialog:hover,
    body.modal-open .modal[id^="modalResolver"] .modal-dialog:focus {
        transform: translate(-50%, -50%) !important;
    }
</style>
@endsection
