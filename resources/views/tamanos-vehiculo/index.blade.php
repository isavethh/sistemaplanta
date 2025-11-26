@extends('adminlte::page')

@section('title', 'Tamaños de Vehículo')

@section('content_header')
    <h1><i class="fas fa-ruler"></i> Tamaños de Vehículo</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Listado de Tamaños</h3>
        <div class="card-tools">
            <a href="{{ route('tamanos-vehiculo.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo Tamaño
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Capacidad Mín. (ton)</th>
                    <th>Capacidad Máx. (ton)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tamanos as $tamano)
                    <tr>
                        <td>{{ $tamano->id }}</td>
                        <td><strong>{{ $tamano->nombre }}</strong></td>
                        <td>{{ $tamano->descripcion ?? '-' }}</td>
                        <td>{{ $tamano->capacidad_min ? number_format($tamano->capacidad_min, 2) : '-' }}</td>
                        <td>{{ $tamano->capacidad_max ? number_format($tamano->capacidad_max, 2) : '-' }}</td>
                        <td>
                            <a href="{{ route('tamanos-vehiculo.edit', $tamano->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('tamanos-vehiculo.destroy', $tamano->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este tamaño?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay tamaños registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

