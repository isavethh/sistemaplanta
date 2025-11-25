@extends('adminlte::page')
@section('title', 'Editar Unidad de Medida')
@section('content_header')
    <h1>Editar Unidad de Medida</h1>
@endsection
@section('content')
<form action="{{ route('unidadesmedida.update', $unidadesmedida) }}" method="POST">
    @csrf @method('PUT')
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ $unidadesmedida->nombre }}" required>
    </div>
    <div class="form-group">
        <label>Abreviatura</label>
        <input type="text" name="abreviatura" class="form-control" value="{{ $unidadesmedida->abreviatura }}">
    </div>
    <button type="submit" class="btn btn-success">Actualizar</button>
</form>
@endsection
