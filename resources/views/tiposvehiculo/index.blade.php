@extends('adminlte::page')
@section('title', 'Tipos de Vehículo')
@section('content_header')
    <h1>Tipos de Vehículo</h1>
@endsection
@section('content')
<a href="{{ route('tiposvehiculo.create') }}" class="btn btn-success mb-2">Nuevo Tipo de Vehículo</a>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tipos as $tipo)
        <tr>
            <td>{{ $tipo->id }}</td>
            <td>{{ $tipo->nombre }}</td>
            <td>
                <a href="{{ route('tiposvehiculo.edit', $tipo) }}" class="btn btn-warning">Editar</a>
                <form action="{{ route('tiposvehiculo.destroy', $tipo) }}" method="POST" style="display:inline-block;">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
@extends('adminlte::page')
@section('title', 'Tipos de Vehículo')
@section('content_header')
    <h1>Tipos de Vehículo</h1>
@endsection
@section('content')
<p>CRUD de tipos de vehículo aquí.</p>
@endsection
