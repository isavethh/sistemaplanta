@extends('adminlte::page')

@section('title', 'Crear Ruta')

@section('content_header')
    <h1><i class="fas fa-route"></i> Crear Ruta entre Almacenes</h1>
@endsection

@section('content')

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h5><i class="fas fa-exclamation-triangle"></i> Errores de Validación:</h5>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow">
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-map-marked-alt"></i> Nueva Ruta</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('direcciones.store') }}" method="POST" id="formRuta">
            @csrf
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Definir Ruta:</strong> 
                El origen siempre es la <strong>Planta en Santa Cruz</strong> (fijo). Seleccione el almacén de destino. 
                La distancia se calculará automáticamente.
            </div>

            @php
                $planta = $almacenes->firstWhere('es_planta', true);
            @endphp

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-industry"></i> Origen (Planta) - FIJO</label>
                        <input type="text" class="form-control bg-light" value="🏭 {{ $planta->nombre ?? 'Planta Principal' }} - Santa Cruz de la Sierra" readonly>
                        <input type="hidden" name="almacen_origen_id" id="almacen_origen_id" value="{{ $planta->id ?? '' }}"
                               data-lat="{{ $planta->latitud ?? -17.783333 }}" 
                               data-lng="{{ $planta->longitud ?? -63.182778 }}">
                        <small class="text-muted">📍 {{ $planta->direccion_completa ?? 'Santa Cruz' }}</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Almacén Destino *</label>
                        <select name="almacen_destino_id" id="almacen_destino_id" class="form-control" required onchange="actualizarMapa()">
                            <option value="">Seleccione el destino</option>
                            @foreach($almacenes as $almacen)
                                @if(!$almacen->es_planta)
                                    <option value="{{ $almacen->id }}" 
                                            data-lat="{{ $almacen->latitud }}" 
                                            data-lng="{{ $almacen->longitud }}">
                                        📦 {{ $almacen->nombre }} - {{ $almacen->direccion_completa }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">Solo almacenes (no incluye planta)</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map"></i> Visualización de Ruta</label>
                <div id="map" style="height: 400px; border: 2px solid #ddd; border-radius: 8px;"></div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-road"></i> Distancia Estimada (km) *</label>
                        <input type="number" name="distancia_km" id="distancia_km" class="form-control bg-light" step="0.01" required readonly>
                        <small class="text-muted">Se calcula automáticamente al seleccionar destino</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Tiempo Estimado (minutos) *</label>
                        <input type="number" name="tiempo_estimado_minutos" id="tiempo_estimado_minutos" class="form-control bg-light" required readonly>
                        <small class="text-muted">Se calcula automáticamente al seleccionar destino</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-info-circle"></i> Descripción de la Ruta</label>
                <textarea name="ruta_descripcion" class="form-control" rows="3" 
                          placeholder="Ej: Por Av. Banzer hasta 4to Anillo, luego norte por Alemana..."></textarea>
            </div>

            <hr>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Antes de guardar:</strong> Asegúrese de haber seleccionado un almacén destino y que la distancia y tiempo se hayan calculado.
            </div>
            
            <button type="submit" class="btn btn-success btn-lg" id="btnGuardar" onclick="return validarAntes()">
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
console.log('🚀 Iniciando mapa...');

let map = L.map('map').setView([-17.783333, -63.182778], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

let markerOrigen = null;
let markerDestino = null;
let routeLine = null;

console.log('✅ Mapa creado correctamente');

async function actualizarMapa() {
    try {
        console.log('🔥 === actualizarMapa() INICIADA === 🔥');
        
        let origenInput = document.getElementById('almacen_origen_id');
        let destinoSelect = document.getElementById('almacen_destino_id');
        
        if (!destinoSelect || !destinoSelect.value || destinoSelect.value === '') {
            console.log('❌ No hay destino seleccionado');
            return;
        }
        
        // Origen (Planta)
        let origenLat = parseFloat(origenInput.dataset.lat);
        let origenLng = parseFloat(origenInput.dataset.lng);
        
        // Destino (Almacén)
        let destinoOption = destinoSelect.options[destinoSelect.selectedIndex];
        let destinoLat = parseFloat(destinoOption.dataset.lat);
        let destinoLng = parseFloat(destinoOption.dataset.lng);
        let destinoNombre = destinoOption.textContent.trim();
        
        console.log('📍 ORIGEN (Planta):', origenLat, origenLng);
        console.log('📍 DESTINO (Almacén):', destinoLat, destinoLng, destinoNombre);
        
        // LIMPIAR TODO
        if (markerOrigen) {
            map.removeLayer(markerOrigen);
            markerOrigen = null;
        }
        if (markerDestino) {
            map.removeLayer(markerDestino);
            markerDestino = null;
        }
        if (routeLine) {
            map.removeLayer(routeLine);
            routeLine = null;
        }
        
        // 1. CREAR MARCADORES INMEDIATAMENTE (NO ESPERAR API)
        console.log('🎯 Creando marcadores...');
        
        // Marcador PLANTA (ROJO)
        markerOrigen = L.marker([origenLat, origenLng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [50, 82],
                iconAnchor: [25, 82],
                popupAnchor: [1, -70],
                shadowSize: [82, 82]
            })
        }).addTo(map);
        markerOrigen.bindPopup('<div style="text-align:center; padding:10px;"><h4 style="color:#dc3545; margin:5px;">🏭 PLANTA</h4><b>Santa Cruz de la Sierra</b><br><small>Origen fijo</small></div>').openPopup();
        
        console.log('✅ Marcador PLANTA creado');
        
        // Marcador ALMACÉN (VERDE)
        markerDestino = L.marker([destinoLat, destinoLng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [50, 82],
                iconAnchor: [25, 82],
                popupAnchor: [1, -70],
                shadowSize: [82, 82]
            })
        }).addTo(map);
        markerDestino.bindPopup('<div style="text-align:center; padding:10px;"><h4 style="color:#28a745; margin:5px;">📦 ALMACÉN</h4><b>' + destinoNombre + '</b></div>');
        
        console.log('✅ Marcador ALMACÉN creado');
        
        // 2. LLAMAR A LA API DE OSRM PARA RUTA REAL
        console.log('🌐 Llamando a API OSRM...');
        
        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${origenLng},${origenLat};${destinoLng},${destinoLat}?overview=full&geometries=geojson`;
        
        console.log('URL OSRM:', osrmUrl);
        
        try {
            const response = await fetch(osrmUrl);
            const data = await response.json();
            
            console.log('📦 Respuesta OSRM:', data);
            
            if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                const route = data.routes[0];
                const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]); // [lng, lat] -> [lat, lng]
                
                // Distancia y tiempo
                const distanciaKm = (route.distance / 1000).toFixed(2);
                const tiempoMin = Math.round(route.duration / 60);
                
                console.log(`✅ RUTA ENCONTRADA: ${distanciaKm} km, ${tiempoMin} min`);
                
                // Dibujar ruta REAL
                routeLine = L.polyline(coordinates, {
                    color: '#FF0000',
                    weight: 8,
                    opacity: 0.8
                }).addTo(map);
                
                console.log('✅ Ruta dibujada en el mapa');
                
                // Actualizar campos
                const inputDistancia = document.getElementById('distancia_km');
                const inputTiempo = document.getElementById('tiempo_estimado_minutos');
                
                inputDistancia.value = distanciaKm;
                inputTiempo.value = tiempoMin;
                
                // Cambiar color para indicar que se calculó
                inputDistancia.style.backgroundColor = '#d4edda';
                inputTiempo.style.backgroundColor = '#d4edda';
                
                console.log(`📝 Valores establecidos en inputs:`);
                console.log(`   - distancia_km: ${inputDistancia.value}`);
                console.log(`   - tiempo_estimado_minutos: ${inputTiempo.value}`);
                
                // Ajustar zoom
                map.fitBounds(routeLine.getBounds(), {padding: [50, 50]});
                
                console.log(`🎉 TODO LISTO: ${distanciaKm} km, ${tiempoMin} minutos`);
                
                // Mostrar notificación
                alert(`✅ Ruta calculada exitosamente!\n\n📏 Distancia: ${distanciaKm} km\n⏱️ Tiempo: ${tiempoMin} minutos\n\nYa puede guardar la ruta.`);
                
            } else {
                throw new Error('No se pudo calcular ruta');
            }
            
        } catch (error) {
            console.error('❌ Error con API OSRM:', error);
            console.log('⚠️ Usando fallback: línea directa');
            
            // FALLBACK: Línea directa
            routeLine = L.polyline([[origenLat, origenLng], [destinoLat, destinoLng]], {
                color: '#FF0000',
                weight: 8,
                opacity: 0.7,
                dashArray: '15, 10'
            }).addTo(map);
            
            // Calcular distancia directa (Haversine)
            const R = 6371;
            const dLat = (destinoLat - origenLat) * Math.PI / 180;
            const dLng = (destinoLng - origenLng) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(origenLat * Math.PI / 180) * Math.cos(destinoLat * Math.PI / 180) *
                    Math.sin(dLng/2) * Math.sin(dLng/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distancia = R * c;
            const tiempo = Math.round((distancia / 40) * 60);
            
            const inputDistancia = document.getElementById('distancia_km');
            const inputTiempo = document.getElementById('tiempo_estimado_minutos');
            
            inputDistancia.value = distancia.toFixed(2);
            inputTiempo.value = tiempo;
            
            // Cambiar color para indicar que se calculó
            inputDistancia.style.backgroundColor = '#fff3cd';
            inputTiempo.style.backgroundColor = '#fff3cd';
            
            console.log(`⚠️ Distancia directa: ${distancia.toFixed(2)} km, ${tiempo} min`);
            console.log(`📝 Valores establecidos (fallback):`);
            console.log(`   - distancia_km: ${inputDistancia.value}`);
            console.log(`   - tiempo_estimado_minutos: ${inputTiempo.value}`);
            
            // Ajustar zoom
            map.fitBounds([[origenLat, origenLng], [destinoLat, destinoLng]], {padding: [50, 50]});
            
            alert(`⚠️ No se pudo calcular ruta por calles.\nUsando distancia directa:\n\n📏 ${distancia.toFixed(2)} km\n⏱️ ${tiempo} minutos\n\nYa puede guardar la ruta.`);
        }
        
    } catch(error) {
        console.error('💥 ERROR FATAL:', error);
        alert('Error: ' + error.message);
    }
}

function validarAntes() {
    console.log('🔍 Validando formulario...');
    
    let origen = document.getElementById('almacen_origen_id').value;
    let destino = document.getElementById('almacen_destino_id').value;
    let distancia = document.getElementById('distancia_km').value;
    let tiempo = document.getElementById('tiempo_estimado_minutos').value;
    
    console.log('Valores:', {origen, destino, distancia, tiempo});
    
    if (!origen) {
        alert('❌ Error: No hay origen configurado');
        return false;
    }
    
    if (!destino) {
        alert('❌ Error: Debe seleccionar un almacén destino');
        return false;
    }
    
    if (!distancia || distancia == '0' || distancia == '') {
        alert('❌ Error: La distancia no se ha calculado. Seleccione un almacén destino y espere a que se calcule la ruta.');
        return false;
    }
    
    if (!tiempo || tiempo == '0' || tiempo == '') {
        alert('❌ Error: El tiempo no se ha calculado. Seleccione un almacén destino y espere a que se calcule la ruta.');
        return false;
    }
    
    console.log('✅ Validación OK, enviando formulario...');
    return true;
}

// INICIALIZAR: Mostrar la planta al cargar
setTimeout(function() {
    console.log('🏁 Inicializando...');
    
    let origenInput = document.getElementById('almacen_origen_id');
    if (origenInput && origenInput.value) {
        let plantaLat = parseFloat(origenInput.dataset.lat);
        let plantaLng = parseFloat(origenInput.dataset.lng);
        
        console.log('📍 Creando marcador inicial PLANTA:', plantaLat, plantaLng);
        
        markerOrigen = L.marker([plantaLat, plantaLng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [50, 82],
                iconAnchor: [25, 82],
                popupAnchor: [1, -70],
                shadowSize: [82, 82]
            })
        }).addTo(map);
        
        markerOrigen.bindPopup('<div style="text-align:center; padding:15px;"><h3 style="color:#dc3545; margin:10px;">🏭 PLANTA</h3><h4>Santa Cruz de la Sierra</h4><p><b>Origen fijo</b></p><small>Seleccione un almacén destino para trazar la ruta</small></div>').openPopup();
            
        map.setView([plantaLat, plantaLng], 13);
        
        console.log('✅ Marcador PLANTA mostrado correctamente');
    } else {
        console.error('❌ No se pudo inicializar el marcador');
    }
}, 300);
</script>
@endsection
