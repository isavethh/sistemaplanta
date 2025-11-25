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
            
            @if($almacen->es_planta)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <strong>¡ATENCIÓN!</strong> 
                    Esta es la <strong>Planta Principal</strong> (origen fijo). Edite con cuidado.
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Nota:</strong> 
                    Este es un almacén de destino para los envíos.
                </div>
            @endif
            
            <div class="form-group">
                <label for="nombre"><i class="fas fa-tag"></i> Nombre del Almacén *</label>
                <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                       value="{{ old('nombre', $almacen->nombre) }}" required placeholder="Ingrese el nombre del almacén">
                @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="direccion_completa"><i class="fas fa-map-marker-alt"></i> Dirección Completa *</label>
                <textarea name="direccion_completa" id="direccion_completa" class="form-control @error('direccion_completa') is-invalid @enderror" 
                          rows="2" required placeholder="Ej: Av. Banzer 500, Santa Cruz de la Sierra">{{ old('direccion_completa', $almacen->direccion_completa) }}</textarea>
                @error('direccion_completa')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Actualizar ubicación en el mapa:</strong> 
                Haga clic en el mapa o arrastre el marcador para cambiar la ubicación.
            </div>

            <div class="form-group">
                <label><i class="fas fa-map"></i> Mapa de Ubicación</label>
                <div id="map" style="height: 400px; border: 2px solid #ddd; border-radius: 8px;"></div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-compass"></i> Latitud *</label>
                        <input type="text" name="latitud" id="latitud" class="form-control @error('latitud') is-invalid @enderror" 
                               value="{{ old('latitud', $almacen->latitud) }}" required readonly>
                        @error('latitud')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-compass"></i> Longitud *</label>
                        <input type="text" name="longitud" id="longitud" class="form-control @error('longitud') is-invalid @enderror" 
                               value="{{ old('longitud', $almacen->longitud) }}" required readonly>
                        @error('longitud')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="activo" class="form-check-input" id="activo" {{ $almacen->activo ? 'checked' : '' }}>
                <label class="form-check-label" for="activo">
                    <i class="fas fa-check-circle"></i> Almacén Activo
                </label>
            </div>

            <hr>
            <div class="form-group">
                <button type="submit" class="btn btn-warning btn-lg">
                    <i class="fas fa-save"></i> Actualizar Almacén
                </button>
                <a href="{{ route('almacenes.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .card {
        border-radius: 10px;
    }
    .form-control:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    .leaflet-container {
        border-radius: 8px;
    }
    .custom-marker-almacen {
        background: transparent;
        border: none;
    }
</style>
@endsection

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Coordenadas actuales del almacén
let lat = {{ $almacen->latitud ?? -17.783333 }};
let lng = {{ $almacen->longitud ?? -63.182778 }};

// Inicializar mapa
let map = L.map('map').setView([lat, lng], 15);

// Capa de mapa
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Icono personalizado para el marcador
let iconoAlmacen = L.divIcon({
    html: '<div style="background-color: #ffc107; width: 35px; height: 35px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"><div style="transform: rotate(45deg); margin-top: 6px; text-align: center; color: white; font-size: 18px; font-weight: bold;">📍</div></div>',
    className: 'custom-marker-almacen',
    iconSize: [35, 35],
    iconAnchor: [17, 35]
});

// Marcador inicial
let marker = L.marker([lat, lng], {
    draggable: true,
    icon: iconoAlmacen
}).addTo(map).bindPopup('📍 <b>{{ $almacen->nombre }}</b><br>Arrastra el marcador o haz clic en el mapa').openPopup();

// Actualizar coordenadas al arrastrar el marcador
marker.on('dragend', function(e) {
    let position = marker.getLatLng();
    document.getElementById('latitud').value = position.lat.toFixed(7);
    document.getElementById('longitud').value = position.lng.toFixed(7);
    marker.bindPopup('📍 <b>Nueva ubicación</b><br>Lat: ' + position.lat.toFixed(5) + '<br>Lng: ' + position.lng.toFixed(5)).openPopup();
});

// Click en el mapa para colocar marcador
map.on('click', function(e) {
    marker.setLatLng(e.latlng);
    document.getElementById('latitud').value = e.latlng.lat.toFixed(7);
    document.getElementById('longitud').value = e.latlng.lng.toFixed(7);
    marker.bindPopup('📍 <b>Nueva ubicación</b><br>Lat: ' + e.latlng.lat.toFixed(5) + '<br>Lng: ' + e.latlng.lng.toFixed(5)).openPopup();
});
</script>
@endsection
