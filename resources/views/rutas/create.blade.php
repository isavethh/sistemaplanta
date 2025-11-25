@extends('adminlte::page')
@section('title', 'Nueva Ruta')
@section('content_header')
    <h1>Nueva Ruta</h1>
@endsection
@section('content')
<form action="{{ route('rutas.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control"></textarea>
    </div>
    <div class="form-group">
        <label>Polyline (opcional)</label>
        <textarea name="polyline" class="form-control"></textarea>
    </div>
    <button class="btn btn-primary mt-2">Guardar</button>
</form>
@endsection
