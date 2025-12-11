@extends('adminlte::page')

@section('title', 'Asignación Múltiple')

@section('content_header')
    <h1><i class="fas fa-truck-loading text-warning"></i> Asignación Múltiple de Envíos</h1>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {!! nl2br(e(session('success'))) !!}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> {!! nl2br(e(session('error'))) !!}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- SELECTOR DE FECHA -->
<div class="card shadow-lg mb-4">
    <div class="card-header bg-gradient-info">
        <h3 class="card-title text-white"><i class="fas fa-calendar-day"></i> Seleccionar Fecha de Entrega</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('asignacion-multiple.index') }}" class="form-inline">
            <div class="form-group mr-3">
                <label for="fecha" class="mr-2"><strong>Fecha:</strong></label>
                <input type="date" name="fecha" id="fecha" class="form-control" 
                       value="{{ $fechaSeleccionada }}" 
                       onchange="this.form.submit()">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar Envíos
            </button>
            
            @if($fechasDisponibles->count() > 0)
            <div class="ml-auto">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Fechas con envíos pendientes:
                    @foreach($fechasDisponibles->take(5) as $fd)
                        <a href="?fecha={{ $fd->fecha }}" class="badge badge-secondary ml-1">
                            {{ Carbon\Carbon::parse($fd->fecha)->format('d/m') }} ({{ $fd->total }})
                        </a>
                    @endforeach
                </small>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- INFORMACIÓN INICIAL -->
<div class="card shadow-lg border-info mb-4">
    <div class="card-header bg-info">
        <h3 class="card-title text-white"><i class="fas fa-info-circle"></i> Información sobre Asignación Múltiple</h3>
    </div>
    <div class="card-body">
        <p class="mb-3">La asignación múltiple te permite agrupar varios envíos del mismo día y asignarlos a un transportista con un vehículo específico. Esto optimiza las rutas y reduce costos de transporte.</p>
        <ul class="mb-0">
            <li><strong>Solo se pueden asignar envíos del mismo día:</strong> Todos los envíos deben tener la misma fecha de entrega estimada.</li>
            <li><strong>El peso total no debe exceder la capacidad del vehículo:</strong> El sistema validará automáticamente y te mostrará una alerta si hay sobrepeso.</li>
            <li><strong>El transportista verá estos envíos en su app móvil:</strong> Una vez asignados, aparecerán como una ruta multi-entrega.</li>
            <li><strong>Podrá hacer su checklist de carga y ver todas las paradas:</strong> El transportista tendrá acceso a checklists y podrá ver todas las paradas en el mapa.</li>
        </ul>
    </div>
</div>

