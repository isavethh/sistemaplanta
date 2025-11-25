@extends('adminlte::page')
@section('title', 'Administradores')
@section('content_header')
    <h1>Administradores</h1>
@endsection
@section('content')
<a href="{{ route('administradores.create') }}" class="btn btn-success mb-2">Nuevo Administrador</a>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<table class="table table-bordered">
    <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Acciones</th></tr></thead>
    <tbody>
        @foreach($admins as $a)
        <tr>
            <td>{{ $a->id }}</td>
            <td>{{ $a->name }}</td>
            <td>{{ $a->email }}</td>
            <td>
                <a href="{{ route('administradores.edit', $a) }}" class="btn btn-warning">Editar</a>
                <form action="{{ route('administradores.destroy', $a) }}" method="POST" style="display:inline-block;">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
@extends('adminlte::page')
@section('title', 'Administradores')
@section('content_header')
    <h1>Administradores</h1>
@endsection
@section('content')
<p>CRUD de administradores aquí.</p>
@endsection
