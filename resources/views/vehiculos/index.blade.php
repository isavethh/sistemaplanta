@extends('adminlte::page')

@section('title', 'Vehículos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-truck"></i> Vehículos</h1>
        <a href="{{ route('vehiculos.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Vehículo
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
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Listado de Vehículos</h3>
    </div>
    <div class="card-body">
        <table id="vehiculosTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Placa</th>
                    <th>Marca/Modelo</th>
                    <th>Tamaño</th>
                    <th>Licencia</th>
                    <th>Capacidad</th>
                    <th>Transportista</th>
                    <th>Estado</th>
                    <th width="120px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehiculos as $vehiculo)
                <tr>
                    <td><strong>{{ $vehiculo->placa }}</strong></td>
                    <td>
                        @if($vehiculo->marca || $vehiculo->modelo)
                            {{ $vehiculo->marca ?? '' }} {{ $vehiculo->modelo ?? '' }}
                            @if($vehiculo->anio)
                                <small class="text-muted">({{ $vehiculo->anio }})</small>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($vehiculo->tamanoVehiculo)
                            <span class="badge badge-info">
                                {{ $vehiculo->tamanoVehiculo->nombre }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $vehiculo->licencia_requerida }}</span>
                    </td>
                    <td>
                        @if($vehiculo->capacidad_carga)
                            {{ number_format($vehiculo->capacidad_carga, 2) }}
                            @if($vehiculo->unidadMedidaCarga)
                                {{ $vehiculo->unidadMedidaCarga->abreviatura }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($vehiculo->transportista)
                            <span class="badge badge-success">
                                {{ $vehiculo->transportista->name }}
                            </span>
                        @else
                            <span class="badge badge-secondary">Sin asignar</span>
                        @endif
                    </td>
                    <td>
                        @if($vehiculo->disponible && $vehiculo->estado == 'activo')
                            <span class="badge badge-success">Disponible</span>
                        @elseif($vehiculo->estado == 'mantenimiento')
                            <span class="badge badge-warning">Mantenimiento</span>
                        @elseif($vehiculo->estado == 'inactivo')
                            <span class="badge badge-danger">Inactivo</span>
                        @else
                            <span class="badge badge-secondary">Ocupado</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('vehiculos.edit', $vehiculo) }}" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('vehiculos.destroy', $vehiculo) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este vehículo?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
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
        $('#vehiculosTable').DataTable({
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
    }
</style>
@endsection

@section('plugins.Datatables', true)