<!-- PANEL PRINCIPAL -->
<div class="row" id="panel-principal">
    <!-- COLUMNA IZQUIERDA: LISTA DE ENVÍOS -->
    <div class="col-lg-7 d-flex">
        <div class="card shadow-lg w-100" id="card-envios">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white">
                    <i class="fas fa-list-check"></i> 
                    Envíos para {{ Carbon\Carbon::parse($fechaSeleccionada)->format('d/m/Y') }}
                    <span class="badge badge-light ml-2">{{ $enviosPendientes->count() }}</span>
                </h3>
            </div>
            <div class="card-body">
                @if($enviosPendientes->isEmpty())
                    <div class="alert alert-warning text-center py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <h5>No hay envíos pendientes para esta fecha</h5>
                        <p class="text-muted">Selecciona otra fecha o crea nuevos envíos.</p>
                    </div>
                @else
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSelectAll">
                            <i class="fas fa-check-square"></i> Seleccionar Todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                            <i class="fas fa-square"></i> Deseleccionar Todos
                        </button>
                        <span class="ml-3 text-muted">
                            <strong id="countSeleccionados">0</strong> envío(s) seleccionado(s)
                        </span>
                    </div>
                    
                    <div class="lista-envios">
                        @foreach($enviosPendientes as $envio)
                        <div class="envio-card" data-envio-id="{{ $envio->id }}">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input checkbox-envio" 
                                       id="envio-{{ $envio->id }}"
                                       value="{{ $envio->id }}"
                                       data-peso="{{ $envio->total_peso }}"
                                       data-codigo="{{ $envio->codigo }}"
                                       data-destino="{{ $envio->almacenDestino->nombre ?? 'N/A' }}"
                                       data-hora="{{ $envio->hora_estimada }}"
                                       data-productos="{{ $envio->productos->count() }}"
                                       onchange="actualizarSeleccion()">
                                <label class="custom-control-label w-100" for="envio-{{ $envio->id }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-primary">{{ $envio->codigo }}</strong>
                                            @if($envio->hora_estimada)
                                                <span class="badge badge-info ml-2">
                                                    <i class="fas fa-clock"></i> {{ $envio->hora_estimada }}
                                                </span>
                                            @endif
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-warehouse"></i> {{ $envio->almacenDestino->nombre ?? 'N/A' }}
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-boxes"></i> {{ $envio->productos->count() }} prod.
                                            </span>
                                            <br>
                                            <span class="badge badge-dark mt-1">
                                                <i class="fas fa-weight"></i> {{ number_format($envio->total_peso, 2) }} kg
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- COLUMNA DERECHA: ASIGNACIÓN Y ANIMACIÓN -->
    <div class="col-lg-5 d-flex flex-column">
        <form action="{{ route('asignacion-multiple.asignar') }}" method="POST" id="formAsignacionMultiple" class="d-flex flex-column h-100">
            @csrf
            <div id="envios-ids-container"></div>
            
            <!-- SELECCIÓN DE TRANSPORTISTA Y VEHÍCULO -->
            <div class="card shadow-lg mb-3" id="card-asignar">
                <div class="card-header bg-gradient-success">
                    <h3 class="card-title text-white"><i class="fas fa-user-cog"></i> Asignar a</h3>
                </div>
                <div class="card-body">
                    <!-- Transportista -->
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Transportista <span class="text-danger">*</span></label>
                        <select name="transportista_id" id="select-transportista" class="form-control" required>
                            <option value="">Seleccione transportista...</option>
                            @foreach($transportistas as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->name }}
                                    @if($t->licencia)
                                        - Lic: {{ $t->licencia }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Vehículo -->
                    <div class="form-group mb-0">
                        <label><i class="fas fa-truck"></i> Vehículo <span class="text-danger">*</span></label>
                        <select name="vehiculo_id" id="select-vehiculo" class="form-control" required onchange="actualizarAnimacionCamion()">
                            <option value="">Seleccione vehículo...</option>
                            @foreach($vehiculos as $v)
                                <option value="{{ $v->id }}"
                                        data-capacidad="{{ $v->capacidad_carga ?? 1000 }}"
                                        data-tipo="{{ $v->tipoTransporte->nombre ?? 'Camión' }}"
                                        data-placa="{{ $v->placa }}"
                                        data-marca="{{ $v->marca }} {{ $v->modelo }}">
                                    🚛 {{ $v->placa }} - {{ $v->marca }} {{ $v->modelo }}
                                    (Cap: {{ number_format($v->capacidad_carga ?? 1000) }} kg)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- ANIMACIÓN DEL CAMIÓN -->
            <div class="card shadow-lg mb-3" id="card-animacion" style="display: none;">
                <div class="card-header bg-gradient-warning">
                    <h3 class="card-title"><i class="fas fa-truck-loading"></i> Simulación de Carga</h3>
                </div>
                <div class="card-body p-4">
                    <!-- Información del vehículo -->
                    <div class="info-vehiculo-box mb-3">
                        <h5 class="text-center mb-2">
                            <i class="fas fa-truck"></i> <span id="info-vehiculo-nombre">-</span>
                        </h5>
                        <div class="text-center">
                            <span class="badge badge-secondary" id="info-vehiculo-placa">-</span>
                        </div>
                    </div>
                    
                    <!-- Camión animado -->
                    <div class="camion-animado-wrapper">
                        <div class="camion-body">
                            <!-- Cabina -->
                            <div class="camion-cabina">
                                <i class="fas fa-truck fa-3x"></i>
                            </div>
                            
                            <!-- Container de carga -->
                            <div class="camion-container-box">
                                <div class="camion-carga-fill" id="carga-fill"></div>
                                <div class="camion-items-grid" id="items-grid"></div>
                            </div>
                        </div>
                        
                        <!-- Ruedas animadas -->
                        <div class="camion-ruedas">
                            <div class="rueda rueda-1"></div>
                            <div class="rueda rueda-2"></div>
                            <div class="rueda rueda-3"></div>
                        </div>
                    </div>
                    
                    <!-- Estadísticas de carga -->
                    <div class="estadisticas-carga mt-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-box">
                                    <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                    <h4 class="mb-0" id="stat-envios">0</h4>
                                    <small>Envíos</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-box">
                                    <i class="fas fa-weight-hanging fa-2x text-warning mb-2"></i>
                                    <h4 class="mb-0" id="stat-peso">0</h4>
                                    <small>Peso (kg)</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-box">
                                    <i class="fas fa-tachometer-alt fa-2x text-success mb-2"></i>
                                    <h4 class="mb-0" id="stat-capacidad">0</h4>
                                    <small>Capacidad (kg)</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress bar de utilización -->
                        <div class="mt-3">
                            <label class="mb-2"><strong>Utilización del Vehículo:</strong></label>
                            <div class="progress" style="height: 35px; border: 2px solid #333;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     id="progress-utilizacion" 
                                     role="progressbar" 
                                     style="width: 0%;">
                                    <strong id="progress-text">0%</strong>
                                </div>
                            </div>
                            <div class="mt-2 text-center">
                                <small id="mensaje-validacion" class="text-muted">
                                    Selecciona envíos y un vehículo
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón de asignar -->
                    <button type="submit" 
                            class="btn btn-success btn-lg btn-block mt-4" 
                            id="btnAsignar" 
                            disabled>
                        <i class="fas fa-check-circle"></i> Asignar {{ Carbon\Carbon::parse($fechaSeleccionada)->format('d/m/Y') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('css')
<style>
/* ============================================
   PANEL PRINCIPAL - IGUALAR ALTURAS
============================================ */
#panel-principal {
    display: flex;
    align-items: stretch;
}

#panel-principal .col-lg-7,
#panel-principal .col-lg-5 {
    display: flex;
    flex-direction: column;
}

#card-envios,
#card-asignar {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 100%;
}

