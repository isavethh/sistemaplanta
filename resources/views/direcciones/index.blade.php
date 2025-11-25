@extends('adminlte::page')

@section('title', 'Rutas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-route"></i> Rutas entre Almacenes</h1>
        <a href="{{ route('direcciones.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nueva Ruta
        </a>
    </div>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="card shadow">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-map-marked-alt"></i> Rutas Definidas</h3>
    </div>
    <div class="card-body">
        <table id="rutasTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Distancia (km)</th>
                    <th>Tiempo (min)</th>
                    <th>Descripción</th>
                    <th width="150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($direcciones as $direccion)
                <tr>
                    <td>{{ $direccion->id }}</td>
                    <td>
                        <span class="badge badge-danger">
                            <i class="fas fa-industry"></i> {{ $direccion->almacenOrigen->nombre ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-success">
                            <i class="fas fa-warehouse"></i> {{ $direccion->almacenDestino->nombre ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ number_format($direccion->distancia_km, 2) }} km</strong>
                    </td>
                    <td>
                        <i class="fas fa-clock"></i> {{ $direccion->tiempo_estimado_minutos }} min
                    </td>
                    <td>{{ $direccion->ruta_descripcion ? Str::limit($direccion->ruta_descripcion, 50) : 'N/A' }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('direcciones.edit', $direccion) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('direcciones.destroy', $direccion) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Eliminar esta ruta?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">
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
    $('#rutasTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        order: [[0, 'desc']]
    });
});
</script>
@endsection

@section('plugins.Datatables', true)
