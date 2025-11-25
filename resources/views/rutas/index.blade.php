@extends('adminlte::page')

@section('title', 'Rutas en Tiempo Real')

@section('content_header')
    <h1><i class="fas fa-route"></i> Rutas en Tiempo Real</h1>
@endsection

@section('content')
<div class="row">
    <!-- Envíos Pendientes -->
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-gradient-warning">
                <h3 class="card-title text-dark"><i class="fas fa-list"></i> Envíos Pendientes</h3>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @php
                    $enviosPendientes = \App\Models\Envio::with(['almacenDestino', 'asignacion.vehiculo', 'asignacion.transportista'])
                        ->whereIn('estado', ['pendiente', 'en_transito'])
                        ->get();
                @endphp

                @forelse($enviosPendientes as $envio)
                    <div class="envio-card mb-3 p-3 border rounded {{ $envio->estado == 'en_transito' ? 'bg-info text-white' : 'bg-light' }}" 
                         data-envio-id="{{ $envio->id }}"
                         data-codigo="{{ $envio->codigo }}"
                         data-lat="{{ $envio->almacenDestino->latitud ?? -17.78 }}"
                         data-lng="{{ $envio->almacenDestino->longitud ?? -63.18 }}"
                         style="cursor: pointer;">
                        <h5 class="mb-2">
                            <span class="badge {{ $envio->estado == 'en_transito' ? 'badge-warning' : 'badge-secondary' }}">
                                {{ strtoupper($envio->estado) }}
                            </span>
                        </h5>
                        <p class="mb-1"><strong>Código:</strong> {{ $envio->codigo }}</p>
                        <p class="mb-1"><strong>Destino:</strong> 📦 {{ $envio->almacenDestino->nombre ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Dirección:</strong> {{ $envio->almacenDestino->direccion_completa ?? 'N/A' }}</p>
                        @if($envio->asignacion && $envio->asignacion->transportista)
                            <p class="mb-1"><strong>Transportista:</strong> {{ $envio->asignacion->transportista->name }}</p>
                        @endif
                        @if($envio->asignacion && $envio->asignacion->vehiculo)
                            <p class="mb-1"><strong>Vehículo:</strong> {{ $envio->asignacion->vehiculo->placa }}</p>
                        @endif
                        <button class="btn btn-sm btn-primary mt-2" onclick="iniciarSimulacion({{ $envio->id }}, '{{ $envio->codigo }}', {{ $envio->almacenDestino->latitud ?? -17.78 }}, {{ $envio->almacenDestino->longitud ?? -63.18 }})">
                            <i class="fas fa-play"></i> {{ $envio->estado == 'pendiente' ? 'Iniciar Ruta' : 'Ver en Mapa' }}
                        </button>
                    </div>
                @empty
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay envíos pendientes o en tránsito
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Mapa -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-map"></i> Mapa de Rutas</h3>
            </div>
            <div class="card-body">
                <div id="info-panel" class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> Seleccione un envío de la lista para ver su ruta en el mapa
                </div>
                <div id="map" style="height: 500px; border-radius: 8px;"></div>
            </div>
        </div>

        <!-- Panel de Control de Simulación -->
        <div class="card shadow mt-3" id="control-panel" style="display: none;">
            <div class="card-header bg-gradient-success">
                <h3 class="card-title text-white"><i class="fas fa-cogs"></i> Control de Simulación</h3>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5>Envío: <span id="envio-codigo">-</span></h5>
                        <p class="mb-0">Estado: <span id="envio-estado" class="badge badge-info">-</span></p>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-danger" onclick="detenerSimulacion()">
                            <i class="fas fa-stop"></i> Detener
                        </button>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 25px;">
                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%">0%</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
    .envio-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transform: translateY(-2px);
        transition: all 0.3s;
    }
    .leaflet-container {
        font-family: inherit;
    }
    /* Ocultar las instrucciones de texto del routing */
    .leaflet-routing-container {
        display: none;
    }
    /* Estilo personalizado para la ruta */
    .leaflet-routing-line {
        stroke: #2196F3;
        stroke-width: 5;
        opacity: 0.8;
    }
</style>
@endsection

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
// Coordenadas de Santa Cruz de la Sierra, Bolivia (Planta - Punto Fijo)
const PLANTA_COORDS = [-17.783333, -63.182778]; // Coordenadas de Santa Cruz

let map, vehiculoMarker, routingControl, rutaReal;
let simulacionActiva = false;
let simulacionInterval;

// Inicializar mapa
document.addEventListener('DOMContentLoaded', function() {
    map = L.map('map').setView(PLANTA_COORDS, 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);
    
    // Marcador de la planta (punto fijo)
    L.marker(PLANTA_COORDS, {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map).bindPopup('<b>Planta - Punto de Origen</b><br>Santa Cruz de la Sierra').openPopup();
});

function iniciarSimulacion(envioId, codigo, lat, lng) {
    if (simulacionActiva) {
        alert('Ya hay una simulación en curso. Deténgala primero.');
        return;
    }
    
    // Usar coordenadas reales del almacén destino
    const destino = [lat, lng];
    
    // Limpiar mapa
    if (vehiculoMarker) map.removeLayer(vehiculoMarker);
    if (routingControl) map.removeControl(routingControl);
    
    // Actualizar info panel
    document.getElementById('info-panel').innerHTML = `<i class="fas fa-spinner fa-spin"></i> Calculando ruta real del envío <strong>${codigo}</strong>...`;
    document.getElementById('info-panel').className = 'alert alert-info mb-3';
    
    // Marcador de destino con icono personalizado
    const destinoMarker = L.marker(destino, {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [35, 57],
            iconAnchor: [17, 57],
            popupAnchor: [1, -45],
            shadowSize: [57, 57]
        })
    }).addTo(map).bindPopup('<b>📦 Punto de Entrega</b>');
    
    // Crear ruta REAL usando OpenStreetMap Routing Service (OSRM)
    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(PLANTA_COORDS[0], PLANTA_COORDS[1]),
            L.latLng(destino[0], destino[1])
        ],
        routeWhileDragging: false,
        addWaypoints: false,
        draggableWaypoints: false,
        fitSelectedRoutes: true,
        showAlternatives: false,
        lineOptions: {
            styles: [{
                color: '#2196F3',
                opacity: 0.8,
                weight: 6
            }],
            extendToWaypoints: true,
            missingRouteTolerance: 0
        },
        createMarker: function(i, waypoint, n) {
            if (i === 0) {
                // Marcador de origen (Planta)
                return L.marker(waypoint.latLng, {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                        iconSize: [35, 57],
                        iconAnchor: [17, 57],
                        popupAnchor: [1, -45],
                        shadowSize: [57, 57]
                    })
                }).bindPopup('<b>🏭 Planta - Origen</b><br>Santa Cruz de la Sierra');
            }
            // El destino ya lo creamos arriba
            return null;
        }
    }).addTo(map);
    
    // Cuando la ruta esté lista
    routingControl.on('routesfound', function(e) {
        rutaReal = e.routes[0].coordinates; // Guardar coordenadas de la ruta real
        const distanciaKm = (e.routes[0].summary.totalDistance / 1000).toFixed(2);
        const tiempoMin = Math.round(e.routes[0].summary.totalTime / 60);
        
        // Actualizar info panel
        document.getElementById('info-panel').innerHTML = `
            <i class="fas fa-route"></i> Ruta calculada: <strong>${distanciaKm} km</strong> | 
            Tiempo estimado: <strong>${tiempoMin} min</strong>
        `;
        document.getElementById('info-panel').className = 'alert alert-success mb-3';
        
        // Esperar 2 segundos y comenzar simulación
        setTimeout(() => {
            comenzarSimulacionRuta(envioId, codigo, rutaReal, destinoMarker);
        }, 2000);
    });
    
    routingControl.on('routingerror', function(e) {
        console.error('Error al calcular ruta:', e);
        document.getElementById('info-panel').innerHTML = `
            <i class="fas fa-exclamation-triangle"></i> No se pudo calcular una ruta real. 
            Usando ruta directa...
        `;
        document.getElementById('info-panel').className = 'alert alert-warning mb-3';
        
        // Fallback a ruta simple
        rutaReal = [
            {lat: PLANTA_COORDS[0], lng: PLANTA_COORDS[1]},
            {lat: destino[0], lng: destino[1]}
        ];
        setTimeout(() => {
            comenzarSimulacionRuta(envioId, codigo, rutaReal, destinoMarker);
        }, 2000);
    });
    
    // Actualizar estado a "en_transito"
    actualizarEstado(envioId, 'en_transito');
}

