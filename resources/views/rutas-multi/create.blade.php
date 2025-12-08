@extends('adminlte::page')

@section('title', 'Crear Ruta Multi-Entrega')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-route text-primary"></i> Crear Ruta Multi-Entrega</h1>
        <a href="{{ route('rutas-multi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Panel izquierdo: Configuración de ruta -->
    <div class="col-lg-4">
        <form id="formCrearRuta" action="{{ route('rutas-multi.store') }}" method="POST">
            @csrf
            
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-truck mr-2"></i>Configurar Ruta</h3>
                </div>
                <div class="card-body">
                    <!-- Fecha -->
                    <div class="form-group">
                        <label for="fecha">
                            <i class="fas fa-calendar-alt text-info"></i> Fecha de Ruta <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="fecha" id="fecha" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Solo se mostrarán envíos con esta fecha
                        </small>
                    </div>

                    <!-- Transportista -->
                    <div class="form-group">
                        <label for="transportista_id">
                            <i class="fas fa-user-tie text-primary"></i> Transportista <span class="text-danger">*</span>
                        </label>
                        <select name="transportista_id" id="transportista_id" class="form-control select2" required>
                            <option value="">-- Seleccione transportista --</option>
                            @foreach($transportistas as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Vehículo -->
                    <div class="form-group">
                        <label for="vehiculo_id">
                            <i class="fas fa-truck text-success"></i> Vehículo <span class="text-danger">*</span>
                        </label>
                        <select name="vehiculo_id" id="vehiculo_id" class="form-control select2" required>
                            <option value="">-- Seleccione vehículo --</option>
                            @foreach($vehiculos as $v)
                                <option value="{{ $v->id }}" 
                                        data-capacidad-peso="{{ $v->capacidad_carga ?? 1000 }}"
                                        data-capacidad-volumen="{{ $v->capacidad_volumen ?? 10 }}"
                                        data-tipo="{{ $v->tipo_vehiculo ?? 'Estándar' }}"
                                        data-marca="{{ $v->marca ?? '' }}"
                                        data-modelo="{{ $v->modelo ?? '' }}">
                                    {{ $v->placa }} - {{ $v->marca ?? '' }} {{ $v->modelo ?? '' }} 
                                    | {{ $v->tipo_vehiculo ?? 'Estándar' }}
                                    ({{ number_format($v->capacidad_carga ?? 0, 0) }} kg / {{ $v->capacidad_volumen ?? 0 }} m³)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Indicador visual de carga del camión -->
                    <div class="card card-outline card-info" id="cardIndicadorCarga" style="display: none;">
                        <div class="card-header py-2">
                            <h3 class="card-title"><i class="fas fa-weight-hanging"></i> Capacidad del Vehículo</h3>
                        </div>
                        <div class="card-body text-center py-2">
                            <!-- Camión visual compacto -->
                            <div class="truck-container mb-2" style="transform: scale(0.8);">
                                <div class="truck-visual">
                                    <div class="truck-cabin">
                                        <div class="truck-window"></div>
                                    </div>
                                    <div class="truck-cargo">
                                        <div class="truck-cargo-fill" id="truckCargoFill">
                                            <div class="cargo-boxes" id="cargoBoxes"></div>
                                        </div>
                                        <div class="truck-cargo-text" id="truckCargoText">0%</div>
                                    </div>
                                    <div class="truck-wheels">
                                        <div class="wheel"></div>
                                        <div class="wheel"></div>
                                        <div class="wheel"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Barra de progreso -->
                            <div class="progress mb-2" style="height: 20px; border-radius: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     id="barraCapacidad"
                                     style="width: 0%; transition: all 0.5s ease;"
                                     aria-valuenow="0" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <span id="porcentajeCapacidad">0%</span>
                                </div>
                            </div>
                            
                            <!-- Información de capacidad -->
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="d-block text-success"><i class="fas fa-box"></i> Cargado</small>
                                    <strong id="pesoCargado" class="d-block">0 kg</strong>
                                </div>
                                <div class="col-6">
                                    <small class="d-block text-info"><i class="fas fa-arrow-up"></i> Disponible</small>
                                    <strong id="pesoDisponible" class="d-block">0 kg</strong>
                                </div>
                            </div>
                            
                            <!-- Info del tipo de vehículo -->
                            <div class="text-center mt-2 small" id="infoTipoVehiculo" style="display: none;">
                                <i class="fas fa-truck text-info"></i> <strong id="tipoVehiculoTexto"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Envíos seleccionados (oculto inicialmente) -->
            <div class="card card-success mt-3" id="cardEnviosSeleccionados" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes mr-2"></i>Envíos en la Ruta
                        <span class="badge badge-light ml-2" id="contadorEnvios">0</span>
                    </h3>
                </div>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="listaEnviosSeleccionados"></ul>
                </div>
                <div class="card-footer py-2">
                    <div class="d-flex justify-content-between small">
                        <span>
                            <strong>Peso:</strong> 
                            <span id="pesoTotal" class="badge badge-info">0 kg</span>
                        </span>
                        <span>
                            <strong>Cant:</strong> 
                            <span id="cantidadTotal" class="badge badge-success">0</span>
                        </span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-block btn-lg mt-3" id="btnCrearRuta" disabled>
                <i class="fas fa-check-circle mr-2"></i>Crear Ruta Multi-Entrega
            </button>
        </form>
    </div>

    <!-- Panel derecho: Mapa y lista de envíos -->
    <div class="col-lg-8">
        <!-- Mapa -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-map-marked-alt mr-2"></i>Mapa de Destinos
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" id="btnOptimizarRuta" title="Optimizar orden de entregas">
                        <i class="fas fa-magic"></i> Optimizar
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="mapa" style="height: 400px; width: 100%;"></div>
            </div>
        </div>

        <!-- Lista de envíos pendientes -->
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Envíos Pendientes de Asignación
                </h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" class="form-control" placeholder="Buscar..." id="buscarEnvio">
                        <div class="input-group-append">
                            <button class="btn btn-default" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 300px;">
                <table class="table table-hover table-striped table-sm">
                    <thead class="sticky-top bg-light">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Código</th>
                            <th>Destino</th>
                            <th>Peso</th>
                            <th>Fecha Est.</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEnviosPendientes">
                        @forelse($enviosPendientes as $envio)
                            <tr data-envio-id="{{ $envio->id }}" 
                                data-codigo="{{ $envio->codigo }}"
                                data-destino="{{ $envio->almacenDestino->nombre ?? 'Sin destino' }}"
                                data-direccion="{{ $envio->almacenDestino->direccion ?? '' }}"
                                data-lat="{{ $envio->almacenDestino->latitud ?? '' }}"
                                data-lng="{{ $envio->almacenDestino->longitud ?? '' }}"
                                data-peso="{{ $envio->productos->sum('total_peso') }}"
                                data-cantidad="{{ $envio->productos->sum('cantidad') }}"
                                data-fecha-entrega="{{ $envio->fecha_estimada_entrega ? \Carbon\Carbon::parse($envio->fecha_estimada_entrega)->format('d/m/Y') : '' }}"
                                class="envio-row checkbox-envio">
                                <td>
                                    <button type="button" class="btn btn-xs btn-success btn-agregar-envio" title="Agregar a ruta">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                                <td><strong>{{ $envio->codigo }}</strong></td>
                                <td>
                                    <small>{{ $envio->almacenDestino->nombre ?? 'Sin destino' }}</small>
                                </td>
                                <td>{{ number_format($envio->productos->sum('total_peso'), 2) }} kg</td>
                                <td>
                                    <span class="badge badge-info badge-fecha-entrega">
                                        <i class="fas fa-calendar"></i>
                                        {{ $envio->fecha_estimada_entrega ? \Carbon\Carbon::parse($envio->fecha_estimada_entrega)->format('d/m/Y') : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $envio->estado == 'pendiente' ? 'warning' : 'info' }}">
                                        {{ ucfirst($envio->estado) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    No hay envíos pendientes de asignación
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
    .envio-seleccionado {
        background-color: #d4edda !important;
    }
    .marker-label {
        background: #28a745;
        border-radius: 50%;
        color: white;
        font-weight: bold;
        padding: 5px 10px;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    .marker-label-selected {
        background: #007bff;
    }
    .drag-handle {
        cursor: move;
    }
    .sortable-ghost {
        opacity: 0.4;
        background-color: #c8ebfb;
    }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    /* ===== ESTILOS DEL CAMIÓN VISUAL ===== */
    .truck-container {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        padding: 10px;
    }
    
    .truck-visual {
        position: relative;
        width: 200px;
        height: 90px;
    }
    
    .truck-cabin {
        position: absolute;
        right: 0;
        bottom: 20px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        border-radius: 8px 8px 0 0;
        border: 2px solid #1a5276;
    }
    
    .truck-window {
        position: absolute;
        top: 8px;
        left: 8px;
        right: 8px;
        height: 20px;
        background: linear-gradient(135deg, #85c1e9, #aed6f1);
        border-radius: 4px;
        border: 1px solid #1a5276;
    }
    
    .truck-cargo {
        position: absolute;
        left: 0;
        bottom: 20px;
        width: 140px;
        height: 60px;
        background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
        border: 3px solid #7f8c8d;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .truck-cargo-fill {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 0%;
        background: linear-gradient(0deg, #27ae60, #2ecc71, #58d68d);
        transition: all 0.5s ease;
        display: flex;
        flex-wrap: wrap;
        align-content: flex-end;
        justify-content: center;
        padding: 2px;
    }
    
    .truck-cargo-fill.warning {
        background: linear-gradient(0deg, #f39c12, #f1c40f, #f4d03f);
    }
    
    .truck-cargo-fill.danger {
        background: linear-gradient(0deg, #c0392b, #e74c3c, #ec7063);
        animation: pulse-danger 1s infinite;
    }
    
    @keyframes pulse-danger {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .cargo-boxes {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        justify-content: center;
        align-content: flex-end;
    }
    
    .cargo-box {
        width: 12px;
        height: 12px;
        background: #8b4513;
        border: 1px solid #5d3a1a;
        border-radius: 2px;
        animation: boxAppear 0.3s ease;
    }
    
    @keyframes boxAppear {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    
    .truck-cargo-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 16px;
        font-weight: bold;
        color: #2c3e50;
        text-shadow: 1px 1px 2px rgba(255,255,255,0.8);
        z-index: 10;
    }
    
    .truck-wheels {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        display: flex;
        justify-content: space-between;
        padding: 0 15px;
    }
    
    .wheel {
        width: 25px;
        height: 25px;
        background: linear-gradient(135deg, #2c3e50, #34495e);
        border-radius: 50%;
        border: 3px solid #1a252f;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.5);
    }
    
    .info-box {
        min-height: auto !important;
    }
    
    .info-box .info-box-icon {
        width: 50px;
        height: 50px;
        line-height: 50px;
        font-size: 20px;
    }
    
    .info-box .info-box-content {
        padding: 5px 10px;
        margin-left: 50px;
    }
    
    .info-box .info-box-number {
        font-size: 18px;
    }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    // Variables globales
    let mapa, marcadores = {}, enviosSeleccionados = [];
    let capacidadVehiculo = 0;
    let capacidadVolumen = 0;
    let tipoVehiculoActual = '';
    let fechaSeleccionada = $('#fecha').val();
    const PLANTA_COORDS = [-17.7833, -63.1821]; // Coordenadas de la planta

    // Filtrar envíos por fecha al cargar la página
    filtrarEnviosPorFecha();

    // Evento al cambiar fecha
    $('#fecha').change(function() {
        fechaSeleccionada = $(this).val();
        
        // Limpiar envíos seleccionados
        enviosSeleccionados = [];
        actualizarListaSeleccionados();
        actualizarMarcadores();
        
        // Filtrar envíos
        filtrarEnviosPorFecha();
        
        // Ocultar alerta
        $('#alertaSeleccionarFecha').fadeOut();
    });

    // Función para filtrar envíos por fecha
    function filtrarEnviosPorFecha() {
        if (!fechaSeleccionada) {
            $('#tablaEnviosPendientes .envio-row').hide();
            return;
        }

        const fechaFormateada = new Date(fechaSeleccionada + 'T00:00:00').toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });

        let enviosMostrados = 0;

        $('#tablaEnviosPendientes .envio-row').each(function() {
            const fechaEnvio = $(this).data('fecha-entrega');
            
            if (fechaEnvio === fechaFormateada) {
                $(this).show();
                enviosMostrados++;
            } else {
                $(this).hide();
            }
        });

        // Mostrar mensaje si no hay envíos
        if (enviosMostrados === 0) {
            if ($('#tablaEnviosPendientes .mensaje-sin-envios').length === 0) {
                $('#tablaEnviosPendientes').append(`
                    <tr class="mensaje-sin-envios">
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                            No hay envíos pendientes para la fecha ${fechaFormateada}
                        </td>
                    </tr>
                `);
            }
        } else {
            $('#tablaEnviosPendientes .mensaje-sin-envios').remove();
        }

        // Actualizar marcadores en el mapa
        actualizarMarcadores();
    }

    // Evento al cambiar vehículo
    $('#vehiculo_id').change(function() {
        const $selected = $(this).find('option:selected');
        capacidadVehiculo = parseFloat($selected.data('capacidad-peso')) || 0;
        capacidadVolumen = parseFloat($selected.data('capacidad-volumen')) || 0;
        tipoVehiculoActual = $selected.data('tipo') || 'Estándar';
        const marca = $selected.data('marca') || '';
        const modelo = $selected.data('modelo') || '';
        
        if (capacidadVehiculo > 0) {
            $('#cardIndicadorCarga').show();
            $('#pesoDisponible').text(capacidadVehiculo.toLocaleString() + ' kg');
            
            // Mostrar tipo de vehículo
            $('#tipoVehiculoTexto').text(tipoVehiculoActual + ' - ' + marca + ' ' + modelo);
            $('#infoTipoVehiculo').show();
            
            actualizarIndicadorCarga();
        } else {
            $('#cardIndicadorCarga').hide();
        }
    });

    // Función para actualizar el indicador visual de carga
    function actualizarIndicadorCarga() {
        if (capacidadVehiculo <= 0) return;

        // Calcular peso total de envíos seleccionados
        let pesoTotal = 0;
        let volumenTotal = 0;
        let cantidadEnvios = enviosSeleccionados.length;
        
        enviosSeleccionados.forEach(id => {
            const $row = $(`#tablaEnviosPendientes tr[data-envio-id="${id}"]`);
            pesoTotal += parseFloat($row.data('peso')) || 0;
            volumenTotal += parseFloat($row.data('volumen')) || 0;
        });

        const porcentaje = Math.min(100, (pesoTotal / capacidadVehiculo) * 100);
        const pesoDisponible = Math.max(0, capacidadVehiculo - pesoTotal);
        const volumenDisponible = Math.max(0, capacidadVolumen - volumenTotal);

        // Actualizar barra de progreso
        const $barra = $('#barraCapacidad');
        $barra.css('width', porcentaje + '%');
        $barra.attr('aria-valuenow', porcentaje);
        $('#porcentajeCapacidad').text(porcentaje.toFixed(1) + '%');

        // Actualizar llenado del camión visual
        const $fill = $('#truckCargoFill');
        $fill.css('height', porcentaje + '%');
        $('#truckCargoText').text(porcentaje.toFixed(0) + '%');

        // Generar cajitas visuales
        const $boxes = $('#cargoBoxes');
        $boxes.empty();
        const numBoxes = Math.min(cantidadEnvios, 20); // Máximo 20 cajitas
        for (let i = 0; i < numBoxes; i++) {
            $boxes.append('<div class="cargo-box"></div>');
        }

        // Cambiar colores según nivel
        $barra.removeClass('bg-success bg-warning bg-danger');
        $fill.removeClass('warning danger');
        
        if (porcentaje >= 95) {
            $barra.addClass('bg-danger');
            $fill.addClass('danger');
        } else if (porcentaje >= 75) {
            $barra.addClass('bg-warning');
            $fill.addClass('warning');
        } else {
            $barra.addClass('bg-success');
        }

        // Actualizar textos
        $('#pesoCargado').text(pesoTotal.toLocaleString() + ' kg');
        $('#volumenCargado').text(volumenTotal.toFixed(1) + ' m³');
        $('#pesoDisponible').text(pesoDisponible.toLocaleString() + ' kg');
        $('#volumenDisponible').text(volumenDisponible.toFixed(1) + ' m³');

        // Mostrar alerta según estado
        const $alerta = $('#alertaCapacidad');
        const $mensaje = $('#mensajeCapacidad');
        
        if (pesoTotal > capacidadVehiculo) {
            $alerta.removeClass('alert-success alert-warning alert-info').addClass('alert-danger').show();
            $mensaje.html('<strong>⚠️ ¡SOBRECARGA!</strong> El peso excede la capacidad del vehículo por ' + 
                         (pesoTotal - capacidadVehiculo).toLocaleString() + ' kg');
        } else if (porcentaje >= 90) {
            $alerta.removeClass('alert-success alert-danger alert-info').addClass('alert-warning').show();
            $mensaje.html('<strong>⚡ ¡Casi lleno!</strong> Solo quedan ' + pesoDisponible.toLocaleString() + ' kg disponibles');
        } else if (porcentaje >= 50) {
            $alerta.removeClass('alert-danger alert-warning alert-info').addClass('alert-success').show();
            $mensaje.html('<strong>✅ Buen nivel de carga.</strong> Aún tienes espacio para ' + pesoDisponible.toLocaleString() + ' kg más');
        } else if (cantidadEnvios > 0) {
            $alerta.removeClass('alert-danger alert-warning alert-success').addClass('alert-info').show();
            $mensaje.html('<strong>📦 Espacio disponible:</strong> Puedes cargar hasta ' + pesoDisponible.toLocaleString() + ' kg adicionales');
        } else {
            $alerta.hide();
        }
    }

    // Inicializar mapa
    mapa = L.map('mapa').setView(PLANTA_COORDS, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapa);

    // Marcador de la planta (origen)
    L.marker(PLANTA_COORDS, {
        icon: L.divIcon({
            className: 'marker-label',
            html: '<i class="fas fa-industry"></i>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        })
    }).addTo(mapa).bindPopup('<strong>🏭 Planta (Origen)</strong>');

    // Agregar marcadores de envíos pendientes
    $('#tablaEnviosPendientes .envio-row').each(function() {
        const $row = $(this);
        const lat = parseFloat($row.data('lat'));
        const lng = parseFloat($row.data('lng'));
        const id = $row.data('envio-id');
        const codigo = $row.data('codigo');
        const destino = $row.data('destino');

        if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
            const marker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'marker-label',
                    html: '<i class="fas fa-box"></i>',
                    iconSize: [25, 25],
                    iconAnchor: [12, 12]
                })
            }).addTo(mapa);
            
            marker.bindPopup(`
                <strong>${codigo}</strong><br>
                ${destino}<br>
                <button class="btn btn-xs btn-success mt-1" onclick="agregarEnvio(${id})">
                    <i class="fas fa-plus"></i> Agregar a ruta
                </button>
            `);
            
            marcadores[id] = marker;
        }
    });

    // Función para agregar envío
    window.agregarEnvio = function(id) {
        const $row = $(`#tablaEnviosPendientes tr[data-envio-id="${id}"]`);
        if ($row.length && !enviosSeleccionados.includes(id)) {
            enviosSeleccionados.push(id);
            $row.addClass('envio-seleccionado');
            $row.find('.btn-agregar-envio').removeClass('btn-success').addClass('btn-danger')
                .html('<i class="fas fa-minus"></i>').attr('title', 'Quitar de ruta');
            
            // Actualizar lista de seleccionados
            actualizarListaSeleccionados();
            actualizarMarcadores();
            actualizarIndicadorCarga();
        }
    };

    // Función para quitar envío
    window.quitarEnvio = function(id) {
        const idx = enviosSeleccionados.indexOf(id);
        if (idx > -1) {
            enviosSeleccionados.splice(idx, 1);
            const $row = $(`#tablaEnviosPendientes tr[data-envio-id="${id}"]`);
            $row.removeClass('envio-seleccionado');
            $row.find('.btn-agregar-envio').removeClass('btn-danger').addClass('btn-success')
                .html('<i class="fas fa-plus"></i>').attr('title', 'Agregar a ruta');
            
            actualizarListaSeleccionados();
            actualizarMarcadores();
            actualizarIndicadorCarga();
        }
    };

    // Click en botón agregar/quitar de la tabla
    $(document).on('click', '.btn-agregar-envio', function() {
        const $row = $(this).closest('tr');
        const id = $row.data('envio-id');
        const fechaEntrega = $row.data('fecha-entrega');
        
        if (enviosSeleccionados.includes(id)) {
            quitarEnvio(id);
        } else {
            // Validar fecha antes de agregar
            if (enviosSeleccionados.length > 0) {
                const primeraFecha = $(`#tablaEnviosPendientes tr[data-envio-id="${enviosSeleccionados[0]}"]`).data('fecha-entrega');
                
                if (fechaEntrega !== primeraFecha) {
                    Swal.fire({
                        icon: 'error',
                        title: '❌ Fecha Incompatible',
                        html: `<p>No puedes agregar este envío porque tiene una fecha de entrega diferente.</p>
                               <div class="alert alert-info mt-3">
                                   <strong>Fecha de envíos ya seleccionados:</strong> ${primeraFecha}<br>
                                   <strong>Fecha de este envío:</strong> ${fechaEntrega}
                               </div>
                               <p class="text-warning"><i class="fas fa-info-circle"></i> Solo puedes agrupar envíos del mismo día.</p>`,
                        confirmButtonText: 'Entendido',
                        width: 600
                    });
                    return;
                }
            }
            
            agregarEnvio(id);
        }
    });

    // Actualizar lista de envíos seleccionados
    function actualizarListaSeleccionados() {
        const $lista = $('#listaEnviosSeleccionados');
        const $card = $('#cardEnviosSeleccionados');
        $lista.empty();

        if (enviosSeleccionados.length === 0) {
            $card.hide();
            $('#contadorEnvios').text('0');
            $('#pesoTotal').text('0 kg');
            $('#cantidadTotal').text('0');
            $('#btnCrearRuta').prop('disabled', true);
            return;
        }
        
        $card.show();

        let pesoTotal = 0, cantidadTotal = 0;

        enviosSeleccionados.forEach((id, index) => {
            const $row = $(`#tablaEnviosPendientes tr[data-envio-id="${id}"]`);
            const codigo = $row.data('codigo');
            const destino = $row.data('destino');
            const peso = parseFloat($row.data('peso')) || 0;
            const cantidad = parseInt($row.data('cantidad')) || 0;

            pesoTotal += peso;
            cantidadTotal += cantidad;

            $lista.append(`
                <li class="list-group-item d-flex justify-content-between align-items-center" data-envio-id="${id}">
                    <div class="drag-handle mr-2">
                        <i class="fas fa-grip-vertical text-muted"></i>
                    </div>
                    <span class="badge badge-primary mr-2">${index + 1}</span>
                    <div class="flex-grow-1">
                        <strong>${codigo}</strong><br>
                        <small class="text-muted">${destino}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarEnvio(${id})">
                        <i class="fas fa-times"></i>
                    </button>
                    <input type="hidden" name="envios_ids[]" value="${id}">
                </li>
            `);
        });

        $('#contadorEnvios').text(enviosSeleccionados.length);
        $('#pesoTotal').text(pesoTotal.toFixed(2) + ' kg');
        $('#cantidadTotal').text(cantidadTotal);
        $('#btnCrearRuta').prop('disabled', false);

        // Hacer la lista sortable
        new Sortable(document.getElementById('listaEnviosSeleccionados'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                // Reordenar el array de envíos según el nuevo orden
                enviosSeleccionados = [];
                $('#listaEnviosSeleccionados li').each(function() {
                    const id = $(this).data('envio-id');
                    if (id) enviosSeleccionados.push(id);
                });
                actualizarMarcadores();
            }
        });
    }

    // Actualizar marcadores en el mapa
    function actualizarMarcadores() {
        // Limpiar marcadores existentes
        Object.keys(marcadores).forEach(id => {
            mapa.removeLayer(marcadores[id]);
        });
        marcadores = {};
        
        // Agregar solo marcadores de envíos visibles
        $('#tablaEnviosPendientes .envio-row:visible').each(function() {
            const id = $(this).data('envio-id');
            const lat = parseFloat($(this).data('lat'));
            const lng = parseFloat($(this).data('lng'));
            const codigo = $(this).data('codigo');
            
            if (lat && lng) {
                const idx = enviosSeleccionados.indexOf(parseInt(id));
                
                const marker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: idx > -1 ? 'marker-label marker-label-selected' : 'marker-label',
                        html: idx > -1 ? `<strong>${idx + 1}</strong>` : '<i class="fas fa-box"></i>',
                        iconSize: idx > -1 ? [30, 30] : [25, 25],
                        iconAnchor: idx > -1 ? [15, 15] : [12, 12]
                    })
                }).addTo(mapa);
                
                marker.bindPopup(`<strong>${codigo}</strong>`);
                marker.on('click', function() {
                    if (enviosSeleccionados.includes(parseInt(id))) {
                        quitarEnvio(id);
                    } else {
                        // Validar fecha antes de agregar desde el mapa
                        const fechaEnvio = $(`#tablaEnviosPendientes tr[data-envio-id="${id}"]`).data('fecha-entrega');
                        if (enviosSeleccionados.length > 0) {
                            const primeraFecha = $(`#tablaEnviosPendientes tr[data-envio-id="${enviosSeleccionados[0]}"]`).data('fecha-entrega');
                            if (fechaEnvio !== primeraFecha) {
                                Swal.fire({
                                    icon: 'error',
                                    title: '❌ Fecha Incompatible',
                                    text: 'Este envío tiene una fecha diferente. Por favor selecciona la fecha correcta primero.',
                                    confirmButtonText: 'Entendido'
                                });
                                return;
                            }
                        }
                        agregarEnvio(id);
                    }
                });
                
                marcadores[id] = marker;
            }
        });

        // Dibujar líneas de ruta si hay más de un envío
        if (window.rutaLine) {
            mapa.removeLayer(window.rutaLine);
        }
        
        if (enviosSeleccionados.length > 0) {
            const puntos = [PLANTA_COORDS]; // Empezar desde la planta
            enviosSeleccionados.forEach(id => {
                const $row = $(`#tablaEnviosPendientes tr[data-envio-id="${id}"]`);
                const lat = parseFloat($row.data('lat'));
                const lng = parseFloat($row.data('lng'));
                if (lat && lng) puntos.push([lat, lng]);
            });
            
            window.rutaLine = L.polyline(puntos, {
                color: '#007bff',
                weight: 3,
                dashArray: '10, 10',
                opacity: 0.7
            }).addTo(mapa);
        }
    }

    // Búsqueda en la tabla (solo en envíos de la fecha seleccionada)
    $('#buscarEnvio').on('keyup', function() {
        const valor = $(this).val().toLowerCase();
        $('#tablaEnviosPendientes .envio-row').each(function() {
            const fechaEnvio = $(this).data('fecha-entrega');
            const fechaFormateada = new Date(fechaSeleccionada + 'T00:00:00').toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            
            // Solo mostrar si coincide con la fecha y con la búsqueda
            if (fechaEnvio === fechaFormateada) {
                const texto = $(this).text().toLowerCase();
                $(this).toggle(texto.includes(valor));
            } else {
                $(this).hide();
            }
        });
    });

    // Optimizar ruta (ordenar por distancia desde planta)
    $('#btnOptimizarRuta').click(function() {
        if (enviosSeleccionados.length < 2) {
            alert('Necesitas al menos 2 envíos para optimizar la ruta');
            return;
        }

        // Ordenar por distancia desde la planta
        enviosSeleccionados.sort((a, b) => {
            const rowA = $(`#tablaEnviosPendientes tr[data-envio-id="${a}"]`);
            const rowB = $(`#tablaEnviosPendientes tr[data-envio-id="${b}"]`);
            
            const distA = calcularDistancia(PLANTA_COORDS[0], PLANTA_COORDS[1], 
                parseFloat(rowA.data('lat')), parseFloat(rowA.data('lng')));
            const distB = calcularDistancia(PLANTA_COORDS[0], PLANTA_COORDS[1], 
                parseFloat(rowB.data('lat')), parseFloat(rowB.data('lng')));
            
            return distA - distB;
        });

        actualizarListaSeleccionados();
        actualizarMarcadores();
        actualizarIndicadorCarga();
        
        toastr.success('Ruta optimizada por distancia desde la planta');
    });

    // Calcular distancia entre dos puntos
    function calcularDistancia(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radio de la tierra en km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Validación de misma fecha al seleccionar envíos
    $(document).on('change', '.checkbox-envio', function() {
        let fechasSeleccionadas = new Set();
        let enviosPorFecha = {};
        
        $('.checkbox-envio:checked').each(function() {
            let fecha = $(this).data('fecha-entrega');
            let codigo = $(this).data('codigo');
            if (fecha) {
                fechasSeleccionadas.add(fecha);
                if (!enviosPorFecha[fecha]) {
                    enviosPorFecha[fecha] = [];
                }
                enviosPorFecha[fecha].push(codigo);
            }
        });
        
        // Si hay más de una fecha diferente
        if (fechasSeleccionadas.size > 1) {
            // Desmarcar el último checkbox
            $(this).prop('checked', false);
            
            // Construir mensaje detallado
            let mensajeFechas = '<div class="text-left">';
            for (let fecha in enviosPorFecha) {
                mensajeFechas += `<strong>${fecha}:</strong> ${enviosPorFecha[fecha].join(', ')}<br>`;
            }
            mensajeFechas += '</div>';
            
            // Mostrar alerta con SweetAlert2
            Swal.fire({
                icon: 'error',
                title: '❌ Fechas Incompatibles',
                html: '<p>No puedes agrupar envíos con diferentes fechas de entrega.</p>' +
                      '<div class="alert alert-warning mt-3">' +
                      '<i class="fas fa-exclamation-triangle"></i> ' +
                      '<strong>Solo puedes seleccionar envíos del mismo día.</strong>' +
                      '</div>' +
                      '<p class="mt-3"><strong>Envíos seleccionados por fecha:</strong></p>' +
                      mensajeFechas,
                confirmButtonText: 'Entendido',
                width: 600
            });
            
            return false;
        }
    });

    // Validar antes de enviar
    $('#formCrearRuta').submit(function(e) {
        if (enviosSeleccionados.length === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos un envío para crear la ruta');
            return false;
        }
        
        if (!$('#transportista_id').val()) {
            e.preventDefault();
            alert('Debe seleccionar un transportista');
            return false;
        }
        
        if (!$('#vehiculo_id').val()) {
            e.preventDefault();
            alert('Debe seleccionar un vehículo');
            return false;
        }

        $(this).find('button[type="submit"]').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin"></i> Creando ruta...'
        );
    });
});
</script>
@stop
