@extends('adminlte::page')
@section('title', 'Envíos')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-shipping-fast"></i> Gestión de Envíos</h1>
        <div>
            <a href="{{ route('envios.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nuevo Envío
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

<div class="card shadow">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Listado de Envíos</h3>
    </div>
    <div class="card-body">
        <table id="enviosTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Código</th>
                    <th>Almacén</th>
                    <th>Dirección</th>
                    <th>Categoría</th>
                    <th>Transportista</th>
                    <th>Estado</th>
                    <th width="200px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($envios as $envio)
                <tr>
                    <td><strong>{{ $envio->codigo }}</strong></td>
                    <td>📦 {{ $envio->almacenDestino->nombre ?? 'N/A' }}</td>
                    <td>{{ Str::limit($envio->almacenDestino->direccion_completa ?? 'N/A', 30) }}</td>
                    <td>
                        @php
                            $primerProducto = $envio->productos->first();
                            $categorias = $envio->productos->pluck('categoria')->unique();
                        @endphp
                        @if($categorias->count() > 1)
                            <span class="badge badge-info">Mixto</span>
                        @else
                            <span class="badge badge-info">{{ $primerProducto->categoria ?? 'N/A' }}</span>
                        @endif
                    </td>
                    <td>{{ optional($envio->asignacion)->transportista->name ?? 'Sin asignar' }}</td>
                    <td>
                        @if($envio->estado == 'pendiente')
                            <span class="badge badge-warning">Pendiente</span>
                        @elseif($envio->estado == 'aprobado')
                            <span class="badge badge-primary"><i class="fas fa-check"></i> Aprobado</span>
                        @elseif($envio->estado == 'en_transito')
                            <span class="badge badge-info">En Tránsito</span>
                        @elseif($envio->estado == 'entregado')
                            <span class="badge badge-success">Entregado</span>
                        @else
                            <span class="badge badge-secondary">{{ $envio->estado }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('envios.show', $envio) }}" class="btn btn-info" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($envio->estado == 'pendiente')
                                <form action="{{ route('envios.aprobar', $envio) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Aprobar este envío? Se generará automáticamente una nota de venta.')">
                                    @csrf
                                    <button type="submit" class="btn btn-success" title="Aprobar y Generar Nota de Venta">
                                        <i class="fas fa-check-circle"></i> Aprobar
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('envios.edit', $envio) }}" class="btn btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('envios.destroy', $envio) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este envío?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
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
        $('#enviosTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    });
</script>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
    .card {
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 15px !important;
    }
    .content-wrapper {
        padding-bottom: 15px !important;
    }
    .alert {
        margin-bottom: 15px !important;
    }
</style>
@endsection

@section('plugins.Datatables', true)
