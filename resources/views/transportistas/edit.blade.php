@extends('adminlte::page')

@section('title', 'Editar Transportista')

@section('content_header')
    <h1><i class="fas fa-user-tie"></i> Editar Transportista</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('transportistas.update', $transportista) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="name">Nombre Completo *</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name', $transportista->name) }}" required>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                       value="{{ old('email', $transportista->email) }}" required>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control @error('telefono') is-invalid @enderror" 
                       value="{{ old('telefono', $transportista->telefono) }}">
                @error('telefono')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="licencia">Tipo de Licencia *</label>
                <select name="licencia" id="licencia" class="form-control @error('licencia') is-invalid @enderror" required>
                    <option value="">-- Seleccione una licencia --</option>
                    <option value="A" {{ old('licencia', $transportista->licencia) == 'A' ? 'selected' : '' }}>A - Motocicletas</option>
                    <option value="B" {{ old('licencia', $transportista->licencia) == 'B' ? 'selected' : '' }}>B - Automóviles y vehículos livianos</option>
                    <option value="C" {{ old('licencia', $transportista->licencia) == 'C' ? 'selected' : '' }}>C - Camiones y vehículos pesados</option>
                </select>
                <small class="form-text text-muted">
                    <strong>Nota:</strong> La licencia determina qué tipos de vehículos puede conducir el transportista.
                </small>
                @error('licencia')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                       placeholder="Dejar vacío para no cambiar">
                <small class="form-text text-muted">Solo ingrese una contraseña si desea cambiarla.</small>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="disponible" name="disponible" 
                           {{ old('disponible', $transportista->disponible) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="disponible">Disponible para asignaciones</label>
                </div>
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Transportista
                </button>
                <a href="{{ route('transportistas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
