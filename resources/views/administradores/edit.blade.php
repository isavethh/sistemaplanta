@extends('adminlte::page')
@section('title', 'Editar Administrador')
@section('content_header')
    <h1>Editar Administrador</h1>
@endsection
@section('content')
<form action="{{ route('administradores.update', $administrador) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" class="form-control" value="{{ $administrador->name }}" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="{{ $administrador->email }}" required>
    </div>
    <div class="form-group">
        <label>Password (dejar vacío para no cambiar)</label>
        <input type="password" name="password" class="form-control">
    </div>
    <button class="btn btn-primary mt-2">Actualizar</button>
</form>
@endsection
