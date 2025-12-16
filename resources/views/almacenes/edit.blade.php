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
        <form action="{{ route('almacenes.update', $almacen->id) }}" method="POST">
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
                <label for="direccion_completa"><i class="fas fa-map-marker-alt"></i> Dirección Completa</label>
                <input type="text" name="direccion_completa" id="direccion_completa" class="form-control @error('direccion_completa') is-invalid @enderror" 
                       value="{{ old('direccion_completa', $almacen->direccion_completa) }}" placeholder="Ingrese la dirección completa del almacén">
                @error('direccion_completa')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="latitud"><i class="fas fa-map-pin"></i> Latitud</label>
                        <input type="number" step="any" name="latitud" id="latitud" class="form-control @error('latitud') is-invalid @enderror" 
                               value="{{ old('latitud', $almacen->latitud) }}" placeholder="-17.8146">
                        @error('latitud')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="longitud"><i class="fas fa-map-pin"></i> Longitud</label>
                        <input type="number" step="any" name="longitud" id="longitud" class="form-control @error('longitud') is-invalid @enderror" 
                               value="{{ old('longitud', $almacen->longitud) }}" placeholder="-63.1561">
                        @error('longitud')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            @if(!auth()->user()->esPropietario())
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="activo" id="activo" class="custom-control-input" {{ old('activo', $almacen->activo) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="activo">Almacén Activo</label>
                </div>
            </div>
            @endif

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
