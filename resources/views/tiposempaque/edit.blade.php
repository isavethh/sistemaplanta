@extends('adminlte::page')
@section('title', 'Editar Tipo de Empaque')
@section('content_header')
    <h1>Editar Tipo de Empaque</h1>
@endsection
@section('content')
<form action="{{ route('tiposempaque.update', $tiposempaque) }}" method="POST">
    @csrf @method('PUT')
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ $tiposempaque->nombre }}" required>
    </div>
    <button type="submit" class="btn btn-success">Actualizar</button>
</form>
@endsection
