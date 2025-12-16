@extends('adminlte::page')
@section('title', 'Pedidos Aceptados')
@section('content_header')
    <h1><i class="fas fa-check-circle"></i> Pedidos Aceptados</h1>
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
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Historial de Pedidos Aceptados</h3>
    </div>
    <div class="card-body">
        <table id="pedidosTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Código</th>
                    <th>Almacén</th>
                    <th>Propietario</th>
                    <th>Fecha Requerida</th>
                    <th>Fecha Aceptación</th>
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
                    <td>{{ $pedido->propietario->name ?? 'N/A' }}</td>
                    <td>{{ $pedido->fecha_requerida->format('d/m/Y') }}</td>
                    <td>{{ $pedido->fecha_propuesta_aceptada ? $pedido->fecha_propuesta_aceptada->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>
                        <span class="badge badge-{{ $pedido->estado == 'entregado' ? 'success' : 'info' }}">
                            {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                        </span>
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
                        <a href="{{ route('trazabilidad.propuestas.ver', $pedido->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        @if($pedido->envio)
                            <a href="{{ route('envios.show', $pedido->envio->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-shipping-fast"></i> Ver Envío
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#pedidosTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[4, 'desc']]
        });
    });
</script>
@endsection

