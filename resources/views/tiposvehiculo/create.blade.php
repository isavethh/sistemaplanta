@extends('adminlte::page')
@section('title', 'Nuevo Tipo de Vehículo')
@section('content_header')
    <h1>Nuevo Tipo de Vehículo</h1>
@endsection
@section('content')
<form action="{{ route('tiposvehiculo.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>
    <button class="btn btn-primary mt-2">Guardar</button>
</form>
@endsection
