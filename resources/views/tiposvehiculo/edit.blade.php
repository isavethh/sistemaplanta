@extends('adminlte::page')
@section('title', 'Editar Tipo de Vehículo')
@section('content_header')
    <h1>Editar Tipo de Vehículo</h1>
@endsection
@section('content')
<form action="{{ route('tiposvehiculo.update', $tiposvehiculo) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ $tiposvehiculo->nombre }}" required>
    </div>
    <button class="btn btn-primary mt-2">Actualizar</button>
</form>
@endsection
