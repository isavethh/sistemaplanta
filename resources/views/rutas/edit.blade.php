@extends('adminlte::page')
@section('title', 'Editar Ruta')
@section('content_header')
    <h1>Editar Ruta</h1>
@endsection
@section('content')
<form action="{{ route('rutas.update', $ruta) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ $ruta->nombre }}" required>
    </div>
    <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control">{{ $ruta->descripcion }}</textarea>
    </div>
    <div class="form-group">
        <label>Polyline</label>
        <textarea name="polyline" class="form-control">{{ $ruta->polyline }}</textarea>
    </div>
    <button class="btn btn-primary mt-2">Actualizar</button>
</form>
@endsection
