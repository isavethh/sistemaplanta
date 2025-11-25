@extends('adminlte::page')

@section('title', 'Editar Categoría')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Categoría</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-warning">
        <h3 class="card-title"><i class="fas fa-folder"></i> Modificar Información de la Categoría</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('categorias.update', $categoria) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="nombre"><i class="fas fa-tag"></i> Nombre de la Categoría *</label>
                <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                       value="{{ old('nombre', $categoria->nombre) }}" required placeholder="Ingrese el nombre de la categoría">
                @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="descripcion"><i class="fas fa-align-left"></i> Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="4" class="form-control @error('descripcion') is-invalid @enderror" 
                          placeholder="Descripción de la categoría">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                @error('descripcion')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <hr>
            <div class="form-group">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar Categoría
                </button>
                <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('css')
<style>
    .card {
        border-radius: 10px;
    }
    .form-control:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
</style>
@endsection

