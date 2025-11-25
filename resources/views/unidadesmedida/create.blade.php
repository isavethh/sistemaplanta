@extends('adminlte::page')
@section('title', 'Nueva Unidad de Medida')
@section('content_header')
    <h1>Nueva Unidad de Medida</h1>
@endsection
@section('content')
<form action="{{ route('unidadesmedida.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Abreviatura</label>
        <input type="text" name="abreviatura" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
</form>
@endsection
