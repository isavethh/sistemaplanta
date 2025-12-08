@extends('adminlte::page')

@section('title', 'Detalle de Ruta')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-route text-primary"></i> 
            Ruta {{ $ruta['codigo'] ?? 'Sin código' }}
        </h1>
        <div>
            <a href="{{ route('rutas-multi.resumen', $ruta['id']) }}" class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Ver Resumen
            </a>
            <a href="{{ route('rutas-multi.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Info general -->
    <div class="col-lg-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Estado:</dt>
                    <dd class="col-7">
                        @php
                            $estadoClase = match($ruta['estado'] ?? '') {
                                'pendiente' => 'warning',
                                'en_transito' => 'info',
                                'completada' => 'success',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-{{ $estadoClase }} badge-lg">
                            {{ ucfirst(str_replace('_', ' ', $ruta['estado'] ?? 'N/A')) }}
                        </span>
                    </dd>

                    <dt class="col-5">Fecha:</dt>
                    <dd class="col-7">{{ isset($ruta['fecha']) ? date('d/m/Y', strtotime($ruta['fecha'])) : '-' }}</dd>

                    <dt class="col-5">Transportista:</dt>
                    <dd class="col-7">{{ $ruta['transportista_nombre'] ?? 'N/A' }}</dd>

                    <dt class="col-5">Email:</dt>
                    <dd class="col-7"><small>{{ $ruta['transportista_email'] ?? '-' }}</small></dd>

                    <dt class="col-5">Vehículo:</dt>
                    <dd class="col-7">{{ $ruta['vehiculo_placa'] ?? 'N/A' }}</dd>

                    <dt class="col-5">Tipo:</dt>
                    <dd class="col-7">{{ $ruta['vehiculo_tipo'] ?? 'N/A' }}</dd>

                    <dt class="col-5">Total Envíos:</dt>
                    <dd class="col-7">
                        <span class="badge badge-primary">{{ $ruta['total_envios'] ?? 0 }}</span>
                    </dd>

                    <dt class="col-5">Peso Total:</dt>
                    <dd class="col-7">{{ number_format($ruta['total_peso'] ?? 0, 2) }} kg</dd>

                    @if(isset($ruta['hora_salida']))
                    <dt class="col-5">Hora Salida:</dt>
                    <dd class="col-7">{{ date('H:i', strtotime($ruta['hora_salida'])) }}</dd>
                    @endif

                    @if(isset($ruta['hora_fin']))
                    <dt class="col-5">Hora Fin:</dt>
                    <dd class="col-7">{{ date('H:i', strtotime($ruta['hora_fin'])) }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Progreso -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Progreso</h3>
            </div>
            <div class="card-body">
                @php
                    $paradas = $ruta['paradas'] ?? [];
                    $completadas = collect($paradas)->where('estado', 'entregado')->count();
                    $total = count($paradas);
                    $porcentaje = $total > 0 ? round(($completadas / $total) * 100) : 0;
                @endphp
                
                <div class="text-center mb-3">
                    <h2 class="mb-0">{{ $completadas }}/{{ $total }}</h2>
                    <small class="text-muted">Entregas completadas</small>
                </div>
                
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-success" style="width: {{ $porcentaje }}%">
                        {{ $porcentaje }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mapa -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Mapa de Ruta</h3>
            </div>
            <div class="card-body p-0">
                <div id="mapa" style="height: 400px;"></div>
            </div>
        </div>
    </div>

    <!-- Lista de paradas -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Paradas de la Ruta</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Orden</th>
                            <th>Envío</th>
                            <th>Destino</th>
                            <th>Estado</th>
                            <th>Hora Llegada</th>
                            <th>Hora Entrega</th>
                            <th>Receptor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ruta['paradas'] ?? [] as $parada)
                            <tr>
                                <td>
                                    <span class="badge badge-{{ $parada['estado'] == 'entregado' ? 'success' : 'primary' }} badge-lg">
                                        {{ $parada['orden'] }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $parada['envio_codigo'] ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    {{ $parada['destino_nombre'] ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $parada['destino_direccion'] ?? '' }}</small>
                                </td>
                                <td>
                                    @php
                                        $estadoParada = match($parada['estado'] ?? '') {
                                            'pendiente' => ['warning', 'Pendiente'],
                                            'en_destino' => ['info', 'En destino'],
                                            'entregado' => ['success', 'Entregado'],
                                            default => ['secondary', $parada['estado'] ?? 'N/A']
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $estadoParada[0] }}">
                                        {{ $estadoParada[1] }}
                                    </span>
                                </td>
                                <td>
                                    @if(isset($parada['hora_llegada']))
                                        {{ date('H:i', strtotime($parada['hora_llegada'])) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($parada['hora_entrega']))
                                        {{ date('H:i', strtotime($parada['hora_entrega'])) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($parada['nombre_receptor']))
                                        {{ $parada['nombre_receptor'] }}<br>
                                        <small class="text-muted">
                                            {{ $parada['cargo_receptor'] ?? '' }}
                                            @if(isset($parada['dni_receptor']))
                                                ({{ $parada['dni_receptor'] }})
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Sin paradas registradas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .badge-lg {
        font-size: 14px;
        padding: 8px 12px;
    }
    .progress {
        border-radius: 10px;
    }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const PLANTA_COORDS = [-17.7833, -63.1821];
    const paradas = @json($ruta['paradas'] ?? []);
    
    // Inicializar mapa
    const mapa = L.map('mapa').setView(PLANTA_COORDS, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapa);

    // Marcador de planta
    L.marker(PLANTA_COORDS, {
        icon: L.divIcon({
            className: '',
            html: '<div style="background: #28a745; color: white; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-industry"></i></div>',
            iconSize: [35, 35],
            iconAnchor: [17, 17]
        })
    }).addTo(mapa).bindPopup('<strong>🏭 Planta (Origen)</strong>');

    // Puntos para la línea
    const puntos = [PLANTA_COORDS];
    
    // Agregar marcadores de paradas
    paradas.forEach((parada, index) => {
        const lat = parseFloat(parada.destino_lat);
        const lng = parseFloat(parada.destino_lng);
        
        if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
            puntos.push([lat, lng]);
            
            const color = parada.estado === 'entregado' ? '#28a745' : 
                         parada.estado === 'en_destino' ? '#17a2b8' : '#ffc107';
            
            L.marker([lat, lng], {
                icon: L.divIcon({
                    className: '',
                    html: `<div style="background: ${color}; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-weight: bold;">${parada.orden}</div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                })
            }).addTo(mapa).bindPopup(`
                <strong>${parada.envio_codigo}</strong><br>
                ${parada.destino_nombre || 'Sin destino'}<br>
                <small>${parada.destino_direccion || ''}</small><br>
                <span class="badge" style="background: ${color}; color: white;">${parada.estado}</span>
            `);
        }
    });

    // Dibujar línea de ruta
    if (puntos.length > 1) {
        L.polyline(puntos, {
            color: '#007bff',
            weight: 4,
            opacity: 0.7
        }).addTo(mapa);

        // Ajustar vista para ver todos los puntos
        mapa.fitBounds(L.latLngBounds(puntos).pad(0.1));
    }
});
</script>
@stop
