@extends('adminlte::page')
@section('title', 'Nuevo Tipo de Empaque')
@section('content_header')
    <h1>Nuevo Tipo de Empaque</h1>
@endsection
@section('content')
<form action="{{ route('tiposempaque.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
</form>
@endsection
