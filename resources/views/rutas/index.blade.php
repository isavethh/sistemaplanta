@extends('adminlte::page')

@section('title', 'Rutas en Tiempo Real')

@section('content_header')
    <h1><i class="fas fa-route"></i> Rutas en Tiempo Real</h1>
@endsection

@section('content')
<div class="row">
    <!-- Envíos Activos -->
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-list"></i> Estado de Envíos</h3>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @php
                    // Solo mostrar envíos en tránsito (iniciados por transportista desde la app)
                    $enviosEnTransito = \App\Models\Envio::with(['almacenDestino', 'asignacion.vehiculo', 'asignacion.transportista.usuario'])
                        ->where('estado', 'en_transito')
                        ->get();
                    
                    // Envíos asignados/aceptados (esperando inicio del transportista)
                    $enviosEsperando = \App\Models\Envio::with(['almacenDestino', 'asignacion.vehiculo', 'asignacion.transportista.usuario'])
                        ->whereIn('estado', ['asignado', 'aceptado'])
                        ->get();
                @endphp

                <!-- Envíos en Tránsito -->
                <h6 class="text-info mt-3"><i class="fas fa-truck-moving"></i> En Tránsito ({{ $enviosEnTransito->count() }})</h6>
                @forelse($enviosEnTransito as $envio)
                    <div class="envio-card mb-3 p-3 border rounded bg-info text-white" 
                         data-envio-id="{{ $envio->id }}"
                         data-codigo="{{ $envio->codigo }}"
                         data-lat="{{ $envio->almacenDestino->latitud ?? -17.78 }}"
                         data-lng="{{ $envio->almacenDestino->longitud ?? -63.18 }}"
                         style="cursor: pointer;">
                        <h5 class="mb-2">
                            <span class="badge badge-warning">
                                🚚 EN RUTA
                            </span>
                        </h5>
                        <p class="mb-1"><strong>Código:</strong> {{ $envio->codigo }}</p>
                        <p class="mb-1"><strong>Destino:</strong> 📦 {{ $envio->almacenDestino->nombre ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Dirección:</strong> {{ $envio->almacenDestino->direccion_completa ?? 'N/A' }}</p>
                        @if($envio->asignacion && $envio->asignacion->transportista)
                            <p class="mb-1"><strong>Transportista:</strong> 
                                {{ $envio->asignacion->transportista->usuario->nombre ?? 'N/A' }} 
                                {{ $envio->asignacion->transportista->usuario->apellido ?? '' }}
                            </p>
                        @endif
                        @if($envio->asignacion && $envio->asignacion->vehiculo)
                            <p class="mb-1"><strong>Vehículo:</strong> {{ $envio->asignacion->vehiculo->placa }}</p>
                        @endif
                        @if($envio->fecha_inicio_transito)
                            <p class="mb-1"><small><strong>Iniciado:</strong> {{ \Carbon\Carbon::parse($envio->fecha_inicio_transito)->format('d/m/Y H:i') }}</small></p>
                        @endif
                        <button class="btn btn-sm btn-light mt-2" onclick="verRutaEnMapa({{ $envio->id }}, '{{ $envio->codigo }}', {{ $envio->almacenDestino->latitud ?? -17.78 }}, {{ $envio->almacenDestino->longitud ?? -63.18 }})">
                            <i class="fas fa-map-marked-alt"></i> Ver en Mapa
                        </button>
                    </div>
                @empty
                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle"></i> No hay envíos en tránsito
                    </div>
                @endforelse

                <!-- Envíos Esperando Inicio -->
                <h6 class="text-warning mt-3"><i class="fas fa-clock"></i> Esperando Inicio ({{ $enviosEsperando->count() }})</h6>
                @forelse($enviosEsperando as $envio)
                    <div class="envio-card mb-3 p-3 border rounded bg-light" 
                         style="opacity: 0.8;">
                        <h5 class="mb-2">
                            <span class="badge badge-{{ $envio->estado == 'aceptado' ? 'success' : 'secondary' }}">
                                {{ strtoupper($envio->estado) }}
                            </span>
                        </h5>
                        <p class="mb-1"><strong>Código:</strong> {{ $envio->codigo }}</p>
                        <p class="mb-1"><strong>Destino:</strong> 📦 {{ $envio->almacenDestino->nombre ?? 'N/A' }}</p>
                        @if($envio->asignacion && $envio->asignacion->transportista)
                            <p class="mb-1"><strong>Transportista:</strong> 
                                {{ $envio->asignacion->transportista->usuario->nombre ?? 'N/A' }} 
                                {{ $envio->asignacion->transportista->usuario->apellido ?? '' }}
                            </p>
                        @endif
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            {{ $envio->estado == 'asignado' ? 'Esperando aceptación del transportista' : 'Esperando que el transportista inicie la ruta desde la app' }}
                        </small>
                    </div>
                @empty
                    <div class="alert alert-secondary">
                        <i class="fas fa-check-circle"></i> No hay envíos esperando
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

// Función para ver la ruta en el mapa (solo visualización)
function verRutaEnMapa(envioId, codigo, lat, lng) {
    if (simulacionActiva) {
        alert('Ya hay una simulación en curso. Deténgala primero.');
        return;
    }
    
    // Usar coordenadas reales del almacén destino
    const destino = [lat, lng];
    
    // Limpiar mapa
    if (vehiculoMarker) map.removeLayer(vehiculoMarker);
    if (rutaPolyline) map.removeLayer(rutaPolyline);
    
    // Marcador de destino
    L.marker(destino, {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map).bindPopup('<b>Punto de Entrega</b>').openPopup();
    
    // Crear ruta
    rutaPolyline = L.polyline([PLANTA_COORDS, destino], {
        color: 'blue',
        weight: 3,
        opacity: 0.7,
        dashArray: '10, 5'
    }).addTo(map);
    
    // Ajustar vista al mapa
    map.fitBounds(rutaPolyline.getBounds(), {padding: [50, 50]});
    
    // Crear marcador del vehículo (empezar desde la planta)
    vehiculoMarker = L.marker(PLANTA_COORDS, {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map).bindPopup(`<b>Envío: ${codigo}</b><br>En tránsito...`).openPopup();
    
    // Mostrar panel de control
    document.getElementById('control-panel').style.display = 'block';
    document.getElementById('envio-codigo').textContent = codigo;
    document.getElementById('envio-estado').textContent = 'EN TRÁNSITO';
    document.getElementById('envio-estado').className = 'badge badge-info';
    
    // Actualizar info panel
    document.getElementById('info-panel').innerHTML = `<i class="fas fa-truck"></i> Simulando ruta del envío <strong>${codigo}</strong>...<br><small class="text-muted">Iniciado por el transportista desde la app móvil</small>`;
    document.getElementById('info-panel').className = 'alert alert-info mb-3';
    
    // Simular movimiento
    simulacionActiva = true;
    let paso = 0;
    const totalPasos = 50;
    
    simulacionInterval = setInterval(() => {
        paso++;
        const progreso = (paso / totalPasos) * 100;
        
        // Actualizar barra de progreso
        document.getElementById('progress-bar').style.width = progreso + '%';
        document.getElementById('progress-bar').textContent = Math.round(progreso) + '%';
        
        // Interpolar posición
        const latActual = PLANTA_COORDS[0] + (destino[0] - PLANTA_COORDS[0]) * (paso / totalPasos);
        const lngActual = PLANTA_COORDS[1] + (destino[1] - PLANTA_COORDS[1]) * (paso / totalPasos);
        
        vehiculoMarker.setLatLng([latActual, lngActual]);
        
        if (paso >= totalPasos) {
            clearInterval(simulacionInterval);
            simulacionActiva = false;
            
            // Envío completado
            vehiculoMarker.bindPopup(`<b>Envío: ${codigo}</b><br>¡Entregado!`).openPopup();
            document.getElementById('envio-estado').textContent = 'ENTREGADO';
            document.getElementById('envio-estado').className = 'badge badge-success';
            
            // Actualizar estado a "entregado" (esto solo es visual, la app lo marca oficialmente)
            document.getElementById('info-panel').innerHTML = `<i class="fas fa-check-circle"></i> Envío <strong>${codigo}</strong> completó su recorrido. <small>(Esperando confirmación de entrega del transportista)</small>`;
            document.getElementById('info-panel').className = 'alert alert-success mb-3';
            
            setTimeout(() => {
                location.reload();
            }, 3000);
        }
    }, 100);
}

function detenerSimulacion() {
    if (simulacionActiva) {
        clearInterval(simulacionInterval);
        simulacionActiva = false;
        document.getElementById('control-panel').style.display = 'none';
        alert('Simulación detenida');
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
