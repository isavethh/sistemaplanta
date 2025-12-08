@extends('adminlte::page')

@section('title', 'Nuevo Vehículo')

@section('content_header')
    <h1><i class="fas fa-truck"></i> Nuevo Vehículo</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('vehiculos.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="placa">Placa *</label>
                        <input type="text" name="placa" id="placa" class="form-control @error('placa') is-invalid @enderror" 
                               value="{{ old('placa') }}" required placeholder="Ej: ABC-1234">
                        @error('placa')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tipo_transporte_id">Tipo de Transporte</label>
                        <select name="tipo_transporte_id" id="tipo_transporte_id" class="form-control @error('tipo_transporte_id') is-invalid @enderror">
                            <option value="">-- Seleccione --</option>
                            @foreach($tiposTransporte as $tipo)
                                <option value="{{ $tipo->id }}" {{ old('tipo_transporte_id') == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_transporte_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tamano_vehiculo_id">Tamaño del Vehículo</label>
                        <select name="tamano_vehiculo_id" id="tamano_vehiculo_id" class="form-control @error('tamano_vehiculo_id') is-invalid @enderror">
                            <option value="">-- Seleccione --</option>
                            @foreach($tamanosVehiculo as $tamano)
                                <option value="{{ $tamano->id }}" {{ old('tamano_vehiculo_id') == $tamano->id ? 'selected' : '' }}>
                                    {{ $tamano->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('tamano_vehiculo_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="licencia_requerida">Licencia Requerida *</label>
                        <select name="licencia_requerida" id="licencia_requerida" class="form-control @error('licencia_requerida') is-invalid @enderror" required>
                            <option value="">-- Seleccione --</option>
                            <option value="A" {{ old('licencia_requerida') == 'A' ? 'selected' : '' }}>A (Motocicletas)</option>
                            <option value="B" {{ old('licencia_requerida', 'B') == 'B' ? 'selected' : '' }}>B (Automóviles)</option>
                            <option value="C" {{ old('licencia_requerida') == 'C' ? 'selected' : '' }}>C (Camiones)</option>
                        </select>
                        @error('licencia_requerida')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="capacidad_carga">Capacidad de Carga</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="capacidad_carga" id="capacidad_carga" 
                                   class="form-control @error('capacidad_carga') is-invalid @enderror" 
                                   value="{{ old('capacidad_carga') }}" placeholder="0.00">
                            <select name="unidad_medida_carga_id" class="form-control" style="max-width: 120px;">
                                <option value="">Unidad</option>
                                @foreach($unidadesMedida as $unidad)
                                    <option value="{{ $unidad->id }}" {{ old('unidad_medida_carga_id') == $unidad->id ? 'selected' : '' }}>
                                        {{ $unidad->abreviatura }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('capacidad_carga')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
