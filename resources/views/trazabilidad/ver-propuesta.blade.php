@extends('adminlte::page')
@section('title', 'Ver Propuesta - ' . $pedido->codigo)
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clipboard-check"></i> Propuesta de Envío - {{ $pedido->codigo }}</h1>
        <div>
            @php
                $tieneRutaPdf = \Route::has('trazabilidad.propuestas.descargar-pdf');
                $tieneRutaPropuestas = \Route::has('trazabilidad.propuestas-envios');
                $tieneRutaVehiculos = \Route::has('propuestas-vehiculos.index');
            @endphp
            @if($tieneRutaPdf)
                <a href="{{ route('trazabilidad.propuestas.descargar-pdf', $pedido->id) }}" class="btn btn-danger mr-2" target="_blank">
                    <i class="fas fa-file-pdf"></i> Descargar PDF
                </a>
            @endif
            @if($tieneRutaPropuestas)
                <a href="{{ route('trazabilidad.propuestas-envios') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            @endif
            @if($tieneRutaVehiculos)
                <a href="{{ route('propuestas-vehiculos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Propuestas
                </a>
            @endif
        </div>
    </div>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="row">
    <!-- Información del Pedido -->
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-info-circle"></i> Información del Pedido</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Código:</strong> {{ $pedido->codigo }}</p>
                        <p><strong>Almacén:</strong> {{ $pedido->almacen->nombre ?? 'N/A' }}</p>
                        <p><strong>Propietario:</strong> {{ $pedido->propietario->name ?? 'N/A' }}</p>
                        <p><strong>Fecha Requerida:</strong> {{ $pedido->fecha_requerida->format('d/m/Y') }}</p>
                        <p><strong>Hora Requerida:</strong> {{ $pedido->hora_requerida ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Estado:</strong> 
                            <span class="badge badge-{{ $pedido->estado == 'propuesta_enviada' ? 'warning' : 'success' }}">
                                {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                            </span>
                        </p>
                        <p><strong>Dirección:</strong> {{ $pedido->direccion_completa ?? 'N/A' }}</p>
                        <p><strong>Coordenadas:</strong> {{ $pedido->latitud }}, {{ $pedido->longitud }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cubicaje y Propuesta -->
@if(isset($cubicaje))
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-success">
                <h3 class="card-title text-white"><i class="fas fa-calculator"></i> Análisis de Cubicaje</h3>
            </div>
            <div class="card-body">
                <!-- Totales -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-weight"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Peso Total</span>
                                <span class="info-box-number">{{ number_format($cubicaje['totales']['peso_kg'] ?? 0, 2) }} kg</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-cube"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Volumen Total</span>
                                <span class="info-box-number">{{ number_format($cubicaje['totales']['volumen_m3'] ?? 0, 2) }} m³</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-boxes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cantidad Productos</span>
                                <span class="info-box-number">{{ $cubicaje['totales']['cantidad_productos'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tipo de Transporte -->
                @if(isset($cubicaje['tipo_transporte']))
                <div class="alert alert-info">
                    <h5><i class="fas fa-truck"></i> Tipo de Transporte Recomendado</h5>
                    <p><strong>{{ $cubicaje['tipo_transporte']->nombre ?? 'Estándar' }}</strong></p>
                    @if($cubicaje['tipo_transporte']->descripcion)
                        <p>{{ $cubicaje['tipo_transporte']->descripcion }}</p>
                    @endif
                </div>
                @endif

                <!-- Visualización Interactiva del Camión -->
                @if(isset($cubicaje['capacidad_requerida']))
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-truck"></i> Visualización de Carga del Vehículo</h5>
                            </div>
                            <div class="card-body text-center">
                                <!-- Camión visual -->
                                <div class="truck-container-propuesta mb-3">
                                    <div class="truck-visual-propuesta">
                                        <div class="truck-cabin-propuesta">
                                            <div class="truck-window-propuesta"></div>
                                        </div>
                                        <div class="truck-cargo-propuesta">
                                            <div class="truck-cargo-fill-propuesta" id="truckCargoFillPropuesta">
                                                <div class="cargo-boxes-propuesta" id="cargoBoxesPropuesta"></div>
                                            </div>
                                            <div class="truck-cargo-text-propuesta" id="truckCargoTextPropuesta">0%</div>
                                        </div>
                                        <div class="truck-wheels-propuesta">
                                            <div class="wheel-propuesta"></div>
                                            <div class="wheel-propuesta"></div>
                                            <div class="wheel-propuesta"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Barra de progreso -->
                                <div class="progress mb-2" style="height: 25px; border-radius: 12px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         id="barraCapacidadPropuesta"
                                         style="width: 0%; transition: all 0.5s ease;"
                                         aria-valuenow="0" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <span id="porcentajeCapacidadPropuesta">0%</span>
                                    </div>
                                </div>
                                
                                <!-- Información de capacidad -->
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <small class="d-block text-success"><i class="fas fa-box"></i> Cargado</small>
                                        <strong id="pesoCargadoPropuesta" class="d-block">{{ number_format($cubicaje['totales']['peso_kg'] ?? 0, 2) }} kg</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="d-block text-info"><i class="fas fa-arrow-up"></i> 
                                            @if(isset($cubicaje['vehiculo_recomendado']) && $cubicaje['vehiculo_recomendado'])
                                                Capacidad del Vehículo
                                            @else
                                                Capacidad Requerida
                                            @endif
                                        </small>
                                        <strong id="capacidadRequeridaPropuesta" class="d-block">
                                            @if(isset($cubicaje['vehiculo_recomendado']) && $cubicaje['vehiculo_recomendado'])
                                                {{ number_format($cubicaje['vehiculo_recomendado']['capacidad_carga_kg'] ?? 0, 2) }} kg
                                            @else
                                                {{ number_format($cubicaje['capacidad_requerida']['peso_minimo_kg'] ?? 0, 2) }} kg
                                            @endif
                                        </strong>
                                    </div>
                                </div>

                                <!-- Información del Vehículo Recomendado -->
                                @if(isset($cubicaje['vehiculo_recomendado']) && $cubicaje['vehiculo_recomendado'])
                                <div class="border-top pt-3 mt-3">
                                    <h6 class="text-primary mb-3"><i class="fas fa-truck"></i> Vehículo Recomendado</h6>
                                    <div class="row text-left">
                                        <div class="col-12 mb-2">
                                            <strong><i class="fas fa-tag"></i> Placa:</strong> 
                                            <span class="badge badge-primary">{{ $cubicaje['vehiculo_recomendado']['vehiculo']->placa ?? 'N/A' }}</span>
                                        </div>
                                        @if($cubicaje['vehiculo_recomendado']['vehiculo']->marca || $cubicaje['vehiculo_recomendado']['vehiculo']->modelo)
                                        <div class="col-12 mb-2">
                                            <strong><i class="fas fa-car"></i> Vehículo:</strong> 
                                            {{ $cubicaje['vehiculo_recomendado']['vehiculo']->marca ?? '' }} 
                                            {{ $cubicaje['vehiculo_recomendado']['vehiculo']->modelo ?? '' }}
                                        </div>
                                        @endif
                                        <div class="col-12 mb-2">
                                            <strong><i class="fas fa-weight"></i> Capacidad de Carga:</strong> 
                                            {{ number_format($cubicaje['vehiculo_recomendado']['capacidad_carga_kg'], 2) }} kg
                                        </div>
                                        @if($cubicaje['vehiculo_recomendado']['capacidad_volumen_m3'] > 0)
                                        <div class="col-12 mb-2">
                                            <strong><i class="fas fa-cube"></i> Capacidad de Volumen:</strong> 
                                            {{ number_format($cubicaje['vehiculo_recomendado']['capacidad_volumen_m3'], 2) }} m³
                                        </div>
                                        @endif
                                        @if($cubicaje['vehiculo_recomendado']['tipo_transporte'])
                                        <div class="col-12 mb-2">
                                            <strong><i class="fas fa-snowflake"></i> Tipo de Transporte:</strong> 
                                            <span class="badge badge-info">{{ $cubicaje['vehiculo_recomendado']['tipo_transporte']->nombre ?? 'N/A' }}</span>
                                        </div>
                                        @endif
                                        @if($cubicaje['vehiculo_recomendado']['tamano'])
                                        <div class="col-12 mb-2">
                                            <strong><i class="fas fa-ruler"></i> Tamaño:</strong> 
                                            {{ $cubicaje['vehiculo_recomendado']['tamano']->nombre ?? 'N/A' }}
                                        </div>
                                        @endif
                                        @if($cubicaje['vehiculo_recomendado']['transportista'])
                                        <div class="col-12 mb-2">
                                            <strong><i class="fas fa-user"></i> Transportista:</strong> 
                                            {{ $cubicaje['vehiculo_recomendado']['transportista']->name ?? 'N/A' }}
                                        </div>
                                        @endif
                                        <div class="col-12 mt-2">
                                            <div class="alert alert-{{ $cubicaje['vehiculo_recomendado']['porcentaje_uso'] > 90 ? 'danger' : ($cubicaje['vehiculo_recomendado']['porcentaje_uso'] > 70 ? 'warning' : 'success') }} mb-0">
                                                <strong>Uso de Capacidad:</strong> 
                                                {{ number_format($cubicaje['vehiculo_recomendado']['porcentaje_uso'], 1) }}%
                                                @if($cubicaje['vehiculo_recomendado']['porcentaje_uso'] > 90)
                                                    <br><small>⚠️ Carga crítica - considerar vehículo más grande</small>
                                                @elseif($cubicaje['vehiculo_recomendado']['porcentaje_uso'] > 70)
                                                    <br><small>⚡ Alta carga - manejar con precaución</small>
                                                @else
                                                    <br><small>✅ Carga óptima</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="border-top pt-3 mt-3">
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <strong>No se encontró vehículo disponible</strong> que cumpla con los requisitos.
                                        <br><small>Se recomienda revisar la disponibilidad de vehículos o ajustar los requisitos del pedido.</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Visualización 3D de Cajas -->
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-cube"></i> Visualización 3D de Empaques Recomendados</h5>
                            </div>
                            <div class="card-body text-center">
                                <!-- Selector de empaque -->
                                @if(isset($cubicaje['recomendacion_empaque']) && count($cubicaje['recomendacion_empaque']) > 0)
                                <div class="mb-3">
                                    <label class="small text-muted">Ver empaque para:</label>
                                    <select id="selector-empaque" class="form-control form-control-sm" onchange="cambiarVisualizacionEmpaque()">
                                        @foreach($cubicaje['recomendacion_empaque'] as $index => $recomendacion)
                                        <option value="{{ $index }}" data-empaque='@json($recomendacion)'>
                                            {{ $recomendacion['producto'] }} - {{ $recomendacion['tipo_empaque']->nombre ?? 'N/A' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- Contenedor de visualización 3D -->
                                <div id="contenedor-3d-empaques" class="cajas-3d-multiples" style="position: relative;">
                                    <!-- Las cajas se cargarán dinámicamente -->
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i> 
                                    <span id="ayuda-scroll" style="display: none;">Desplázate para ver todas las cajas</span>
                                </small>

                                <!-- Información del empaque actual -->
                                <div id="info-empaque-actual" class="mt-3 p-2 bg-light rounded">
                                    <small class="text-muted">Seleccione un producto para ver su empaque recomendado</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Recomendación de Empaque -->
                @if(isset($cubicaje['recomendacion_empaque']))
                <div class="card mt-3">
                    <div class="card-header bg-warning">
                        <h5 class="card-title"><i class="fas fa-box"></i> Recomendación de Empaque</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Tipo de Empaque</th>
                                    <th>Cantidad de Cajas</th>
                                    <th>Dimensiones (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cubicaje['recomendacion_empaque'] as $recomendacion)
                                <tr>
                                    <td>{{ $recomendacion['producto'] }}</td>
                                    <td>{{ $recomendacion['tipo_empaque']->nombre ?? 'N/A' }}</td>
                                    <td>{{ $recomendacion['cantidad_cajas'] }}</td>
                                    <td>
                                        @if($recomendacion['dimensiones_caja'])
                                            {{ $recomendacion['dimensiones_caja']['largo_cm'] ?? 0 }} x 
                                            {{ $recomendacion['dimensiones_caja']['ancho_cm'] ?? 0 }} x 
                                            {{ $recomendacion['dimensiones_caja']['alto_cm'] ?? 0 }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Velocidad Recomendada -->
                @if(isset($cubicaje['velocidad_recomendada']))
                <div class="alert alert-warning mt-3">
                    <h5><i class="fas fa-tachometer-alt"></i> Velocidad Recomendada</h5>
                    <p><strong>Velocidad:</strong> {{ $cubicaje['velocidad_recomendada']['velocidad_recomendada_kmh'] ?? 60 }} km/h</p>
                    <p><strong>Máxima:</strong> {{ $cubicaje['velocidad_recomendada']['velocidad_maxima_kmh'] ?? 80 }} km/h</p>
                    <p><strong>Mínima:</strong> {{ $cubicaje['velocidad_recomendada']['velocidad_minima_kmh'] ?? 40 }} km/h</p>
                    <p><strong>Razón:</strong> {{ $cubicaje['velocidad_recomendada']['razon'] ?? 'N/A' }}</p>
                </div>
                @endif

                <!-- Recomendaciones Generales -->
                @if(isset($cubicaje['recomendaciones']))
                <div class="card mt-3">
                    <div class="card-header bg-info">
                        <h5 class="card-title"><i class="fas fa-lightbulb"></i> Recomendaciones</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            @foreach($cubicaje['recomendaciones'] as $recomendacion)
                            <li>{{ $recomendacion }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Productos del Pedido -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title text-white"><i class="fas fa-boxes"></i> Productos del Pedido</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Peso Unitario (kg)</th>
                            <th>Precio Unitario</th>
                            <th>Total Peso (kg)</th>
                            <th>Total Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->productos as $producto)
                        <tr>
                            <td>{{ $producto->producto_nombre }}</td>
                            <td>{{ $producto->cantidad }}</td>
                            <td>{{ number_format($producto->peso_unitario, 2) }}</td>
                            <td>Bs {{ number_format($producto->precio_unitario, 2) }}</td>
                            <td>{{ number_format($producto->total_peso, 2) }}</td>
                            <td>Bs {{ number_format($producto->total_precio, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="4" class="text-right">TOTALES:</td>
                            <td>{{ number_format($pedido->productos->sum('total_peso'), 2) }} kg</td>
                            <td>Bs {{ number_format($pedido->productos->sum('total_precio'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Acciones -->
@if($pedido->estado == 'propuesta_enviada')
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-warning">
                <h3 class="card-title text-white"><i class="fas fa-tasks"></i> Acciones</h3>
            </div>
            <div class="card-body">
                @php
                    $tieneRutaPdf = \Route::has('trazabilidad.propuestas.descargar-pdf');
                    $tieneRutaAprobar = \Route::has('trazabilidad.propuestas.aprobar');
                @endphp
                @if($tieneRutaPdf)
                    <a href="{{ route('trazabilidad.propuestas.descargar-pdf', $pedido->id) }}" class="btn btn-danger btn-lg mr-2" target="_blank">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </a>
                @endif
                
                @if($tieneRutaAprobar)
                    <form action="{{ route('trazabilidad.propuestas.aprobar', $pedido->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de aprobar esta propuesta?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg mr-2">
                            <i class="fas fa-check"></i> Aprobar Propuesta
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-warning btn-lg" data-toggle="modal" data-target="#rechazarModal">
                        <i class="fas fa-times"></i> Rechazar Propuesta
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Rechazar -->
<div class="modal fade" id="rechazarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rechazar Propuesta</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            @php
                $tieneRutaRechazar = \Route::has('trazabilidad.propuestas.rechazar');
            @endphp
            @if($tieneRutaRechazar)
            <form action="{{ route('trazabilidad.propuestas.rechazar', $pedido->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Motivo del Rechazo <span class="text-danger">*</span></label>
                        <textarea name="motivo" class="form-control" rows="3" required minlength="10" placeholder="Explique el motivo del rechazo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar Propuesta</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('css')
<style>
/* ===== ESTILOS DEL CAMIÓN VISUAL ===== */
.truck-container-propuesta {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    padding: 15px;
}

.truck-visual-propuesta {
    position: relative;
    width: 220px;
    height: 100px;
}

.truck-cabin-propuesta {
    position: absolute;
    right: 0;
    bottom: 20px;
    width: 55px;
    height: 55px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-radius: 8px 8px 0 0;
    border: 2px solid #1a5276;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.truck-window-propuesta {
    position: absolute;
    top: 8px;
    left: 8px;
    right: 8px;
    height: 20px;
    background: linear-gradient(135deg, #85c1e9, #aed6f1);
    border-radius: 4px;
    border: 1px solid #1a5276;
}

.truck-cargo-propuesta {
    position: absolute;
    left: 0;
    bottom: 20px;
    width: 160px;
    height: 85px;
    background: linear-gradient(135deg, #ecf0f1, #bdc3c7);
    border: 3px solid #7f8c8d;
    border-radius: 4px;
    overflow: hidden;
}

.truck-cargo-fill-propuesta {
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

.truck-cargo-fill-propuesta.warning {
    background: linear-gradient(0deg, #f39c12, #f1c40f, #f4d03f);
}

.truck-cargo-fill-propuesta.danger {
    background: linear-gradient(0deg, #c0392b, #e74c3c, #ec7063);
    animation: pulse-danger 1s infinite;
}

@keyframes pulse-danger {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.cargo-boxes-propuesta {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
    justify-content: center;
    align-content: flex-end;
}

.cargo-box-propuesta {
    width: 14px;
    height: 14px;
    background: #8b4513;
    border: 1px solid #5d3a1a;
    border-radius: 2px;
    animation: boxAppear 0.3s ease;
}

@keyframes boxAppear {
    from { transform: scale(0); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.truck-cargo-text-propuesta {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
    text-shadow: 1px 1px 2px rgba(255,255,255,0.8);
    z-index: 10;
}

.truck-wheels-propuesta {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: space-between;
    padding: 0 20px;
}

.wheel-propuesta {
    width: 25px;
    height: 25px;
    background: linear-gradient(135deg, #2c3e50, #34495e);
    border-radius: 50%;
    border: 3px solid #1a252f;
    box-shadow: inset 0 0 5px rgba(0,0,0,0.5);
}

/* ===== ESTILOS DE CAJA 3D MEJORADO ===== */
.cajas-3d-multiples {
    perspective: 1200px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: flex-start;
    gap: 15px;
    max-height: 500px;
    min-height: 300px;
    padding: 20px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 10px;
    overflow-y: auto;
    overflow-x: hidden;
    position: relative;
}

.cajas-3d-multiples::-webkit-scrollbar {
    width: 8px;
}

.cajas-3d-multiples::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.1);
    border-radius: 4px;
}

.cajas-3d-multiples::-webkit-scrollbar-thumb {
    background: #6c757d;
    border-radius: 4px;
}

.cajas-3d-multiples::-webkit-scrollbar-thumb:hover {
    background: #5a6268;
}

.caja-3d-wrapper-propuesta {
    perspective: 1000px;
    display: inline-block;
    margin: 10px;
}

.caja-3d-propuesta {
    width: 200px;
    height: 200px;
    position: relative;
    transform-style: preserve-3d;
    animation: rotarCajaPropuesta 15s ease-in-out infinite;
    transition: transform 0.3s ease;
}

.caja-3d-propuesta:hover {
    animation-play-state: paused;
    transform: rotateY(-25deg) rotateX(15deg) scale(1.1);
}

@keyframes rotarCajaPropuesta {
    0%, 100% {
        transform: rotateY(-20deg) rotateX(10deg);
    }
    25% {
        transform: rotateY(20deg) rotateX(10deg);
    }
    50% {
        transform: rotateY(20deg) rotateX(-10deg);
    }
    75% {
        transform: rotateY(-20deg) rotateX(-10deg);
    }
}

/* Cara frontal de la caja */
.caja-frontal-propuesta {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 30%, #8B4513 60%, #654321 100%);
    border: 4px solid #654321;
    border-radius: 8px;
    position: relative;
    transform: translateZ(10px);
    box-shadow: 
        inset 0 0 30px rgba(0,0,0,0.4),
        8px 8px 20px rgba(0,0,0,0.5),
        0 0 0 2px rgba(255,255,255,0.1);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 12px;
    overflow: hidden;
}

.caja-frontal-propuesta::before {
    content: '';
    position: absolute;
    top: 8%;
    left: 8%;
    right: 8%;
    bottom: 8%;
    border: 2px dashed rgba(255,255,255,0.4);
    border-radius: 4px;
    z-index: 0;
}

/* Cara superior (efecto 3D) */
.caja-top-propuesta {
    position: absolute;
    top: -10px;
    left: 0;
    right: 0;
    height: 10px;
    background: linear-gradient(135deg, #A0522D 0%, #8B4513 100%);
    border: 4px solid #654321;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    transform: rotateX(90deg) translateZ(10px);
    transform-origin: bottom;
}

/* Cara lateral derecha (efecto 3D) */
.caja-side-propuesta {
    position: absolute;
    top: 0;
    right: -10px;
    bottom: 0;
    width: 10px;
    background: linear-gradient(90deg, #8B4513 0%, #654321 100%);
    border: 4px solid #654321;
    border-left: none;
    border-radius: 0 8px 8px 0;
    transform: rotateY(90deg) translateZ(10px);
    transform-origin: left;
}

.productos-dentro-propuesta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(25px, 1fr));
    gap: 3px;
    width: 100%;
    height: 100%;
    padding: 8px;
    overflow: auto;
    max-height: 180px;
    position: relative;
    z-index: 1;
    align-content: start;
}

.productos-dentro-propuesta::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}

.productos-dentro-propuesta::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.1);
    border-radius: 2px;
}

.productos-dentro-propuesta::-webkit-scrollbar-thumb {
    background: #28a745;
    border-radius: 2px;
}

.producto-item-mini-propuesta {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    border-radius: 4px;
    border: 2px solid #FF8C00;
    animation: itemBouncePropuesta 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    padding: 2px;
    min-height: 22px;
    min-width: 22px;
    box-shadow: 
        0 2px 4px rgba(0,0,0,0.2),
        inset 0 1px 2px rgba(255,255,255,0.3);
    transition: all 0.3s ease;
    color: #333;
    font-weight: bold;
    cursor: pointer;
    position: relative;
}

.producto-item-mini-propuesta::after {
    content: '📦';
    font-size: 0.7rem;
    filter: drop-shadow(0 1px 1px rgba(0,0,0,0.3));
}

.producto-item-mini-propuesta:hover {
    transform: scale(1.2) translateZ(5px);
    z-index: 10;
    box-shadow: 
        0 4px 8px rgba(0,0,0,0.3),
        inset 0 1px 2px rgba(255,255,255,0.4);
}

@keyframes itemBouncePropuesta {
    0% {
        opacity: 0;
        transform: scale(0) rotate(-180deg) translateZ(-20px);
    }
    60% {
        transform: scale(1.15) rotate(10deg) translateZ(5px);
    }
    100% {
        opacity: 1;
        transform: scale(1) rotate(0deg) translateZ(0);
    }
}

.caja-etiqueta-propuesta {
    position: absolute;
    bottom: -35px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 6px 18px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    white-space: nowrap;
    box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    z-index: 5;
}

/* Múltiples cajas */
.cajas-grid-3d {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 15px;
    justify-items: center;
    width: 100%;
    padding: 10px;
}

.caja-individual-3d {
    display: inline-block;
    margin: 0 5px;
}

/* Cuando hay muchas cajas, hacerlas más pequeñas */
.cajas-grid-3d.muchas-cajas .caja-3d-wrapper-propuesta {
    transform: scale(0.8);
}

.cajas-grid-3d.muchas-cajas .caja-3d-propuesta {
    width: 150px;
    height: 150px;
}

.cajas-grid-3d.muchas-cajas .caja-etiqueta-propuesta {
    font-size: 0.7rem;
    padding: 4px 12px;
    bottom: -25px;
}

/* Contador de cajas */
.contador-cajas {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.85rem;
    z-index: 10;
    font-weight: bold;
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    @if(isset($cubicaje))
    // Datos del cubicaje
    const pesoTotal = {{ $cubicaje['totales']['peso_kg'] ?? 0 }};
    const recomendacionesEmpaque = @json($cubicaje['recomendacion_empaque'] ?? []);
    
    @if(isset($cubicaje['vehiculo_recomendado']) && $cubicaje['vehiculo_recomendado'])
        const capacidadVehiculo = {{ $cubicaje['vehiculo_recomendado']['capacidad_carga_kg'] ?? 1000 }};
        const porcentajeUso = Math.min(100, (pesoTotal / capacidadVehiculo) * 100);
    @else
        const capacidadRequerida = {{ $cubicaje['capacidad_requerida']['peso_minimo_kg'] ?? 1000 }};
        const porcentajeUso = Math.min(100, (pesoTotal / capacidadRequerida) * 100);
    @endif
    const productos = @json($pedido->productos ?? []);
    
    // Actualizar gráfico del camión
    @if(isset($cubicaje['vehiculo_recomendado']) && $cubicaje['vehiculo_recomendado'])
        actualizarGraficoTruck(porcentajeUso, pesoTotal, capacidadVehiculo);
    @else
        actualizarGraficoTruck(porcentajeUso, pesoTotal, capacidadRequerida);
    @endif
    
    // Inicializar visualización 3D de empaques
    if (recomendacionesEmpaque.length > 0) {
        cambiarVisualizacionEmpaque();
    } else {
        actualizarGrafico3DCajas(productos);
    }
    @endif
});

function actualizarGraficoTruck(porcentaje, pesoCargado, capacidad) {
    const $fill = $('#truckCargoFillPropuesta');
    const $text = $('#truckCargoTextPropuesta');
    const $barra = $('#barraCapacidadPropuesta');
    const $porcentaje = $('#porcentajeCapacidadPropuesta');
    const $pesoCargado = $('#pesoCargadoPropuesta');
    const $capacidadRequerida = $('#capacidadRequeridaPropuesta');
    
    // Actualizar altura del fill
    $fill.css('height', porcentaje + '%');
    
    // Cambiar color según porcentaje
    $fill.removeClass('warning danger');
    if (porcentaje > 90) {
        $fill.addClass('danger');
    } else if (porcentaje > 70) {
        $fill.addClass('warning');
    }
    
    // Actualizar texto
    $text.text(porcentaje.toFixed(1) + '%');
    $porcentaje.text(porcentaje.toFixed(1) + '%');
    
    // Actualizar barra de progreso
    $barra.css('width', porcentaje + '%');
    $barra.attr('aria-valuenow', porcentaje);
    
    // Actualizar información de capacidad
    $pesoCargado.text(pesoCargado.toFixed(2) + ' kg');
    $capacidadRequerida.text(capacidad.toFixed(2) + ' kg');
    
    // Agregar cajas visuales dentro del camión
    const numCajas = Math.min(20, Math.ceil(porcentaje / 5));
    const $cargoBoxes = $('#cargoBoxesPropuesta');
    $cargoBoxes.empty();
    
    for (let i = 0; i < numCajas; i++) {
        $cargoBoxes.append('<div class="cargo-box-propuesta"></div>');
    }
}

function cambiarVisualizacionEmpaque() {
    const selector = document.getElementById('selector-empaque');
    if (!selector) return;
    
    const index = parseInt(selector.value);
    const option = selector.options[index];
    const empaqueData = JSON.parse(option.getAttribute('data-empaque'));
    
    const $contenedor = $('#contenedor-3d-empaques');
    const $info = $('#info-empaque-actual');
    
    $contenedor.empty();
    
    if (!empaqueData || !empaqueData.tipo_empaque) {
        $info.html('<small class="text-danger">No hay empaque recomendado para este producto</small>');
        return;
    }
    
    const tipoEmpaque = empaqueData.tipo_empaque;
    const cantidadCajas = empaqueData.cantidad_cajas || 1;
    const itemsPorCaja = empaqueData.items_por_caja || empaqueData.cantidad_producto || 1;
    const dimensiones = empaqueData.dimensiones_caja || {};
    
    // Determinar si hay muchas cajas (más de 6)
    const tieneMuchasCajas = cantidadCajas > 6;
    const cajasAMostrar = tieneMuchasCajas ? 6 : cantidadCajas;
    const cajasOcultas = cantidadCajas - cajasAMostrar;
    
    // Calcular tamaño de caja basado en dimensiones y cantidad
    let escala = calcularEscalaCaja(dimensiones);
    if (tieneMuchasCajas) {
        escala = escala * 0.7; // Reducir tamaño cuando hay muchas cajas
    }
    
    // Crear contenedor con contador si hay muchas cajas
    const $grid = $('<div>').addClass('cajas-grid-3d');
    if (tieneMuchasCajas) {
        $grid.addClass('muchas-cajas');
        const $contador = $('<div>').addClass('contador-cajas')
            .html(`<i class="fas fa-cubes"></i> ${cantidadCajas} cajas totales`);
        $contenedor.append($contador);
    }
    
    // Crear visualización de cajas (limitado a 6 si hay muchas)
    for (let i = 0; i < cajasAMostrar; i++) {
        const $wrapper = $('<div>').addClass('caja-3d-wrapper-propuesta');
        const tamañoBase = tieneMuchasCajas ? 150 : 200;
        const $caja3d = $('<div>').addClass('caja-3d-propuesta')
            .css({
                'width': (tamañoBase * escala) + 'px',
                'height': (tamañoBase * escala) + 'px'
            });
        
        // Cara frontal
        const $frontal = $('<div>').addClass('caja-frontal-propuesta');
        const $productos = $('<div>').addClass('productos-dentro-propuesta');
        
        // Calcular grid según items por caja
        const columnas = Math.ceil(Math.sqrt(itemsPorCaja));
        $productos.css('grid-template-columns', `repeat(${columnas}, 1fr)`);
        
        // Agregar items visuales
        const itemsEnEstaCaja = Math.min(itemsPorCaja, empaqueData.cantidad_producto - (i * itemsPorCaja));
        for (let j = 0; j < itemsEnEstaCaja; j++) {
            const $item = $('<div>')
                .addClass('producto-item-mini-propuesta')
                .css('background', getColorProducto(i))
                .attr('title', empaqueData.producto + ' - Item ' + (j + 1));
            
            $productos.append($item);
        }
        
        $frontal.append($productos);
        $caja3d.append($frontal);
        
        // Cara superior
        const $top = $('<div>').addClass('caja-top-propuesta');
        $caja3d.append($top);
        
        // Cara lateral
        const $side = $('<div>').addClass('caja-side-propuesta');
        $caja3d.append($side);
        
        // Etiqueta
        let textoEtiqueta = `Caja ${i + 1}`;
        if (cantidadCajas > 1) {
            textoEtiqueta += `/${cantidadCajas}`;
        }
        textoEtiqueta += ` - ${itemsEnEstaCaja} items`;
        
        const $etiqueta = $('<div>')
            .addClass('caja-etiqueta-propuesta')
            .text(textoEtiqueta);
        
        $wrapper.append($caja3d);
        $wrapper.append($etiqueta);
        $grid.append($wrapper);
    }
    
    // Si hay cajas ocultas, agregar indicador
    if (cajasOcultas > 0) {
        const $indicador = $('<div>')
            .addClass('caja-3d-wrapper-propuesta')
            .css({
                'display': 'flex',
                'align-items': 'center',
                'justify-content': 'center',
                'flex-direction': 'column',
                'min-width': '150px',
                'min-height': '150px'
            });
        
        const $cajaOculta = $('<div>')
            .css({
                'width': '120px',
                'height': '120px',
                'background': 'linear-gradient(135deg, #6c757d 0%, #5a6268 100%)',
                'border': '3px solid #495057',
                'border-radius': '8px',
                'display': 'flex',
                'align-items': 'center',
                'justify-content': 'center',
                'color': 'white',
                'font-size': '2rem',
                'opacity': '0.6'
            })
            .html(`<i class="fas fa-ellipsis-h"></i>`);
        
        const $etiquetaOculta = $('<div>')
            .addClass('caja-etiqueta-propuesta')
            .css({
                'background': 'linear-gradient(135deg, #6c757d 0%, #5a6268 100%)',
                'margin-top': '10px'
            })
            .text(`+${cajasOcultas} más`);
        
        $indicador.append($cajaOculta);
        $indicador.append($etiquetaOculta);
        $grid.append($indicador);
    }
    
    $contenedor.append($grid);
    
    // Mostrar ayuda de scroll si hay muchas cajas
    if (tieneMuchasCajas) {
        $('#ayuda-scroll').show();
    } else {
        $('#ayuda-scroll').hide();
    }
    
    // Actualizar información
    const infoHtml = `
        <div class="row text-center">
            <div class="col-6">
                <strong><i class="fas fa-box"></i> Tipo:</strong><br>
                <span class="badge badge-success">${tipoEmpaque.nombre || 'N/A'}</span>
            </div>
            <div class="col-6">
                <strong><i class="fas fa-cubes"></i> Cantidad:</strong><br>
                <span class="badge badge-info">${cantidadCajas} cajas</span>
                ${tieneMuchasCajas ? '<br><small class="text-muted">(Mostrando 6 + ' + cajasOcultas + ' más)</small>' : ''}
            </div>
        </div>
        <div class="row text-center mt-2">
            <div class="col-12">
                <strong><i class="fas fa-ruler-combined"></i> Dimensiones:</strong><br>
                <small>${dimensiones.largo_cm || 0} × ${dimensiones.ancho_cm || 0} × ${dimensiones.alto_cm || 0} cm</small>
            </div>
        </div>
        <div class="row text-center mt-2">
            <div class="col-12">
                <strong><i class="fas fa-shopping-bag"></i> Items por caja:</strong><br>
                <small>${itemsPorCaja} unidades</small>
            </div>
        </div>
    `;
    $info.html(infoHtml);
}

function calcularEscalaCaja(dimensiones) {
    if (!dimensiones || !dimensiones.largo_cm) return 1;
    
    // Normalizar dimensiones (asumiendo que la caja más grande es 100cm)
    const maxDimension = Math.max(dimensiones.largo_cm, dimensiones.ancho_cm, dimensiones.alto_cm);
    const escala = Math.min(1.5, Math.max(0.5, 100 / maxDimension));
    return escala;
}

function actualizarGrafico3DCajas(productos) {
    const $contenedor = $('#contenedor-3d-empaques');
    const $info = $('#info-empaque-actual');
    
    $contenedor.empty();
    
    let totalItems = 0;
    let productosMostrados = [];
    
    // Crear representación visual de productos
    productos.forEach((producto, index) => {
        const cantidad = producto.cantidad || 1;
        const nombre = producto.producto_nombre || 'Producto';
        
        // Mostrar máximo 3 items por producto para mejor visualización
        const itemsAMostrar = Math.min(cantidad, 3);
        
        for (let i = 0; i < itemsAMostrar; i++) {
            const $item = $('<div>')
                .addClass('producto-item-mini-propuesta')
                .text(nombre.substring(0, 8))
                .attr('title', nombre + ' (' + cantidad + ' unidades)')
                .css('background', getColorProducto(index));
            
            $contenedor.append($item);
            totalItems++;
        }
        
        productosMostrados.push({
            nombre: nombre,
            cantidad: cantidad
        });
    });
    
    // Actualizar etiqueta
    const totalCantidad = productos.reduce((sum, p) => sum + (p.cantidad || 0), 0);
    $info.html(`<small><strong>Total:</strong> ${totalCantidad} items en ${productos.length} productos</small>`);
}

function getColorProducto(index) {
    const colores = [
        'linear-gradient(135deg, #FFD700 0%, #FFA500 100%)',
        'linear-gradient(135deg, #FF6B6B 0%, #EE5A6F 100%)',
        'linear-gradient(135deg, #4ECDC4 0%, #44A08D 100%)',
        'linear-gradient(135deg, #95E1D3 0%, #F38181 100%)',
        'linear-gradient(135deg, #FCE38A 0%, #F38181 100%)',
        'linear-gradient(135deg, #A8E6CF 0%, #88D8A3 100%)',
        'linear-gradient(135deg, #FFD3A5 0%, #FD9853 100%)',
    ];
    return colores[index % colores.length];
}
</script>
@endsection

