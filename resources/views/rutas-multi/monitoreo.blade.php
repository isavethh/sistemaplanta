@extends('adminlte::page')

@section('title', 'Monitoreo de Rutas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tv text-info"></i> Monitoreo de Rutas en Tiempo Real</h1>
        <a href="{{ route('rutas-multi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Estadísticas -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $estadisticas['total_rutas'] ?? 0 }}</h3>
                <p>Total Rutas</p>
            </div>
            <div class="icon">
                <i class="fas fa-route"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $estadisticas['rutas_en_transito'] ?? 0 }}</h3>
                <p>En Tránsito</p>
            </div>
            <div class="icon">
                <i class="fas fa-truck"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $estadisticas['rutas_completadas'] ?? 0 }}</h3>
                <p>Completadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ number_format($estadisticas['promedio_tiempo_minutos'] ?? 0, 0) }} min</h3>
                <p>Tiempo Promedio</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Mapa de rutas activas -->
    <div class="col-lg-8">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marked-alt"></i> Rutas Activas
                </h3>
                <div class="card-tools">
                    <span id="estadoConexion" class="mr-2">
                        <span class="badge badge-secondary"><i class="fas fa-spinner fa-spin"></i> Conectando...</span>
                    </span>
                    <button type="button" class="btn btn-tool" id="btnRefrescar" title="Refrescar">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="mapaMonitoreo" style="height: 500px;"></div>
            </div>
        </div>
    </div>

    <!-- Lista de rutas activas -->
    <div class="col-lg-4">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck-loading"></i> Rutas en Tránsito
                </h3>
            </div>
            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="listaRutasActivas">
                    @forelse($rutasActivas as $ruta)
                        <li class="list-group-item ruta-item" data-ruta-id="{{ $ruta['id'] }}" data-total-paradas="{{ $ruta['total_paradas'] ?? 0 }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $ruta['codigo'] }}</strong>
                                    <span class="badge badge-info float-right">
                                        <i class="fas fa-truck fa-spin"></i>
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> {{ $ruta['transportista'] ?? 'N/A' }}
                                    </small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="progress" style="height: 10px;">
                                    @php
                                        $progreso = isset($ruta['total_paradas']) && $ruta['total_paradas'] > 0 
                                            ? round(($ruta['paradas_completadas'] ?? 0) / $ruta['total_paradas'] * 100) 
                                            : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $progreso }}%"></div>
                                </div>
                                <small class="text-muted">
                                    {{ $ruta['paradas_completadas'] ?? 0 }}/{{ $ruta['total_paradas'] ?? 0 }} entregas
                                </small>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('rutas-multi.show', $ruta['id']) }}" 
                                   class="btn btn-xs btn-outline-info">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-4">
                            <i class="fas fa-truck fa-2x mb-2"></i><br>
                            No hay rutas en tránsito
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Resumen del día -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i> Resumen del Día
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center border-right">
                        <h4 class="text-success">{{ $estadisticas['total_envios'] ?? 0 }}</h4>
                        <small>Envíos Totales</small>
                    </div>
                    <div class="col-6 text-center">
                        <h4 class="text-info">{{ number_format($estadisticas['total_peso'] ?? 0, 0) }} kg</h4>
                        <small>Peso Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .ruta-item {
        transition: background-color 0.3s;
    }
    .ruta-item:hover {
        background-color: #f8f9fa;
    }
    .truck-marker {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const PLANTA_COORDS = [-17.7833, -63.1821];
    const NODE_API_URL = '{{ env("NODE_API_URL", "http://10.26.10.192:8001/api") }}';
    const rutasActivas = @json($rutasActivas ?? []);
    let marcadoresRutas = {};
    let polilneasRutas = {};
    
    console.log('🗺️ Monitoreo iniciado, URL API:', NODE_API_URL);
    console.log('📍 Rutas activas:', rutasActivas.length);
    
    // Inicializar mapa
    const mapa = L.map('mapaMonitoreo').setView(PLANTA_COORDS, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapa);

    // Marcador de planta
    L.marker(PLANTA_COORDS, {
        icon: L.divIcon({
            className: '',
            html: '<div style="background: #28a745; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-industry"></i></div>',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        })
    }).addTo(mapa).bindPopup('<strong>🏭 Planta Central</strong>');

    // Función para actualizar posición de una ruta en el mapa
    function actualizarPosicionEnMapa(rutaId, latitud, longitud, info = {}) {
        const lat = parseFloat(latitud);
        const lng = parseFloat(longitud);
        
        if (isNaN(lat) || isNaN(lng)) return;

        const rutaData = rutasActivas.find(r => r.id == rutaId);
        const nombreRuta = rutaData ? rutaData.codigo : `Ruta ${rutaId}`;
        const transportista = rutaData ? rutaData.transportista : 'N/A';

        if (marcadoresRutas[rutaId]) {
            // Actualizar posición del marcador existente
            marcadoresRutas[rutaId].setLatLng([lat, lng]);
        } else {
            // Crear nuevo marcador
            marcadoresRutas[rutaId] = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'truck-marker',
                    html: '<div style="background: #ffc107; color: #000; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); animation: pulse 1.5s infinite;"><i class="fas fa-truck"></i></div>',
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                })
            }).addTo(mapa);
        }

        // Actualizar popup
        marcadoresRutas[rutaId].bindPopup(`
            <strong>🚚 ${nombreRuta}</strong><br>
            <small>Transportista: ${transportista}</small><br>
            <small>Parada actual: ${info.parada_actual_index || 0}</small><br>
            <small>Última actualización: ${new Date().toLocaleTimeString()}</small>
        `);

        // Añadir a la línea de trayecto
        if (!polilneasRutas[rutaId]) {
            polilneasRutas[rutaId] = L.polyline([], {
                color: '#ffc107',
                weight: 3,
                opacity: 0.7,
                dashArray: '10, 10'
            }).addTo(mapa);
        }
        polilneasRutas[rutaId].addLatLng([lat, lng]);
    }

    // Mostrar rutas activas iniciales que tienen ubicación guardada en la BD
    rutasActivas.forEach(ruta => {
        if (ruta.ultima_latitud && ruta.ultima_longitud) {
            actualizarPosicionEnMapa(
                ruta.id,
                ruta.ultima_latitud,
                ruta.ultima_longitud,
                { 
                    parada_actual_index: ruta.paradas_completadas || 0,
                    transportista: ruta.transportista 
                }
            );
        }
    });

    // Actualizar lista de rutas en el panel lateral
    function actualizarListaRutas(ubicaciones) {
        ubicaciones.forEach(ub => {
            const rutaItem = document.querySelector(`.ruta-item[data-ruta-id="${ub.ruta_id}"]`);
            if (rutaItem) {
                const progressBar = rutaItem.querySelector('.progress-bar');
                const progressText = rutaItem.querySelector('small:last-child');
                if (progressBar && ub.parada_actual_index !== undefined) {
                    const totalParadas = parseInt(rutaItem.dataset.totalParadas) || 2;
                    const progreso = Math.min(100, (ub.parada_actual_index / totalParadas) * 100);
                    progressBar.style.width = `${progreso}%`;
                }
            }
        });
    }

    // Consultar ubicaciones activas desde el backend Node.js
    async function consultarUbicaciones() {
        try {
            const url = `${NODE_API_URL}/rutas-entrega/ubicaciones-activas`;
            console.log('🔄 Consultando ubicaciones:', url);
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('📡 Respuesta del servidor:', data);
            
            if (data.success && data.ubicaciones) {
                console.log('📍 Ubicaciones activas:', data.ubicaciones.length);
                
                if (data.ubicaciones.length > 0) {
                    data.ubicaciones.forEach(ubicacion => {
                        console.log(`🚚 Ruta ${ubicacion.ruta_id}: lat=${ubicacion.latitud}, lng=${ubicacion.longitud}`);
                        actualizarPosicionEnMapa(
                            ubicacion.ruta_id,
                            ubicacion.latitud,
                            ubicacion.longitud,
                            ubicacion
                        );
                    });
                    
                    // Actualizar indicador de conexión - con ubicaciones
                    document.getElementById('estadoConexion').innerHTML = 
                        `<span class="badge badge-success"><i class="fas fa-satellite-dish"></i> ${data.ubicaciones.length} camión(es) en tiempo real</span>`;
                } else {
                    // Conectado pero sin ubicaciones activas
                    document.getElementById('estadoConexion').innerHTML = 
                        '<span class="badge badge-info"><i class="fas fa-check-circle"></i> Conectado - Sin rutas en movimiento</span>';
                }
            } else {
                document.getElementById('estadoConexion').innerHTML = 
                    '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Sin datos</span>';
            }
        } catch (error) {
            console.error('❌ Error consultando ubicaciones:', error);
            document.getElementById('estadoConexion').innerHTML = 
                `<span class="badge badge-danger" title="${error.message}"><i class="fas fa-times-circle"></i> Error de conexión</span>`;
        }
    }

    // Consultar ubicaciones cada 1.5 segundos para mayor fluidez
    consultarUbicaciones();
    setInterval(consultarUbicaciones, 1500);

    // Botón refrescar
    document.getElementById('btnRefrescar').addEventListener('click', function() {
        // Limpiar trayectos
        Object.values(polilneasRutas).forEach(p => p.setLatLngs([]));
        consultarUbicaciones();
    });
});
</script>
@stop
