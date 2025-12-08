@extends('adminlte::page')
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
                <!-- Estado -->
                <div class="mb-4 text-center">
                    @if($incidente->estado == 'pendiente')
                        <span class="badge badge-danger p-3" style="font-size: 1.2em;">
                            <i class="fas fa-clock fa-lg"></i> PENDIENTE
                        </span>
                    @elseif($incidente->estado == 'en_proceso')
                        <span class="badge badge-warning p-3" style="font-size: 1.2em;">
                            <i class="fas fa-spinner fa-spin fa-lg"></i> EN PROCESO
                        </span>
                    @elseif($incidente->estado == 'resuelto')
                        <span class="badge badge-success p-3" style="font-size: 1.2em;">
                            <i class="fas fa-check-circle fa-lg"></i> RESUELTO
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
                                'otro' => 'Otro Problema',
                            ];
                        @endphp
                        <span class="badge badge-danger p-2">
                            {{ $tiposTexto[$incidente->tipo_incidente] ?? $incidente->tipo_incidente }}
                        </span>
                    </div>
                </div>

                <!-- Descripción del Almacén -->
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-comment"></i> Descripción del Almacén:</strong></div>
                    <div class="col-md-8">
                        <div class="alert alert-warning border" style="background: #fff3cd; border-left: 4px solid #ffc107 !important;">
                            <i class="fas fa-quote-left text-muted"></i>
                            <strong style="font-size: 1.1em;">
                                {{ $incidente->descripcion ?? 'Sin descripción proporcionada' }}
                            </strong>
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
                            <a href="{{ route('envios.show', $incidente->envio_id) }}">{{ $incidente->envio_codigo }}</a>
                        </p>
                        <p><strong>Almacén:</strong> {{ $incidente->almacen_nombre }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Dirección:</strong> {{ $incidente->almacen_direccion ?? 'N/A' }}</p>
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
        <!-- Foto de Evidencia -->
        @if($incidente->foto_url)
        <div class="card shadow mb-3">
            <div class="card-header bg-info">
                <h5 class="card-title text-white mb-0"><i class="fas fa-camera"></i> Foto de Evidencia</h5>
            </div>
            <div class="card-body text-center">
                <a href="http://10.26.14.34:3001{{ $incidente->foto_url }}" target="_blank">
                    <img src="http://10.26.14.34:3001{{ $incidente->foto_url }}" 
                         class="img-fluid img-thumbnail" 
                         style="max-height: 300px;"
                         alt="Evidencia del incidente">
                </a>
                <p class="text-muted mt-2"><small>Click para ver en tamaño completo</small></p>
            </div>
        </div>
        @endif

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
</style>
@endsection
