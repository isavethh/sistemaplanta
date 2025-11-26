@extends('adminlte::page')
@section('title', 'Editar Dirección')
@section('content_header')
    <h1>Editar Dirección</h1>
@endsection
@section('content')
<form action="{{ route('direcciones.update', $direccion) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Calle</label>
        <input type="text" name="calle" class="form-control" value="{{ $direccion->calle }}" required>
    </div>
    <div class="form-group">
        <label>Ciudad</label>
        <input type="text" name="ciudad" class="form-control" value="{{ $direccion->ciudad }}">
    </div>
    <div class="form-group">
        <label>Departamento</label>
        <input type="text" name="departamento" class="form-control" value="{{ $direccion->departamento }}">
    </div>
    <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control">{{ $direccion->descripcion }}</textarea>
    </div>
    <button class="btn btn-primary mt-2">Actualizar</button>
</form>
@endsection
