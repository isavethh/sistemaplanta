@extends('adminlte::page')
@section('title', 'Tracking de Envío')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-map-marker-alt text-info"></i> Tracking: {{ $envio->codigo }}</h1>
        <a href="{{ route('envios.show', $envio) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Envío
        </a>
    </div>
@endsection

@section('content')
@php
    $planta = \App\Models\Almacen::where('es_planta', true)->first();
    $almacenDestino = $envio->almacenDestino;
    
    // Coordenadas de origen (Planta)
    $origenLat = $planta->latitud ?? -17.7833;
    $origenLng = $planta->longitud ?? -63.1821;
    
    // Coordenadas de destino (Almacén)
    $destinoLat = $almacenDestino->latitud ?? -17.7750;
    $destinoLng = $almacenDestino->longitud ?? -63.1950;
@endphp

<div class="row">
    <div class="col-md-8">
        <!-- Mapa -->
        <div class="card shadow">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title text-white"><i class="fas fa-map"></i> Mapa de Ruta</h3>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Estado del Envío -->
        <div class="card shadow mb-3">
            <div class="card-header bg-primary">
                <h5 class="card-title text-white mb-0"><i class="fas fa-info-circle"></i> Estado Actual</h5>
            </div>
            <div class="card-body text-center">
                @if($envio->estado == 'pendiente')
                    <span class="badge badge-warning p-3" style="font-size: 1.2em;">
                        <i class="fas fa-clock"></i> PENDIENTE
                    </span>
                @elseif($envio->estado == 'aprobado')
                    <span class="badge badge-primary p-3" style="font-size: 1.2em;">
                        <i class="fas fa-check"></i> APROBADO
                    </span>
                @elseif($envio->estado == 'en_transito')
                    <span class="badge badge-info p-3" style="font-size: 1.2em;">
                        <i class="fas fa-truck"></i> EN TRÁNSITO
                    </span>
                @elseif($envio->estado == 'entregado')
                    <span class="badge badge-success p-3" style="font-size: 1.2em;">
                        <i class="fas fa-check-circle"></i> ENTREGADO
                    </span>
                @endif
            </div>
        </div>

        <!-- Origen -->
        <div class="card shadow mb-3">
            <div class="card-header bg-secondary">
                <h5 class="card-title text-white mb-0"><i class="fas fa-industry"></i> Origen</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ $planta->nombre ?? 'Planta Principal' }}</strong></p>
                <small class="text-muted">{{ $planta->direccion_completa ?? 'Sin dirección' }}</small>
            </div>
        </div>

        <!-- Destino -->
        <div class="card shadow mb-3">
            <div class="card-header bg-success">
                <h5 class="card-title text-white mb-0"><i class="fas fa-warehouse"></i> Destino</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ $almacenDestino->nombre ?? 'N/A' }}</strong></p>
                <small class="text-muted">{{ $almacenDestino->direccion_completa ?? 'Sin dirección' }}</small>
            </div>
        </div>

        <!-- Transportista -->
        <div class="card shadow mb-3">
            <div class="card-header bg-info">
                <h5 class="card-title text-white mb-0"><i class="fas fa-user-tie"></i> Transportista</h5>
            </div>
            <div class="card-body">
                @if($envio->asignacion && $envio->asignacion->transportista)
                    <p class="mb-1"><strong>{{ $envio->asignacion->transportista->name }}</strong></p>
                    <small class="text-muted">{{ $envio->asignacion->transportista->email }}</small>
                    @if($envio->asignacion->vehiculo)
                        <hr class="my-2">
                        <small><i class="fas fa-truck"></i> {{ $envio->asignacion->vehiculo->placa ?? 'N/A' }}</small>
                    @endif
                @else
                    <p class="text-muted mb-0"><i class="fas fa-user-slash"></i> Sin asignar</p>
                @endif
            </div>
        </div>

        <!-- Información de Ruta -->
        <div class="card shadow">
            <div class="card-header bg-warning">
                <h5 class="card-title text-dark mb-0"><i class="fas fa-route"></i> Info de Ruta</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><i class="fas fa-road"></i> <strong>Distancia:</strong> <span id="distancia">Calculando...</span></p>
                <p class="mb-0"><i class="fas fa-clock"></i> <strong>Tiempo est.:</strong> <span id="duracion">Calculando...</span></p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAIwhMeAvxLiKqRu3KMtwN1iT1jJBtioG0&libraries=geometry,places&callback=initMap" async defer></script>
<script>
let map, directionsService, directionsRenderer;

const origen = { lat: {{ $origenLat }}, lng: {{ $origenLng }} };
const destino = { lat: {{ $destinoLat }}, lng: {{ $destinoLng }} };

function initMap() {
    // Crear mapa
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: origen,
        mapTypeId: 'roadmap',
        styles: [
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            }
        ]
    });

    // Servicio de direcciones
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: true,
        polylineOptions: {
            strokeColor: '#4285F4',
            strokeWeight: 5
        }
    });

    // Calcular y mostrar ruta
    calcularRuta();
}

function calcularRuta() {
    const request = {
        origin: origen,
        destination: destino,
        travelMode: google.maps.TravelMode.DRIVING
    };

    directionsService.route(request, function(result, status) {
        if (status === google.maps.DirectionsStatus.OK) {
            directionsRenderer.setDirections(result);

            // Mostrar info de ruta
            const leg = result.routes[0].legs[0];
            document.getElementById('distancia').textContent = leg.distance.text;
            document.getElementById('duracion').textContent = leg.duration.text;

            // Agregar marcadores personalizados
            agregarMarcadores(leg);
        } else {
            console.error('Error al calcular ruta:', status);
            // Mostrar marcadores aunque falle la ruta
            agregarMarcadoresSinRuta();
        }
    });
}

function agregarMarcadores(leg) {
    // Marcador de origen (Planta)
    new google.maps.Marker({
        position: leg.start_location,
        map: map,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
            scaledSize: new google.maps.Size(40, 40)
        },
        title: '{{ $planta->nombre ?? "Planta" }}'
    });

    // Marcador de destino (Almacén)
    new google.maps.Marker({
        position: leg.end_location,
        map: map,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
            scaledSize: new google.maps.Size(40, 40)
        },
        title: '{{ $almacenDestino->nombre ?? "Almacén" }}'
    });

    @if($envio->estado == 'en_transito')
    // Simular posición del transportista (punto medio para demo)
    const midLat = (origen.lat + destino.lat) / 2;
    const midLng = (origen.lng + destino.lng) / 2;
    
    new google.maps.Marker({
        position: { lat: midLat, lng: midLng },
        map: map,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/truck.png',
            scaledSize: new google.maps.Size(40, 40)
        },
        title: 'Transportista en camino'
    });
    @endif
}

function agregarMarcadoresSinRuta() {
    new google.maps.Marker({
        position: origen,
        map: map,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
            scaledSize: new google.maps.Size(40, 40)
        },
        title: 'Origen: {{ $planta->nombre ?? "Planta" }}'
    });

    new google.maps.Marker({
        position: destino,
        map: map,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
            scaledSize: new google.maps.Size(40, 40)
        },
        title: 'Destino: {{ $almacenDestino->nombre ?? "Almacén" }}'
    });

    // Ajustar vista
    const bounds = new google.maps.LatLngBounds();
    bounds.extend(origen);
    bounds.extend(destino);
    map.fitBounds(bounds);

    document.getElementById('distancia').textContent = 'No disponible';
    document.getElementById('duracion').textContent = 'No disponible';
}
</script>
@endsection

@section('css')
<style>
    #map {
        border-radius: 0 0 10px 10px;
    }
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
</style>
@endsection
