@extends('adminlte::page')
@section('title', 'Editar Usuario')
@section('content_header')
    <h1>Editar Usuario</h1>
@endsection
@section('content')
<form action="{{ route('users.update', $user) }}" method="POST">
    @csrf @method('PUT')
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
    </div>
    <div class="form-group">
        <label>Rol</label>
        <select name="role" class="form-control">
            <option value="admin" @if($user->role=='admin') selected @endif>Admin</option>
            <option value="transportista" @if($user->role=='transportista') selected @endif>Transportista</option>
            <option value="user" @if($user->role=='user') selected @endif>User</option>
        </select>
    </div>
    <button type="submit" class="btn btn-success">Actualizar</button>
</form>
@endsection
