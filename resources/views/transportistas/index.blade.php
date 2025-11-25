@extends('adminlte::page')
@section('title', 'Transportistas')
@section('content_header')
    <h1>Transportistas</h1>
@endsection
@section('content')
<a href="{{ route('transportistas.create') }}" class="btn btn-success mb-2">Nuevo Transportista</a>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<table class="table table-bordered">
    <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Acciones</th></tr></thead>
    <tbody>
        @foreach($transportistas as $t)
        <tr>
            <td>{{ $t->id }}</td>
            <td>{{ $t->name }}</td>
            <td>{{ $t->email }}</td>
            <td>
                <a href="{{ route('transportistas.edit', $t) }}" class="btn btn-warning">Editar</a>
                <form action="{{ route('transportistas.destroy', $t) }}" method="POST" style="display:inline-block;">
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
@section('title', 'Transportistas')
@section('content_header')
    <h1>Transportistas</h1>
@endsection
@section('content')
<p>CRUD de transportistas aquí.</p>
@endsection