function comenzarSimulacionRuta(envioId, codigo, rutaCoords, destinoMarker) {
    // Mostrar panel de control
    document.getElementById('control-panel').style.display = 'block';
    document.getElementById('envio-codigo').textContent = codigo;
    document.getElementById('envio-estado').textContent = 'EN TRÁNSITO';
    document.getElementById('envio-estado').className = 'badge badge-info';
    
    // Actualizar info panel
    document.getElementById('info-panel').innerHTML = `<i class="fas fa-truck"></i> Vehículo en ruta hacia <strong>${codigo}</strong>...`;
    document.getElementById('info-panel').className = 'alert alert-info mb-3';
    
    // Crear marcador del vehículo con icono de camión
    vehiculoMarker = L.marker([rutaCoords[0].lat, rutaCoords[0].lng], {
        icon: L.divIcon({
            className: 'custom-truck-icon',
            html: '<div style="font-size: 30px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">🚚</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        zIndexOffset: 1000
    }).addTo(map).bindPopup(`<b>Envío: ${codigo}</b><br>En tránsito...`).openPopup();
    
    // Simular movimiento a lo largo de la ruta REAL
    simulacionActiva = true;
    let puntoActual = 0;
    const totalPuntos = rutaCoords.length;
    const velocidad = Math.max(50, Math.floor(totalPuntos / 100)); // Ajustar velocidad según longitud de ruta
    
    simulacionInterval = setInterval(() => {
        if (puntoActual < totalPuntos) {
            const coord = rutaCoords[puntoActual];
            const progreso = (puntoActual / totalPuntos) * 100;
            
            // Actualizar barra de progreso
            document.getElementById('progress-bar').style.width = progreso + '%';
            document.getElementById('progress-bar').textContent = Math.round(progreso) + '%';
            
            // Mover vehículo
            vehiculoMarker.setLatLng([coord.lat, coord.lng]);
            
            // Centrar mapa en el vehículo suavemente
            if (puntoActual % 10 === 0) {
                map.panTo([coord.lat, coord.lng], {animate: true, duration: 0.5});
            }
            
            puntoActual += velocidad;
        } else {
            // Llegó al destino
            clearInterval(simulacionInterval);
            simulacionActiva = false;
            
            // Posicionar en el destino exacto
            const ultimoCoord = rutaCoords[totalPuntos - 1];
            vehiculoMarker.setLatLng([ultimoCoord.lat, ultimoCoord.lng]);
            
            // Envío completado
            vehiculoMarker.bindPopup(`<b>Envío: ${codigo}</b><br>✅ ¡Entregado!`).openPopup();
            document.getElementById('envio-estado').textContent = 'ENTREGADO';
            document.getElementById('envio-estado').className = 'badge badge-success';
            document.getElementById('progress-bar').className = 'progress-bar bg-success';
            
            // Actualizar estado a "entregado"
            actualizarEstado(envioId, 'entregado');
            
            document.getElementById('info-panel').innerHTML = `<i class="fas fa-check-circle"></i> ¡Envío <strong>${codigo}</strong> entregado exitosamente!`;
            document.getElementById('info-panel').className = 'alert alert-success mb-3';
            
            // Mostrar destino
            destinoMarker.openPopup();
            
            setTimeout(() => {
                location.reload();
            }, 4000);
        }
    }, 100);
}

function detenerSimulacion() {
    if (simulacionActiva) {
        clearInterval(simulacionInterval);
        simulacionActiva = false;
        document.getElementById('control-panel').style.display = 'none';
        document.getElementById('info-panel').innerHTML = '<i class="fas fa-stop-circle"></i> Simulación detenida';
        document.getElementById('info-panel').className = 'alert alert-warning mb-3';
        
        setTimeout(() => {
            location.reload();
        }, 2000);
    }
}

function actualizarEstado(envioId, estado) {
    fetch(`/envios/${envioId}/actualizar-estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ estado: estado })
    });
}
</script>
@endsection