#card-envios .card-body,
#card-asignar .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}

#card-envios .lista-envios {
    flex: 1;
    min-height: 0;
}

#formAsignacionMultiple {
    display: flex;
    flex-direction: column;
    height: 100%;
}

#formAsignacionMultiple > .card {
    flex-shrink: 0;
}

#formAsignacionMultiple #card-animacion {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

#formAsignacionMultiple #card-animacion .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow-y: auto;
}

/* ============================================
   LISTA DE ENVÍOS
============================================ */
.envio-card {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    min-height: 120px;
}

.envio-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
    transform: translateX(5px);
}

.envio-card.selected {
    background: #e3f2fd;
    border-color: #2196F3;
    border-width: 3px;
}

.custom-control-label {
    cursor: pointer;
    user-select: none;
    display: flex;
    flex-direction: column;
    height: 100%;
    width: 100%;
    min-height: 100%;
}

.custom-control-label > div {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 100%;
    height: 100%;
}

.lista-envios {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 10px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
    align-items: start;
}

.lista-envios::-webkit-scrollbar {
    width: 8px;
}

.lista-envios::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.lista-envios::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.lista-envios::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* ============================================
   CAMIÓN ANIMADO
============================================ */
.camion-animado-wrapper {
    background: linear-gradient(to bottom, #87CEEB 0%, #f5f5f5 70%);
    border-radius: 15px;
    padding: 30px 20px 20px 20px;
    position: relative;
    overflow: hidden;
}

/* Carretera */
.camion-animado-wrapper::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: repeating-linear-gradient(
        90deg,
        #333 0px,
        #333 30px,
        #fff 30px,
        #fff 60px
    );
}

