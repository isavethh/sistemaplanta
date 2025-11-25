@extends('adminlte::page')
@section('title', 'Unidades de Medida')
@section('content_header')
    <h1>Unidades de Medida</h1>
@endsection
@section('content')
<a href="{{ route('unidadesmedida.create') }}" class="btn btn-success mb-2">Nueva Unidad</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Abreviatura</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($unidades as $unidad)
        <tr>
            <td>{{ $unidad->id }}</td>
            <td>{{ $unidad->nombre }}</td>
            <td>{{ $unidad->abreviatura }}</td>
            <td>
                <a href="{{ route('unidadesmedida.edit', $unidad) }}" class="btn btn-warning">Editar</a>
                <form action="{{ route('unidadesmedida.destroy', $unidad) }}" method="POST" style="display:inline-block;">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
