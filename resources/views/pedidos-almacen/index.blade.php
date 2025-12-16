@extends('adminlte::page')
@section('title', 'Mis Pedidos')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-shopping-cart"></i> Mis Pedidos de Almacén</h1>
        <div>
            <a href="{{ route('pedidos-almacen.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nuevo Pedido
            </a>
        </div>
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

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card shadow">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Listado de Pedidos</h3>
    </div>
    <div class="card-body">
        <table id="pedidosTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Código</th>
                    <th>Almacén</th>
                    <th>Fecha Requerida</th>
                    <th>Hora Requerida</th>
                    <th>Estado</th>
                    <th>Productos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                <tr>
                    <td><strong>{{ $pedido->codigo }}</strong></td>
                    <td>{{ $pedido->almacen->nombre ?? 'N/A' }}</td>
                    <td>{{ $pedido->fecha_requerida->format('d/m/Y') }}</td>
                    <td>{{ $pedido->hora_requerida ?? 'N/A' }}</td>
                    <td>
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
                        <span class="badge badge-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</span>
                    </td>
                    <td>{{ $pedido->productos->count() }} productos</td>
                    <td>
                        <a href="{{ route('pedidos-almacen.show', $pedido->id) }}" class="btn btn-sm btn-info" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($pedido->estado == 'pendiente')
                            <a href="{{ route('pedidos-almacen.edit', $pedido->id) }}" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('pedidos-almacen.destroy', $pedido->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este pedido?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                        @if($pedido->envio_id)
                            <a href="{{ route('pedidos-almacen.seguimiento', $pedido->id) }}" class="btn btn-sm btn-primary" title="Seguimiento">
                                <i class="fas fa-map-marked-alt"></i>
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
            order: [[0, 'desc']]
        });
    });
</script>
@endsection

