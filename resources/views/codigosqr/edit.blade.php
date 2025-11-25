@extends('adminlte::page')
@section('title', 'Editar Código QR')
@section('content_header')
    <h1>Editar Código QR</h1>
@endsection
@section('content')
<form action="{{ route('codigosqr.update', $codigosqr) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Código</label>
        <input type="text" name="codigo" class="form-control" value="{{ $codigosqr->codigo }}" required>
    </div>
    <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control">{{ $codigosqr->descripcion }}</textarea>
    </div>
    <button class="btn btn-primary mt-2">Actualizar</button>
</form>
@endsection
