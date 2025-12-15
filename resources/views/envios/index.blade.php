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
                    <th style="display:none;">ID</th>
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
                    $esNuevo = $envio->created_at && $envio->created_at->diffInHours(now()) < 24;
                @endphp
                <tr class="{{ $esNuevo ? 'table-success' : '' }}" style="{{ $esNuevo ? 'animation: highlight 2s ease-in-out;' : '' }}">
                    <td style="display:none;" data-order="{{ $envio->id }}">{{ $envio->id }}</td>
                    <td>
                        <strong>{{ $envio->codigo }}</strong>
                        @if($esNuevo)
                            <span class="badge badge-success badge-pill ml-1">NUEVO</span>
                        @endif
                    </td>
                    <td data-order="{{ $envio->created_at ? $envio->created_at->timestamp : 0 }}">
                        @if($envio->created_at)
                            <i class="fas fa-calendar-alt text-primary"></i> {{ $envio->created_at->format('d/m/Y') }}<br>
                            <small><i class="fas fa-clock text-info"></i> {{ $envio->created_at->format('H:i:s') }}</small>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
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
                        @php
                            $tieneIncidente = \DB::table('incidentes')
                                ->where('envio_id', $envio->id)
                                ->where('accion', 'cancelar')
                                ->exists();
                        @endphp
                        @if($envio->estado == 'pendiente')
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                        @elseif($envio->estado == 'asignado')
                            <span class="badge badge-primary"><i class="fas fa-user-check"></i> Asignado</span>
                        @elseif($envio->estado == 'en_transito')
                            <span class="badge badge-info"><i class="fas fa-truck"></i> En Tránsito</span>
                        @elseif($envio->estado == 'entregado')
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Entregado</span>
                        @elseif($envio->estado == 'cancelado')
                            <span class="badge badge-danger">
                                <i class="fas fa-times-circle"></i> Cancelado
                                @if($tieneIncidente)
                                    <i class="fas fa-exclamation-triangle ml-1" title="Cancelado por incidente"></i>
                                @endif
                            </span>
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
        // Inicializar DataTable con configuración que evite ocultar filas
        var table = $('#enviosTable').DataTable({
            responsive: true,
            order: [[0, 'desc']], // Ordenar por ID (columna 0 oculta, más reciente primero)
            pageLength: 100, // Mostrar más registros por página por defecto
            lengthMenu: [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "Todos"]],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            // Deshabilitar filtros automáticos que puedan ocultar filas
            search: {
                smart: false
            },
            // Asegurar que todas las filas se muestren
            processing: false,
            serverSide: false, // Usar procesamiento del lado del cliente
            deferRender: false,
            // No ocultar filas con valores null o vacíos
            columnDefs: [
                {
                    targets: 0, // Columna ID (oculta)
                    visible: false,
                    searchable: false
                },
                {
                    targets: '_all',
                    defaultContent: 'N/A' // Valor por defecto para celdas vacías
                }
            ],
            // Asegurar que todas las filas se rendericen
            drawCallback: function(settings) {
                // Verificar que todas las filas estén visibles
                var api = this.api();
                var rows = api.rows({page: 'current'}).nodes();
                $(rows).css('display', 'table-row');
            }
        });
        
        // Forzar renderizado completo después de un pequeño delay
        setTimeout(function() {
            table.draw(false); // Redibujar sin resetear la paginación
        }, 100);
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
