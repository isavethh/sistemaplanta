@extends('adminlte::page')

@section('title', 'Editar Tamaño de Transporte')

@section('content_header')
    <h1><i class="fas fa-ruler-combined"></i> Editar Tamaño de Transporte</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('tamanos-transporte.update', $tamanosTransporte) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="nombre">Nombre *</label>
                <input type="text" name="nombre" id="nombre" 
                       class="form-control @error('nombre') is-invalid @enderror" 
                       value="{{ old('nombre', $tamanosTransporte->nombre) }}" required>
                @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" 
                          class="form-control @error('descripcion') is-invalid @enderror" 
                          rows="3">{{ old('descripcion', $tamanosTransporte->descripcion) }}</textarea>
                @error('descripcion')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar
                </button>
                <a href="{{ route('tamanos-transporte.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection












