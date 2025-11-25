@extends('adminlte::page')
@section('title', 'Tipos de Empaque')
@section('content_header')
    <h1>Tipos de Empaque</h1>
@endsection
@section('content')
<a href="{{ route('tiposempaque.create') }}" class="btn btn-success mb-2">Nuevo Tipo de Empaque</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($empaques as $empaque)
        <tr>
            <td>{{ $empaque->id }}</td>
            <td>{{ $empaque->nombre }}</td>
            <td>
                <a href="{{ route('tiposempaque.edit', $empaque) }}" class="btn btn-warning">Editar</a>
                <form action="{{ route('tiposempaque.destroy', $empaque) }}" method="POST" style="display:inline-block;">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
