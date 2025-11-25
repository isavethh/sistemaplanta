@extends('adminlte::page')
@section('title', 'Nuevo Transportista')
@section('content_header')
    <h1>Nuevo Transportista</h1>
@endsection
@section('content')
<form action="{{ route('transportistas.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-primary mt-2">Guardar</button>
</form>
@endsection
