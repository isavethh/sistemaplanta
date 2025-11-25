@extends('adminlte::page')
@section('title', 'Editar Cliente')
@section('content_header')
    <h1>Editar Cliente</h1>
@endsection
@section('content')
<form action="{{ route('clientes.update', $cliente) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" class="form-control" value="{{ $cliente->name }}" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="{{ $cliente->email }}" required>
    </div>
    <div class="form-group">
        <label>Password (dejar vacío para no cambiar)</label>
        <input type="password" name="password" class="form-control">
    </div>
    <button class="btn btn-primary mt-2">Actualizar</button>
</form>
@endsection
