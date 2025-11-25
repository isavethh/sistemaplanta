@extends('adminlte::page')
@section('title', 'Nuevo Usuario')
@section('content_header')
    <h1>Nuevo Usuario</h1>
@endsection
@section('content')
<form action="{{ route('users.store') }}" method="POST">
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
        <label>Contraseña</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Rol</label>
        <select name="role" class="form-control">
            <option value="admin">Admin</option>
            <option value="transportista">Transportista</option>
            <option value="user">User</option>
        </select>
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
</form>
@endsection
