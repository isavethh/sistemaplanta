@extends('adminlte::page')
@section('title', 'Nuevo Vehículo')
@section('content_header')
    <h1>Nuevo Vehículo</h1>
@endsection
@section('content')
<form action="{{ route('vehiculos.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Placa</label>
        <input type="text" name="placa" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Tipo</label>
        <select name="tipo" class="form-control">
            <option value="">--</option>
            @foreach($tipos as $t)
                <option value="{{ $t->id }}">{{ $t->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Capacidad</label>
        <input type="number" step="0.01" name="capacidad" class="form-control">
    </div>
    <div class="form-group">
        <label>Transportista</label>
        <select name="user_id" class="form-control">
            <option value="">--</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-primary mt-2">Guardar</button>
</form>
@endsection
