@extends('adminlte::page')

@section('title', 'Reportar Incidente')

@section('adminlte_css_pre')
    @include('layouts.preloader-killer')
@endsection

@section('content_header')
    <h1 class="m-0"><i class="fas fa-exclamation-triangle text-danger"></i> Reportar Nuevo Incidente</h1>
    <small class="text-muted">Completa el formulario para reportar un incidente durante uno de tus envíos</small>
@endsection

@section('content')
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card card-outline card-danger">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-info-circle"></i> Información</h5>
    </div>
    <div class="card-body">
        <p class="mb-0">
            <strong>¿Cuándo reportar un incidente?</strong> Reporta cualquier problema que ocurra durante el transporte de un envío, 
            como accidentes, averías, robos, pérdidas, daños, retrasos o cualquier otra situación que afecte la entrega.
        </p>
        <p class="mb-0 mt-2">
            <strong>¿Solicitar ayuda?</strong> Si el incidente es grave y necesitas asistencia inmediata del administrador, 
            marca la opción "Solicitar Ayuda". De lo contrario, solo se registrará como un incidente normal.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('reportes.mis-incidentes.store') }}" enctype="multipart/form-data">
    @csrf
    
    <div class="card">
        <div class="card-header bg-danger">
            <h5 class="card-title text-white"><i class="fas fa-edit"></i> Datos del Incidente</h5>
        </div>
        <div class="card-body">
            <!-- Envío -->
            <div class="form-group">
                <label for="envio_id"><strong>Envío Afectado *</strong></label>
                <select name="envio_id" id="envio_id" class="form-control @error('envio_id') is-invalid @enderror" required>
                    <option value="">-- Seleccione un envío --</option>
                    @foreach($envios as $envio)
                        <option value="{{ $envio->id }}" {{ old('envio_id') == $envio->id ? 'selected' : '' }}>
                            {{ $envio->codigo }} - {{ $envio->almacen_nombre ?? 'Sin almacén' }} 
                            ({{ ucfirst($envio->estado) }})
                        </option>
                    @endforeach
                </select>
                @error('envio_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                @if($envios->isEmpty())
                    <small class="text-danger">No tienes envíos asignados en este momento.</small>
                @endif
            </div>
            
            <!-- Tipo de Incidente -->
            <div class="form-group">
                <label for="tipo_incidente"><strong>Tipo de Incidente *</strong></label>
                <select name="tipo_incidente" id="tipo_incidente" class="form-control @error('tipo_incidente') is-invalid @enderror" required>
                    <option value="">-- Seleccione el tipo --</option>
                    @foreach($tiposIncidente as $key => $label)
                        <option value="{{ $key }}" {{ old('tipo_incidente') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('tipo_incidente')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            
            <!-- Descripción -->
            <div class="form-group">
                <label for="descripcion"><strong>Descripción del Incidente *</strong></label>
                <textarea name="descripcion" id="descripcion" 
                          class="form-control @error('descripcion') is-invalid @enderror" 
                          rows="5" 
                          placeholder="Describe detalladamente qué ocurrió, cuándo, dónde y cualquier información relevante..."
                          required>{{ old('descripcion') }}</textarea>
                <small class="form-text text-muted">Mínimo 10 caracteres. Sé lo más detallado posible.</small>
                @error('descripcion')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            
            <!-- Foto (Opcional) -->
            <div class="form-group">
                <label for="foto"><strong>Foto del Incidente (Opcional)</strong></label>
                <input type="file" name="foto" id="foto" 
                       class="form-control-file @error('foto') is-invalid @enderror"
                       accept="image/jpeg,image/jpg,image/png,image/gif">
                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                @error('foto')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
            
            <!-- Solicitar Ayuda -->
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="solicitar_ayuda" id="solicitar_ayuda" 
                           value="1" 
                           class="custom-control-input"
                           {{ old('solicitar_ayuda') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="solicitar_ayuda">
                        <strong class="text-danger">
                            <i class="fas fa-exclamation-circle"></i> Solicitar Ayuda Urgente del Administrador
                        </strong>
                    </label>
                </div>
                <small class="form-text text-muted">
                    Marca esta opción si el incidente es grave y necesitas asistencia inmediata. 
                    El administrador será notificado y podrá ayudarte a resolver la situación.
                </small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-paper-plane"></i> Reportar Incidente
            </button>
            <a href="{{ route('reportes.mis-incidentes') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </div>
</form>
@endsection

@section('js')
<script>
    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const descripcion = document.getElementById('descripcion').value.trim();
        if (descripcion.length < 10) {
            e.preventDefault();
            showAlert('La descripción debe tener al menos 10 caracteres.', 'Validación', 'fa-exclamation-triangle', 'bg-warning');
            return false;
        }
    });
</script>
@include('partials.modal-alert')
@endsection

