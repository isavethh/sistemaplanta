@extends('adminlte::page')

@section('title', 'Editar Tipo de Transporte')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Tipo de Transporte</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-warning">
        <h3 class="card-title"><i class="fas fa-truck-loading"></i> Editar Tipo</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('tipos-transporte.update', $tiposTransporte) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Nombre *</label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                       value="{{ old('nombre', $tiposTransporte->nombre) }}" required>
                @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $tiposTransporte->descripcion) }}</textarea>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="requiere_temperatura_controlada" class="form-check-input" id="requiere_temp" 
                       {{ $tiposTransporte->requiere_temperatura_controlada ? 'checked' : '' }} onchange="toggleTemperatura()">
                <label class="form-check-label" for="requiere_temp">
                    Requiere Control de Temperatura
                </label>
            </div>

            <div id="temperatura-fields" style="display:{{ $tiposTransporte->requiere_temperatura_controlada ? 'block' : 'none' }};">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-thermometer-empty"></i> Temperatura Mínima (°C)</label>
                            <input type="number" name="temperatura_minima" class="form-control" step="0.01" 
                                   value="{{ old('temperatura_minima', $tiposTransporte->temperatura_minima) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-thermometer-full"></i> Temperatura Máxima (°C)</label>
                            <input type="number" name="temperatura_maxima" class="form-control" step="0.01" 
                                   value="{{ old('temperatura_maxima', $tiposTransporte->temperatura_maxima) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="activo" class="form-check-input" id="activo" {{ $tiposTransporte->activo ? 'checked' : '' }}>
                <label class="form-check-label" for="activo">Activo</label>
            </div>

            <hr>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> Actualizar
            </button>
            <a href="{{ route('tipos-transporte.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
function toggleTemperatura() {
    const checkbox = document.getElementById('requiere_temp');
    const fields = document.getElementById('temperatura-fields');
    fields.style.display = checkbox.checked ? 'block' : 'none';
}
</script>
@endsection

