@extends('adminlte::page')
@section('title', 'Estados de Vehículo')
@section('content_header')
    <h1>Estados de Vehículo</h1>
@endsection
@section('content')
<a href="{{ route('estadosvehiculo.create') }}" class="btn btn-success mb-2">Nuevo Estado</a>
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
        @foreach($estados as $estado)
        <tr>
            <td>{{ $estado->id }}</td>
            <td>{{ $estado->nombre }}</td>
            <td>
                <a href="{{ route('estadosvehiculo.edit', $estado) }}" class="btn btn-warning">Editar</a>
                <form action="{{ route('estadosvehiculo.destroy', $estado) }}" method="POST" style="display:inline-block;">
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
@section('title', 'Estados de Vehículo')
@section('content_header')
    <h1>Estados de Vehículo</h1>
@endsection
@section('content')
<p>CRUD de estados de vehículo aquí.</p>
@endsection
