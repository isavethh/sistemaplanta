@extends('adminlte::page')
@section('title', 'Nuevo Código QR')
@section('content_header')
    <h1>Nuevo Código QR</h1>
@endsection
@section('content')
<form action="{{ route('codigosqr.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Código</label>
        <input type="text" name="codigo" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control"></textarea>
    </div>
    <button class="btn btn-primary mt-2">Guardar</button>
</form>
@endsection
