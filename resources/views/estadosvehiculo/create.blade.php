@extends('adminlte::page')
@section('title', 'Nuevo Estado de Vehículo')
@section('content_header')
    <h1>Nuevo Estado</h1>
@endsection
@section('content')
<form action="{{ route('estadosvehiculo.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>
    <button class="btn btn-primary mt-2">Guardar</button>
</form>
@endsection
