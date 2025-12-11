@extends('adminlte::page')

@section('title', 'Crear Tipo de Transporte')

@section('content_header')
    <h1><i class="fas fa-plus-circle"></i> Crear Tipo de Transporte</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-truck-loading"></i> Nuevo Tipo</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('tipos-transporte.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Nombre *</label>
                <select name="nombre" class="form-control @error('nombre') is-invalid @enderror" required>
                    <option value="">Seleccione el tipo</option>
                    <option value="Aislado">Aislado</option>
                    <option value="Ventilado">Ventilado</option>
                    <option value="Hermético">Hermético</option>
                    <option value="Refrigerado">Refrigerado</option>
                    <option value="Congelado">Congelado</option>
                    <option value="Estándar">Estándar</option>
                </select>
                @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción del tipo de transporte"></textarea>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="requiere_temperatura_controlada" class="form-check-input" id="requiere_temp" onchange="toggleTemperatura()">
                <label class="form-check-label" for="requiere_temp">
                    Requiere Control de Temperatura
                </label>
            </div>

            <div id="temperatura-fields" style="display:none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-thermometer-empty"></i> Temperatura Mínima (°C)</label>
                            <input type="number" name="temperatura_minima" class="form-control" step="0.01" placeholder="-20">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-thermometer-full"></i> Temperatura Máxima (°C)</label>
                            <input type="number" name="temperatura_maxima" class="form-control" step="0.01" placeholder="25">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="activo" class="form-check-input" id="activo" checked>
                <label class="form-check-label" for="activo">Activo</label>
            </div>

            <hr>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar
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

