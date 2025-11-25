@extends('adminlte::page')
@section('title', 'Editar Vehículo')
@section('content_header')
    <h1>Editar Vehículo</h1>
@endsection
@section('content')
<form action="{{ route('vehiculos.update', $vehiculo) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Placa</label>
        <input type="text" name="placa" class="form-control" value="{{ $vehiculo->placa }}" required>
    </div>
    <div class="form-group">
        <label>Tipo</label>
        <select name="tipo" class="form-control">
            <option value="">--</option>
            @foreach($tipos as $t)
                <option value="{{ $t->id }}" {{ $vehiculo->tipo == $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Capacidad</label>
        <input type="number" step="0.01" name="capacidad" class="form-control" value="{{ $vehiculo->capacidad }}">
    </div>
    <div class="form-group">
        <label>Transportista</label>
        <select name="user_id" class="form-control">
            <option value="">--</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ $vehiculo->transportista_id == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-primary mt-2">Actualizar</button>
</form>
@endsection
