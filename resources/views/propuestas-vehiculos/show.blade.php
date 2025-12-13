@extends('adminlte::page')
@section('title', 'Detalles de Propuesta')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clipboard-check"></i> Detalles de Propuesta de Vehículos</h1>
        <div>
            <a href="{{ route('propuestas-vehiculos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Información General -->
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-info-circle"></i> Información General</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Código de Envío:</strong> {{ $propuesta->codigo_envio }}</p>
                        <p><strong>Fecha de Propuesta:</strong> {{ $propuesta->fecha_propuesta->format('d/m/Y H:i:s') }}</p>
                        <p><strong>Estado:</strong> 
                            @if($propuesta->estado == 'aprobada')
                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aprobada</span>
                            @elseif($propuesta->estado == 'rechazada')
                                <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rechazada</span>
                            @else
                                <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        @if($propuesta->fecha_decision)
                            <p><strong>Fecha de Decisión:</strong> {{ $propuesta->fecha_decision->format('d/m/Y H:i:s') }}</p>
                        @endif
                        @if($propuesta->observaciones_trazabilidad)
                            <p><strong>Observaciones de Trazabilidad:</strong></p>
                            <div class="alert alert-info">
                                {{ $propuesta->observaciones_trazabilidad }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales del Envío -->
        <div class="card shadow mt-3">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title text-white"><i class="fas fa-calculator"></i> Totales del Envío</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-weight"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Peso Total</span>
                                <span class="info-box-number">{{ number_format($propuesta->propuesta_data['totales']['peso_kg'] ?? 0, 2) }} kg</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-cube"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Volumen Total</span>
                                <span class="info-box-number">{{ number_format($propuesta->propuesta_data['totales']['volumen_m3'] ?? 0, 2) }} m³</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-boxes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cantidad de Productos</span>
                                <span class="info-box-number">{{ $propuesta->propuesta_data['totales']['cantidad_productos'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehículos Propuestos -->
        <div class="card shadow mt-3">
            <div class="card-header bg-gradient-success">
                <h3 class="card-title text-white">
                    <i class="fas fa-truck"></i> Vehículos Propuestos 
                    <span class="badge badge-light">{{ count($propuesta->propuesta_data['vehiculos_propuestos'] ?? []) }}</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Vehículo</th>
                                <th>Placa</th>
                                <th>Transportista</th>
                                <th>Peso Asignado (kg)</th>
                                <th>Volumen Asignado (m³)</th>
                                <th>% Uso</th>
                                <th>Tipo Transporte</th>
                                <th>Tamaño</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($propuesta->propuesta_data['vehiculos_propuestos'] ?? [] as $index => $vehiculo)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $vehiculo['vehiculo']['marca'] ?? 'N/A' }} {{ $vehiculo['vehiculo']['modelo'] ?? '' }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $vehiculo['vehiculo']['placa'] ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    {{ $vehiculo['vehiculo']['transportista']['name'] ?? 'Sin asignar' }}
                                </td>
                                <td>
                                    <strong>{{ number_format($vehiculo['peso_asignado_kg'] ?? 0, 2) }}</strong> kg
                                </td>
                                <td>
                                    <strong>{{ number_format($vehiculo['volumen_asignado_m3'] ?? 0, 2) }}</strong> m³
                                </td>
                                <td>
                                    @php
                                        $porcentaje = $vehiculo['porcentaje_uso'] ?? 0;
                                        $color = $porcentaje > 80 ? 'danger' : ($porcentaje > 60 ? 'warning' : 'success');
                                    @endphp
                                    <span class="badge badge-{{ $color }}">{{ number_format($porcentaje, 1) }}%</span>
                                </td>
                                <td>
                                    {{ $vehiculo['tipo_transporte']['nombre'] ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $vehiculo['tamano']['nombre'] ?? 'N/A' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Productos del Envío -->
        @if(isset($propuesta->propuesta_data['productos']) && count($propuesta->propuesta_data['productos']) > 0)
        <div class="card shadow mt-3">
            <div class="card-header bg-gradient-warning">
                <h3 class="card-title text-white"><i class="fas fa-box"></i> Productos del Envío</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Peso Unitario (kg)</th>
                                <th>Peso Total (kg)</th>
                                <th>Dimensiones (cm)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($propuesta->propuesta_data['productos'] as $producto)
                            <tr>
                                <td>
                                    <strong>{{ $producto['producto']['nombre'] ?? 'N/A' }}</strong>
                                </td>
                                <td>{{ $producto['cantidad'] ?? 0 }}</td>
                                <td>{{ number_format($producto['peso_unitario'] ?? 0, 2) }}</td>
                                <td>{{ number_format($producto['total_peso'] ?? 0, 2) }}</td>
                                <td>
                                    @if(isset($producto['alto_producto_cm']) && isset($producto['ancho_producto_cm']) && isset($producto['largo_producto_cm']))
                                        {{ $producto['alto_producto_cm'] }} x {{ $producto['ancho_producto_cm'] }} x {{ $producto['largo_producto_cm'] }}
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
        </div>
        @endif

        <!-- Acciones -->
        <div class="card shadow mt-3">
            <div class="card-body">
                <a href="{{ route('propuestas-vehiculos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
                @if($propuesta->envio)
                    <a href="{{ route('envios.show', $propuesta->envio->id) }}" class="btn btn-primary">
                        <i class="fas fa-box"></i> Ver Envío
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

