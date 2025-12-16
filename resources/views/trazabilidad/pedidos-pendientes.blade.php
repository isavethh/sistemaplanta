@extends('adminlte::page')
@section('title', 'Pedidos Pendientes')
@section('content_header')
    <h1><i class="fas fa-clock"></i> Pedidos Pendientes de Aprobación</h1>
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

<div class="card shadow">
    <div class="card-header bg-gradient-warning">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Pedidos Enviados desde Almacenes</h3>
    </div>
    <div class="card-body">
        @if($pedidos->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No hay pedidos pendientes de aprobación.
            </div>
        @else
            <div class="row">
                @foreach($pedidos as $pedido)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h5 class="card-title text-white">
                                <i class="fas fa-box"></i> {{ $pedido->codigo }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Almacén:</strong> {{ $pedido->almacen->nombre ?? 'N/A' }}</p>
                            <p><strong>Propietario:</strong> {{ $pedido->propietario->name ?? 'N/A' }}</p>
                            <p><strong>Fecha Requerida:</strong> {{ $pedido->fecha_requerida->format('d/m/Y') }}</p>
                            <p><strong>Hora Requerida:</strong> {{ $pedido->hora_requerida ?? 'N/A' }}</p>
                            <p><strong>Productos:</strong> {{ $pedido->productos->count() }} productos</p>
                            <p><strong>Total Peso:</strong> {{ number_format($pedido->productos->sum('total_peso'), 2) }} kg</p>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <form action="{{ route('trazabilidad.pedidos.aceptar', $pedido->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Aceptar
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rechazarModal{{ $pedido->id }}">
                                    <i class="fas fa-times"></i> Rechazar
                                </button>
                                
                                <a href="{{ route('trazabilidad.propuestas.ver', $pedido->id) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Rechazar -->
                <div class="modal fade" id="rechazarModal{{ $pedido->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Rechazar Pedido</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('trazabilidad.pedidos.rechazar', $pedido->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Motivo del Rechazo <span class="text-danger">*</span></label>
                                        <textarea name="motivo" class="form-control" rows="3" required minlength="10" placeholder="Explique el motivo del rechazo..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Rechazar Pedido</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

