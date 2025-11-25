@extends('adminlte::page')

@section('title', 'Crear Producto')

@section('content_header')
    <h1><i class="fas fa-plus-circle"></i> Crear Nuevo Producto</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-box-open"></i> Información del Producto</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('productos.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombre"><i class="fas fa-tag"></i> Nombre del Producto *</label>
                        <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                               value="{{ old('nombre') }}" required placeholder="Ingrese el nombre del producto">
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="categoria_id"><i class="fas fa-folder"></i> Categoría *</label>
                        <select name="categoria_id" id="categoria_id" class="form-control @error('categoria_id') is-invalid @enderror" required>
                            <option value="">Seleccione una categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="precio_base"><i class="fas fa-dollar-sign"></i> Precio Base</label>
                        <input type="number" name="precio_base" id="precio_base" class="form-control @error('precio_base') is-invalid @enderror" 
                               value="{{ old('precio_base') }}" step="0.01" min="0" placeholder="0.00">
                        @error('precio_base')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="descripcion"><i class="fas fa-align-left"></i> Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="4" class="form-control @error('descripcion') is-invalid @enderror" 
                          placeholder="Descripción del producto">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <hr>
            <div class="form-group">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
                <a href="{{ route('productos.index') }}" class="btn btn-secondary">
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
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
</style>
@endsection

