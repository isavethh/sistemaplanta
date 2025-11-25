@extends('adminlte::page')
@section('title', 'Editar Estado de Vehículo')
@section('content_header')
    <h1>Editar Estado</h1>
@endsection
@section('content')
<form action="{{ route('estadosvehiculo.update', $estadosvehiculo) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ $estadosvehiculo->nombre }}" required>
    </div>
    <button class="btn btn-primary mt-2">Actualizar</button>
</form>
@endsection
