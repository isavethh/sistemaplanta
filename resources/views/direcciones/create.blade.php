@extends('adminlte::page')

@section('title', 'Crear Ruta')

@section('content_header')
    <h1><i class="fas fa-route"></i> Crear Ruta entre Almacenes</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-map-marked-alt"></i> Nueva Ruta</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('direcciones.store') }}" method="POST">
            @csrf
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Definir Ruta:</strong> 
                Seleccione el almacén de origen (Planta) y el almacén de destino. 
                La distancia se calculará automáticamente.
            </div>

            <div class="form-group">
                <label><i class="fas fa-warehouse"></i> Almacén Origen (Planta) *</label>
                <select name="almacen_origen_id" id="almacen_origen_id" class="form-control" required onchange="actualizarMapa()">
                    <option value="">Seleccione el origen</option>
                    @foreach($almacenes as $almacen)
                        <option value="{{ $almacen->id }}" 
                                data-lat="{{ $almacen->latitud }}" 
                                data-lng="{{ $almacen->longitud }}"
                                {{ $almacen->codigo == 'ALM-PLANTA' ? 'selected' : '' }}>
                            {{ $almacen->nombre }} ({{ $almacen->codigo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Almacén Destino *</label>
                <select name="almacen_destino_id" id="almacen_destino_id" class="form-control" required onchange="actualizarMapa()">
                    <option value="">Seleccione el destino</option>
                    @foreach($almacenes as $almacen)
                        <option value="{{ $almacen->id }}" 
                                data-lat="{{ $almacen->latitud }}" 
                                data-lng="{{ $almacen->longitud }}">
                            {{ $almacen->nombre }} ({{ $almacen->codigo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map"></i> Visualización de Ruta</label>
                <div id="map" style="height: 400px; border: 2px solid #ddd; border-radius: 8px;"></div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-road"></i> Distancia Estimada (km)</label>
                        <input type="number" name="distancia_km" id="distancia_km" class="form-control" step="0.01" readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Tiempo Estimado (minutos)</label>
                        <input type="number" name="tiempo_estimado_minutos" id="tiempo_estimado_minutos" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-info-circle"></i> Descripción de la Ruta</label>
                <textarea name="ruta_descripcion" class="form-control" rows="3" 
                          placeholder="Ej: Por Av. Banzer hasta 4to Anillo, luego norte por Alemana..."></textarea>
            </div>

            <hr>
            
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save"></i> Guardar Ruta
            </button>
            <a href="{{ route('direcciones.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .custom-marker {
        background: transparent;
        border: none;
    }
    
    @keyframes dash {
        to {
            stroke-dashoffset: -20;
        }
    }
    
    .ruta-animada {
        animation: dash 1s linear infinite;
    }
    
    .leaflet-popup-content-wrapper {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    
    .leaflet-popup-content {
        margin: 15px;
        font-family: Arial, sans-serif;
    }
</style>
@endsection

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map = L.map('map').setView([-17.783333, -63.182778], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

let markerOrigen = null;
let markerDestino = null;
let routeLine = null;

function actualizarMapa() {
    let origenSelect = document.getElementById('almacen_origen_id');
    let destinoSelect = document.getElementById('almacen_destino_id');
    
    if (!origenSelect.value || !destinoSelect.value) return;
    
    let origenOption = origenSelect.options[origenSelect.selectedIndex];
    let destinoOption = destinoSelect.options[destinoSelect.selectedIndex];
    
    let origenLat = parseFloat(origenOption.dataset.lat);
    let origenLng = parseFloat(origenOption.dataset.lng);
    let destinoLat = parseFloat(destinoOption.dataset.lat);
    let destinoLng = parseFloat(destinoOption.dataset.lng);
    
    // Limpiar marcadores anteriores
    if (markerOrigen) map.removeLayer(markerOrigen);
    if (markerDestino) map.removeLayer(markerDestino);
    if (routeLine) map.removeLayer(routeLine);
    
    // MARCADOR ORIGEN - ROJO (Planta)
    let iconoOrigen = L.divIcon({
        html: '<div style="background-color: #dc3545; width: 40px; height: 40px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"><div style="transform: rotate(45deg); margin-top: 8px; text-align: center; color: white; font-size: 20px; font-weight: bold;">🏭</div></div>',
        className: 'custom-marker',
        iconSize: [40, 40],
        iconAnchor: [20, 40]
    });
    
    markerOrigen = L.marker([origenLat, origenLng], { icon: iconoOrigen })
        .addTo(map)
        .bindPopup('<div style="text-align: center;"><b style="color: #dc3545; font-size: 16px;">🏭 ORIGEN (PLANTA)</b><br>' + origenOption.text + '</div>')
        .openPopup();
    
    // MARCADOR DESTINO - VERDE (Almacén)
    let iconoDestino = L.divIcon({
        html: '<div style="background-color: #28a745; width: 40px; height: 40px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"><div style="transform: rotate(45deg); margin-top: 8px; text-align: center; color: white; font-size: 20px; font-weight: bold;">📦</div></div>',
        className: 'custom-marker',
        iconSize: [40, 40],
        iconAnchor: [20, 40]
    });
    
    markerDestino = L.marker([destinoLat, destinoLng], { icon: iconoDestino })
        .addTo(map)
        .bindPopup('<div style="text-align: center;"><b style="color: #28a745; font-size: 16px;">📦 DESTINO (ALMACÉN)</b><br>' + destinoOption.text + '</div>');
    
    // LÍNEA DE RUTA - AZUL CON ANIMACIÓN
    routeLine = L.polyline([[origenLat, origenLng], [destinoLat, destinoLng]], {
        color: '#007bff',
        weight: 5,
        opacity: 0.8,
        dashArray: '10, 10',
        className: 'ruta-animada'
    }).addTo(map);
    
    // Añadir flechas direccionales en la mitad de la ruta
    let midLat = (origenLat + destinoLat) / 2;
    let midLng = (origenLng + destinoLng) / 2;
    
    let iconoFlecha = L.divIcon({
        html: '<div style="font-size: 30px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">➡️</div>',
        className: 'flecha-ruta',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });
    
    L.marker([midLat, midLng], { icon: iconoFlecha }).addTo(map);
    
    // Ajustar vista con padding
    map.fitBounds([[origenLat, origenLng], [destinoLat, destinoLng]], {padding: [80, 80]});
    
    // Calcular distancia (Haversine)
    let R = 6371;
    let dLat = (destinoLat - origenLat) * Math.PI / 180;
    let dLng = (destinoLng - origenLng) * Math.PI / 180;
    let a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(origenLat * Math.PI / 180) * Math.cos(destinoLat * Math.PI / 180) *
            Math.sin(dLng/2) * Math.sin(dLng/2);
    let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    let distancia = R * c;
    
    document.getElementById('distancia_km').value = distancia.toFixed(2);
    
    // Estimar tiempo
    let tiempoMinutos = Math.round((distancia / 40) * 60);
    document.getElementById('tiempo_estimado_minutos').value = tiempoMinutos;
}

// Inicializar mapa si hay planta seleccionada
window.onload = function() {
    actualizarMapa();
};
</script>
@endsection
