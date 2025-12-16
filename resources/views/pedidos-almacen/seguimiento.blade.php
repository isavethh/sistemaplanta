@extends('adminlte::page')
@section('title', 'Seguimiento - ' . $pedido->codigo)
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-map-marked-alt"></i> Seguimiento: {{ $pedido->codigo }}</h1>
        <a href="{{ route('pedidos-almacen.show', $pedido->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@endsection

@section('content')
@if(!$pedido->envio)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> Este pedido aún no tiene un envío asociado.
    </div>
    <a href="{{ route('pedidos-almacen.show', $pedido->id) }}" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Volver al Pedido
    </a>
@else
@php
    $envio = $pedido->envio;
    $planta = \App\Models\Almacen::where('es_planta', true)->first();
    $almacenDestino = $pedido->almacen;
    
    // Coordenadas de origen (Planta)
    $origenLat = $planta->latitud ?? -17.7833;
    $origenLng = $planta->longitud ?? -63.1821;
    
    // Coordenadas de destino (Almacén)
    $destinoLat = $almacenDestino->latitud ?? -17.8146;
    $destinoLng = $almacenDestino->longitud ?? -63.1561;
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
                @elseif($envio->estado == 'asignado')
                    <span class="badge badge-info p-3" style="font-size: 1.2em;">
                        <i class="fas fa-user-tag"></i> ASIGNADO
                    </span>
                @elseif($envio->estado == 'en_transito')
                    <span class="badge badge-primary p-3" style="font-size: 1.2em;">
                        <i class="fas fa-truck"></i> EN TRÁNSITO
                    </span>
                @elseif($envio->estado == 'entregado')
                    <span class="badge badge-success p-3" style="font-size: 1.2em;">
                        <i class="fas fa-check-circle"></i> ENTREGADO
                    </span>
                @else
                    <span class="badge badge-secondary p-3" style="font-size: 1.2em;">
                        {{ strtoupper($envio->estado) }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Información del Envío -->
        <div class="card shadow mb-3">
            <div class="card-header bg-info">
                <h5 class="card-title text-white mb-0"><i class="fas fa-shipping-fast"></i> Información</h5>
            </div>
            <div class="card-body">
                <p><strong>Código Envío:</strong><br>{{ $envio->codigo }}</p>
                <p><strong>Fecha Estimada:</strong><br>{{ $envio->fecha_estimada_entrega ? $envio->fecha_estimada_entrega->format('d/m/Y') : 'N/A' }}</p>
                <p><strong>Hora Estimada:</strong><br>{{ $envio->hora_estimada ?? 'N/A' }}</p>
                @if($envio->asignacion && $envio->asignacion->vehiculo)
                    <p><strong>Transportista:</strong><br>{{ $envio->asignacion->vehiculo->transportista->name ?? 'N/A' }}</p>
                    <p><strong>Vehículo:</strong><br>{{ $envio->asignacion->vehiculo->placa ?? 'N/A' }}</p>
                @endif
            </div>
        </div>

        <!-- Origen y Destino -->
        <div class="card shadow">
            <div class="card-header bg-success">
                <h5 class="card-title text-white mb-0"><i class="fas fa-route"></i> Ruta</h5>
            </div>
            <div class="card-body">
                <p><strong><i class="fas fa-industry"></i> Origen:</strong><br>
                    {{ $planta->nombre ?? 'Planta Principal' }}<br>
                    <small class="text-muted">{{ $planta->direccion_completa ?? 'Santa Cruz, Bolivia' }}</small>
                </p>
                <hr>
                <p><strong><i class="fas fa-warehouse"></i> Destino:</strong><br>
                    {{ $almacenDestino->nombre }}<br>
                    <small class="text-muted">{{ $almacenDestino->direccion_completa ?? 'Santa Cruz, Bolivia' }}</small>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Historial -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-history"></i> Historial de Eventos</h3>
            </div>
            <div class="card-body">
                @if($envio->historial && $envio->historial->count() > 0)
                    <div class="timeline">
                        @foreach($envio->historial as $evento)
                        <div>
                            <i class="fas fa-circle bg-{{ $evento->tipo == 'creacion' ? 'blue' : ($evento->tipo == 'entrega' ? 'green' : 'info') }}"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $evento->fecha_hora->format('d/m/Y H:i') }}</span>
                                <h3 class="timeline-header">{{ $evento->descripcion }}</h3>
                                @if($evento->observaciones)
                                    <div class="timeline-body">{{ $evento->observaciones }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay historial disponible aún.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('js')
@if($pedido->envio)
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', '') }}&callback=initMap" async defer></script>
<script>
    function initMap() {
        const origen = { lat: {{ $origenLat }}, lng: {{ $origenLng }} };
        const destino = { lat: {{ $destinoLat }}, lng: {{ $destinoLng }} };
        
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 12,
            center: origen,
            mapTypeId: 'roadmap'
        });
        
        // Marcador origen
        new google.maps.Marker({
            position: origen,
            map: map,
            title: 'Origen (Planta)',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
            }
        });
        
        // Marcador destino
        new google.maps.Marker({
            position: destino,
            map: map,
            title: 'Destino ({{ $almacenDestino->nombre }})',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
            }
        });
        
        // Ruta
        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer();
        directionsRenderer.setMap(map);
        
        directionsService.route({
            origin: origen,
            destination: destino,
            travelMode: 'DRIVING'
        }, (response, status) => {
            if (status === 'OK') {
                directionsRenderer.setDirections(response);
            }
        });
    }
</script>
@endif
@endsection

@section('css')
<style>
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }
    .timeline-item {
        padding: 10px 0;
        border-left: 2px solid #dee2e6;
        margin-left: 20px;
        padding-left: 20px;
    }
    .timeline-item i {
        position: absolute;
        left: -10px;
        top: 10px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        text-align: center;
        line-height: 20px;
        color: white;
    }
</style>
@endsection