.camion-body {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    margin-bottom: 25px;
}

.camion-cabina {
    color: #1976D2;
    margin-right: -8px;
    z-index: 2;
    position: relative;
    animation: bounce-truck 2s ease-in-out infinite;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
}

.camion-container-box {
    width: 250px;
    height: 100px;
    background: linear-gradient(135deg, #fff 0%, #f5f5f5 100%);
    border: 4px solid #333;
    border-radius: 8px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 6px 12px rgba(0,0,0,0.3);
}

.camion-carga-fill {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 100%;
    width: 0%;
    background: linear-gradient(to top, #4CAF50, #81C784);
    transition: width 0.8s ease-in-out, background 0.5s;
    border-right: 3px solid #2E7D32;
}

.camion-carga-fill.warning {
    background: linear-gradient(to top, #FFC107, #FFD54F);
    border-right-color: #F57C00;
}

.camion-carga-fill.danger {
    background: linear-gradient(to top, #F44336, #E57373);
    border-right-color: #C62828;
    animation: shake 0.5s ease-in-out;
}

.camion-items-grid {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-wrap: wrap;
    align-content: flex-end;
    padding: 5px;
    gap: 4px;
    z-index: 1;
}

.item-caja {
    width: 20px;
    height: 20px;
    background: #FF9800;
    border: 2px solid #E65100;
    border-radius: 3px;
    position: relative;
    animation: dropIn 0.6s ease-out forwards;
    opacity: 0;
    transform: translateY(-50px) scale(0.5);
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.item-caja::before {
    content: '📦';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 10px;
}

@keyframes dropIn {
    0% {
        opacity: 0;
        transform: translateY(-50px) scale(0.5) rotate(-10deg);
    }
    60% {
        transform: translateY(5px) scale(1.1) rotate(5deg);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1) rotate(0deg);
    }
}

/* Ruedas animadas */
.camion-ruedas {
    display: flex;
    justify-content: space-around;
    width: 300px;
    margin: 0 auto;
    position: relative;
    top: -15px;
}

.rueda {
    width: 25px;
    height: 25px;
    background: #333;
    border: 4px solid #666;
    border-radius: 50%;
    position: relative;
}

.rueda.activa {
    animation: rotar 1s linear infinite;
}

.rueda::before {
    content: '';
    position: absolute;
    width: 8px;
    height: 8px;
    background: #999;
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

@keyframes rotar {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes bounce-truck {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Estadísticas */
.estadisticas-carga {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
}

.stat-box {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.progress {
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}

.progress-bar {
    font-size: 16px;
    font-weight: bold;
    transition: width 0.8s ease-in-out, background-color 0.5s;
}

/* Info adicional */
.info-vehiculo-box {
    background: #f0f0f0;
    padding: 10px;
    border-radius: 8px;
    border: 2px dashed #666;
}

/* Alerta de capacidad */
.alert-capacidad {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
@endsection

@section('js')
<script>
console.log('✅ Módulo de Asignación Múltiple cargado');

let enviosSeleccionados = [];
let pesoTotal = 0;
let capacidadVehiculo = 0;

// Seleccionar todos
$('#btnSelectAll').on('click', function() {
    $('.checkbox-envio').prop('checked', true).trigger('change');
});

// Deseleccionar todos
$('#btnDeselectAll').on('click', function() {
    $('.checkbox-envio').prop('checked', false).trigger('change');
});

// Actualizar selección
function actualizarSeleccion() {
    enviosSeleccionados = [];
    pesoTotal = 0;
    
    $('.checkbox-envio:checked').each(function() {
        const checkbox = $(this);
        const card = checkbox.closest('.envio-card');
        
        enviosSeleccionados.push({
            id: checkbox.val(),
            codigo: checkbox.data('codigo'),
            peso: parseFloat(checkbox.data('peso')) || 0,
            destino: checkbox.data('destino'),
            hora: checkbox.data('hora')
        });
        
        pesoTotal += parseFloat(checkbox.data('peso')) || 0;
        
        // Marcar visualmente
        card.addClass('selected');
    });
    
    // Desmarcar no seleccionados
    $('.checkbox-envio:not(:checked)').each(function() {
        $(this).closest('.envio-card').removeClass('selected');
    });
    
    // Actualizar contador
    $('#countSeleccionados').text(enviosSeleccionados.length);
    
    // Actualizar inputs hidden
    $('#envios-ids-container').html('');
    enviosSeleccionados.forEach(function(envio) {
        $('#envios-ids-container').append(
            '<input type="hidden" name="envios_ids[]" value="' + envio.id + '">'
        );
    });
    
    // Actualizar animación
    actualizarAnimacionCamion();
    
    // Igualar alturas después de actualizar
    setTimeout(function() {
        igualarAlturasCardsPrincipales();
        igualarAlturasCards();
    }, 100);
};

// Actualizar animación del camión
function actualizarAnimacionCamion() {
    const selectVehiculo = document.getElementById('select-vehiculo');
    const option = selectVehiculo.options[selectVehiculo.selectedIndex];
    
    if (!option.value || enviosSeleccionados.length === 0) {
        $('#card-animacion').hide();
        $('#btnAsignar').prop('disabled', true);
        return;
    }
    
    // Mostrar card de animación
    $('#card-animacion').slideDown(300);
    
    // Obtener datos del vehículo
    capacidadVehiculo = parseFloat(option.dataset.capacidad) || 1000;
    const tipo = option.dataset.tipo || 'Camión';
    const placa = option.dataset.placa || '';
    const marca = option.dataset.marca || '';
    
    // Actualizar info del vehículo
    $('#info-vehiculo-nombre').text(marca);
    $('#info-vehiculo-placa').text(placa);
    
    // Calcular porcentaje
    const porcentaje = Math.min((pesoTotal / capacidadVehiculo) * 100, 120);
    const porcentajeLimpio = Math.min(porcentaje, 100);
    
    // Actualizar estadísticas
    $('#stat-envios').text(enviosSeleccionados.length);
    $('#stat-peso').text(pesoTotal.toFixed(2));
    $('#stat-capacidad').text(capacidadVehiculo.toFixed(0));
    
    // Actualizar progress bar
    const progressBar = $('#progress-utilizacion');
    const cargaFill = $('#carga-fill');
    
    progressBar.css('width', porcentajeLimpio + '%');
    cargaFill.css('width', porcentajeLimpio + '%');
    $('#progress-text').text(porcentaje.toFixed(1) + '%');
    
    // Cambiar colores según capacidad
    progressBar.removeClass('bg-success bg-warning bg-danger');
    cargaFill.removeClass('warning danger');
    
    let mensaje = '';
    let puedeAsignar = false;
    
    if (porcentaje <= 60) {
        progressBar.addClass('bg-success');
        mensaje = '✅ Carga óptima. Vehículo con buena capacidad disponible.';
        puedeAsignar = true;
    } else if (porcentaje <= 90) {
        progressBar.addClass('bg-warning');
        cargaFill.addClass('warning');
        mensaje = '⚠️ Carga alta pero aceptable. Verifica la distribución.';
        puedeAsignar = true;
    } else if (porcentaje <= 100) {
        progressBar.addClass('bg-warning');
        cargaFill.addClass('warning');
        mensaje = '⚠️ Capacidad casi al máximo. Conducir con precaución.';
        puedeAsignar = true;
    } else {
        progressBar.addClass('bg-danger');
        cargaFill.addClass('danger');
        const exceso = (pesoTotal - capacidadVehiculo).toFixed(2);
        mensaje = `❌ SOBREPESO: ${exceso} kg de exceso. NO SE PUEDE REALIZAR EL ENVÍO.`;
        puedeAsignar = false;
    }
    
    $('#mensaje-validacion').html(mensaje);
    $('#btnAsignar').prop('disabled', !puedeAsignar);
    
    // Animar ruedas si está OK
    if (puedeAsignar) {
        $('.rueda').addClass('activa');
    } else {
        $('.rueda').removeClass('activa');
    }
    
    // Renderizar items (cajas) en el camión
    renderizarCajas();
}

// Renderizar cajas en el camión
function renderizarCajas() {
    const itemsGrid = document.getElementById('items-grid');
    itemsGrid.innerHTML = '';
    
    const numItems = Math.min(enviosSeleccionados.length * 3, 24); // Máximo 24 cajas visuales
    
    for (let i = 0; i < numItems; i++) {
        const item = document.createElement('div');
        item.className = 'item-caja';
        item.style.animationDelay = (i * 0.1) + 's';
        itemsGrid.appendChild(item);
    }
}

// Igualar alturas de los cards principales
function igualarAlturasCardsPrincipales() {
    const cardEnvios = document.getElementById('card-envios');
    const cardAsignar = document.getElementById('card-asignar');
    
    if (!cardEnvios || !cardAsignar) return;
    
    // Resetear alturas
    cardEnvios.style.height = 'auto';
    cardAsignar.style.height = 'auto';
    
    // Obtener alturas
    const alturaEnvios = cardEnvios.offsetHeight;
    const alturaAsignar = cardAsignar.offsetHeight;
    
    // Establecer la altura máxima
    const alturaMaxima = Math.max(alturaEnvios, alturaAsignar);
    
    if (alturaMaxima > 0) {
        cardEnvios.style.height = alturaMaxima + 'px';
        cardAsignar.style.height = alturaMaxima + 'px';
    }
}

// Igualar alturas de cards en cada fila
function igualarAlturasCards() {
    const cards = document.querySelectorAll('.envio-card');
    if (cards.length === 0) return;
    
    // Resetear alturas
    cards.forEach(card => {
        card.style.height = 'auto';
    });
    
    // Agrupar cards por fila (basado en posición)
    const rows = [];
    let currentRow = [];
    let currentTop = null;
    
    cards.forEach((card, index) => {
        const rect = card.getBoundingClientRect();
        const top = Math.round(rect.top);
        
        if (currentTop === null || Math.abs(top - currentTop) < 5) {
            // Misma fila
            currentRow.push(card);
            if (currentTop === null) currentTop = top;
        } else {
            // Nueva fila
            if (currentRow.length > 0) {
                rows.push(currentRow);
            }
            currentRow = [card];
            currentTop = top;
        }
    });
    
    // Agregar última fila
    if (currentRow.length > 0) {
        rows.push(currentRow);
    }
    
    // Igualar alturas en cada fila
    rows.forEach(row => {
        let maxHeight = 0;
        row.forEach(card => {
            const height = card.offsetHeight;
            if (height > maxHeight) {
                maxHeight = height;
            }
        });
        row.forEach(card => {
            card.style.height = maxHeight + 'px';
        });
    });
}

// Inicializar
$(document).ready(function() {
    console.log('📦 Módulo cargado. Envíos disponibles:', {{ $enviosPendientes->count() }});
    
    // Igualar alturas de cards principales
    function actualizarAlturas() {
        igualarAlturasCardsPrincipales();
        igualarAlturasCards();
    }
    
    // Igualar alturas inicial
    setTimeout(actualizarAlturas, 100);
    
    // Igualar alturas cuando cambia el tamaño de la ventana
    $(window).on('resize', function() {
        setTimeout(actualizarAlturas, 100);
    });
    
    // Igualar alturas cuando se muestra/oculta la animación
    const observer = new MutationObserver(function(mutations) {
        setTimeout(actualizarAlturas, 150);
    });
    
    const cardAnimacion = document.getElementById('card-animacion');
    if (cardAnimacion) {
        observer.observe(cardAnimacion, {
            attributes: true,
            attributeFilter: ['style']
        });
    }
    
    // Trigger inicial
    actualizarSeleccion();
    
    // Actualizar alturas después de actualizar selección
    const originalActualizarSeleccion = window.actualizarSeleccion;
    window.actualizarSeleccion = function() {
        if (originalActualizarSeleccion) {
            originalActualizarSeleccion();
        }
        setTimeout(actualizarAlturas, 150);
    };
});
</script>
@endsection

