@extends('adminlte::page')
@section('title', 'Editar Almacén')
@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Almacén</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-warning">
        <h3 class="card-title"><i class="fas fa-warehouse"></i> Modificar Información del Almacén</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('almacenes.update', $almacen) }}" method="POST">
            @csrf 
            @method('PUT')
            
            <div class="form-group">
                <label for="nombre"><i class="fas fa-tag"></i> Nombre del Almacén *</label>
                <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                       value="{{ old('nombre', $almacen->nombre) }}" required placeholder="Ingrese el nombre del almacén">
                @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="direccion_id"><i class="fas fa-map-marker-alt"></i> Dirección *</label>
                <select name="direccion_id" id="direccion_id" class="form-control @error('direccion_id') is-invalid @enderror" required>
                    <option value="">Seleccione una dirección</option>
                    @foreach($direcciones as $direccion)
                        <option value="{{ $direccion->id }}" {{ old('direccion_id', $almacen->direccion_id) == $direccion->id ? 'selected' : '' }}>
                            {{ $direccion->descripcion }}
                        </option>
                    @endforeach
                </select>
                @error('direccion_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <hr>
            <div class="form-group">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar Almacén
                </button>
                <a href="{{ route('almacenes.index') }}" class="btn btn-secondary">
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
