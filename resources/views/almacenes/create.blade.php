@extends('adminlte::page')

@section('title', 'Crear Almacén')

@section('content_header')
    <h1><i class="fas fa-warehouse"></i> Crear Almacén</h1>
@endsection

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow">
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-map-marked-alt"></i> Nuevo Almacén con Ubicación</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('almacenes.store') }}" method="POST" id="formAlmacen">
            @csrf
            
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> <strong>Nota:</strong> 
                Solo se crean <strong>almacenes de destino</strong>. La <strong>Planta</strong> ya está creada y es fija en Santa Cruz.
            </div>

            <div class="form-group">
                <label><i class="fas fa-warehouse"></i> Nombre del Almacén *</label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                       value="{{ old('nombre') }}" required placeholder="Ej: Almacén Norte">
                @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="text-muted">Este será un almacén de destino para los envíos</small>
            </div>

            <!-- Campo oculto para asegurar que siempre sea almacén (no planta) -->
            <input type="hidden" name="es_planta" value="0">

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Dirección Completa *</label>
                <textarea name="direccion_completa" class="form-control @error('direccion_completa') is-invalid @enderror" rows="2" 
                          placeholder="Ej: Av. Banzer 500, Santa Cruz de la Sierra" required>{{ old('direccion_completa') }}</textarea>
                @error('direccion_completa')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Marque la ubicación en el mapa:</strong> 
                Haga clic en el mapa para establecer la ubicación exacta del almacén.
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
                               value="{{ old('latitud', '-17.783333') }}" required readonly>
                        @error('latitud')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-compass"></i> Longitud *</label>
                        <input type="text" name="longitud" id="longitud" class="form-control @error('longitud') is-invalid @enderror" 
                               value="{{ old('longitud', '-63.182778') }}" required readonly>
                        @error('longitud')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="activo" class="form-check-input" id="activo" checked>
                <label class="form-check-label" for="activo">
                    <i class="fas fa-check-circle"></i> Almacén Activo
                </label>
            </div>

            <hr>
            
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save"></i> Guardar Almacén
            </button>
            <a href="{{ route('almacenes.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .leaflet-container {
        border-radius: 8px;
    }
    
    .custom-marker-almacen {
        background: transparent;
        border: none;
    }
    
    .leaflet-popup-content-wrapper {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
</style>
@endsection

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Coordenadas de Santa Cruz de la Sierra (centro por defecto)
let lat = {{ old('latitud', '-17.783333') }};
let lng = {{ old('longitud', '-63.182778') }};

// Inicializar mapa
let map = L.map('map').setView([lat, lng], 13);

// Capa de mapa
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Icono personalizado para el marcador
let iconoAlmacen = L.divIcon({
    html: '<div style="background-color: #007bff; width: 35px; height: 35px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"><div style="transform: rotate(45deg); margin-top: 6px; text-align: center; color: white; font-size: 18px; font-weight: bold;">📍</div></div>',
    className: 'custom-marker-almacen',
    iconSize: [35, 35],
    iconAnchor: [17, 35]
});

// Marcador inicial
let marker = L.marker([lat, lng], {
    draggable: true,
    icon: iconoAlmacen
}).addTo(map).bindPopup('📍 <b>Ubicación del Almacén</b><br>Arrastra el marcador o haz clic en el mapa').openPopup();

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

// Botón para centrar en ubicación actual
let locationButton = L.control({position: 'topright'});
locationButton.onAdd = function(map) {
    let div = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
    div.innerHTML = '<button style="background:white; padding:8px; cursor:pointer;" title="Mi ubicación"><i class="fas fa-crosshairs"></i></button>';
    div.onclick = function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                let latlng = [position.coords.latitude, position.coords.longitude];
                map.setView(latlng, 15);
                marker.setLatLng(latlng);
                document.getElementById('latitud').value = position.coords.latitude.toFixed(7);
                document.getElementById('longitud').value = position.coords.longitude.toFixed(7);
            });
        }
    }
    return div;
};
locationButton.addTo(map);
</script>
@endsection
