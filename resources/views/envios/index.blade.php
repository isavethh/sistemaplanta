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
                    <th>Fecha/Hora Creación</th>
                    <th>Almacén</th>
                    <th>Categoría</th>
                    <th>Transportista</th>
                    <th>Estado</th>
                    <th width="150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($envios as $envio)
                @php
                    $esNuevo = $envio->created_at->diffInHours(now()) < 24;
                @endphp
                <tr class="{{ $esNuevo ? 'table-success' : '' }}" style="{{ $esNuevo ? 'animation: highlight 2s ease-in-out;' : '' }}">
                    <td>
                        <strong>{{ $envio->codigo }}</strong>
                        @if($esNuevo)
                            <span class="badge badge-success badge-pill ml-1">NUEVO</span>
                        @endif
                    </td>
                    <td>
                        <i class="fas fa-calendar-alt text-primary"></i> {{ $envio->created_at->format('d/m/Y') }}<br>
                        <small><i class="fas fa-clock text-info"></i> {{ $envio->created_at->format('H:i:s') }}</small>
                    </td>
                    <td>
                        <i class="fas fa-warehouse text-success"></i> {{ $envio->almacenDestino->nombre ?? 'N/A' }}
                    </td>
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
                    <td>
                        @php
                            $transportista = optional($envio->asignacion)->transportista ?? null;
                        @endphp
                        @if($transportista)
                            <i class="fas fa-user-tie text-primary"></i> {{ $transportista->name }}
                        @else
                            <span class="text-muted"><i class="fas fa-user-slash"></i> Sin asignar</span>
                        @endif
                    </td>
                    <td>
                        @if($envio->estado == 'pendiente')
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                        @elseif($envio->estado == 'asignado')
                            <span class="badge badge-primary"><i class="fas fa-user-check"></i> Asignado</span>
                        @elseif($envio->estado == 'en_transito')
                            <span class="badge badge-info"><i class="fas fa-truck"></i> En Tránsito</span>
                        @elseif($envio->estado == 'entregado')
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Entregado</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($envio->estado) }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('envios.show', $envio) }}" class="btn btn-info" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($envio->estado == 'pendiente')
                                <a href="{{ route('envios.edit', $envio) }}" class="btn btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('envios.destroy', $envio) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este envío?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
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
            order: [[0, 'desc']], // Ordenar por código (más reciente primero)
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
    
    /* Animación para envíos nuevos */
    @keyframes highlight {
        0% {
            background-color: #d4edda;
            transform: scale(1);
        }
        50% {
            background-color: #c3e6cb;
            transform: scale(1.02);
        }
        100% {
            background-color: #d4edda;
            transform: scale(1);
        }
    }
    
    .table-success {
        background-color: #d4edda !important;
        font-weight: 500;
    }
    
    .table-success td {
        border-color: #c3e6cb !important;
    }
</style>
@endsection

@section('plugins.Datatables', true)
