@extends('adminlte::page')
@section('title', 'Editar Ruta')
@section('content_header')
    <h1><i class="fas fa-route"></i> Editar Ruta</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-warning">
        <h3 class="card-title text-white"><i class="fas fa-edit"></i> Editar Ruta</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('direcciones.update', $direccion) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Editar Ruta:</strong> 
                El origen siempre es la <strong>Planta</strong> (fijo). Puede cambiar el almacén de destino.
            </div>

            @php
                $planta = \App\Models\Almacen::where('es_planta', true)->first();
            @endphp

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-industry"></i> Origen (Planta) - FIJO</label>
                        <input type="text" class="form-control bg-light" 
                               value="🏭 {{ $direccion->almacenOrigen->nombre ?? 'Planta Principal' }} - Santa Cruz" 
                               readonly>
                        <input type="hidden" name="almacen_origen_id" value="{{ $direccion->almacen_origen_id }}">
                        <small class="text-muted">📍 No se puede cambiar el origen</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Almacén Destino *</label>
                        <select name="almacen_destino_id" class="form-control" required>
                            <option value="">Seleccione el destino</option>
                            @foreach(\App\Models\Almacen::where('es_planta', false)->where('activo', true)->get() as $almacen)
                                <option value="{{ $almacen->id }}" {{ $direccion->almacen_destino_id == $almacen->id ? 'selected' : '' }}>
                                    📦 {{ $almacen->nombre }} - {{ $almacen->direccion_completa }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-road"></i> Distancia (km)</label>
                        <input type="number" name="distancia_km" class="form-control" 
                               step="0.01" value="{{ $direccion->distancia_km }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Tiempo Estimado (minutos)</label>
                        <input type="number" name="tiempo_estimado_minutos" class="form-control" 
                               value="{{ $direccion->tiempo_estimado_minutos }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-info-circle"></i> Descripción de la Ruta</label>
                <textarea name="ruta_descripcion" class="form-control" rows="3" 
                          placeholder="Ej: Por Av. Banzer hasta 4to Anillo...">{{ $direccion->ruta_descripcion }}</textarea>
            </div>

            <hr>

            <button type="submit" class="btn btn-warning btn-lg">
                <i class="fas fa-save"></i> Actualizar Ruta
            </button>
            <a href="{{ route('direcciones.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>
@endsection
