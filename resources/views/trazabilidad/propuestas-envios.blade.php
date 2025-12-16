@extends('adminlte::page')
@section('title', 'PlanTrack - Propuestas de Envíos')
@section('content_header')
    <h1><i class="fas fa-clipboard-check"></i> PlanTrack - Propuestas de Envíos</h1>
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
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Propuestas Recibidas</h3>
    </div>
    <div class="card-body">
        <table id="propuestasTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Código Pedido</th>
                    <th>Almacén</th>
                    <th>Fecha Propuesta</th>
                    <th>Estado</th>
                    <th>Envió Asociado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                <tr>
                    <td><strong>{{ $pedido->codigo }}</strong></td>
                    <td>{{ $pedido->almacen->nombre ?? 'N/A' }}</td>
                    <td>{{ $pedido->fecha_propuesta_enviada ? $pedido->fecha_propuesta_enviada->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>
                        @if($pedido->estado == 'propuesta_enviada')
                            <span class="badge badge-warning">Pendiente Aprobación</span>
                        @elseif($pedido->estado == 'propuesta_aceptada')
                            <span class="badge badge-success">Aprobada</span>
                        @endif
                    </td>
                    <td>
                        @if($pedido->envio)
                            <a href="{{ route('envios.show', $pedido->envio->id) }}" class="btn btn-sm btn-info">
                                {{ $pedido->envio->codigo }}
                            </a>
                        @else
                            <span class="text-muted">Sin envío</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('trazabilidad.propuestas.ver', $pedido->id) }}" class="btn btn-primary" title="Ver Propuesta">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="{{ route('trazabilidad.propuestas.descargar-pdf', $pedido->id) }}" class="btn btn-danger" target="_blank" title="Descargar PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            @if($pedido->estado == 'propuesta_enviada')
                                <form action="{{ route('trazabilidad.propuestas.aprobar', $pedido->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success" title="Aprobar Propuesta">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#rechazarPropuestaModal{{ $pedido->id }}" title="Rechazar Propuesta">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- Modal Rechazar Propuesta -->
                <div class="modal fade" id="rechazarPropuestaModal{{ $pedido->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Rechazar Propuesta</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('trazabilidad.propuestas.rechazar', $pedido->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Motivo del Rechazo <span class="text-danger">*</span></label>
                                        <textarea name="motivo" class="form-control" rows="3" required minlength="10" placeholder="Explique el motivo del rechazo..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Rechazar Propuesta</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#propuestasTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[2, 'desc']]
        });
    });
</script>
@endsection

