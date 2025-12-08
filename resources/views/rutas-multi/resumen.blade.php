@extends('adminlte::page')

@section('title', 'Resumen de Ruta')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center no-print">
        <h1><i class="fas fa-file-alt text-success"></i> Resumen de Entrega</h1>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="{{ route('rutas-multi.show', $resumen['ruta']['id']) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="resumen-container">
    <!-- Encabezado -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <h2 class="mb-0">🏭 PLANTA LOGÍSTICA</h2>
                    <p class="text-muted mb-0">Sistema de Control de Entregas</p>
                </div>
                <div class="col-sm-6 text-right">
                    <h3 class="text-primary">{{ $resumen['ruta']['codigo'] }}</h3>
                    <p class="mb-0">
                        <strong>Fecha:</strong> 
                        {{ isset($resumen['ruta']['fecha']) ? date('d/m/Y', strtotime($resumen['ruta']['fecha'])) : '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de la ruta -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-truck"></i> Información del Transporte</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Transportista:</th>
                            <td>{{ $resumen['ruta']['transportista_nombre'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td>{{ $resumen['ruta']['transportista_telefono'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Vehículo:</th>
                            <td>{{ $resumen['ruta']['vehiculo_placa'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Tipo:</th>
                            <td>{{ $resumen['ruta']['vehiculo_tipo'] ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Estadísticas</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Total Envíos:</th>
                            <td><span class="badge badge-primary badge-lg">{{ $resumen['estadisticas']['total_envios'] ?? 0 }}</span></td>
                        </tr>
                        <tr>
                            <th>Entregas Completadas:</th>
                            <td>
                                <span class="badge badge-success badge-lg">
                                    {{ $resumen['estadisticas']['paradas_completadas'] ?? 0 }} / {{ $resumen['estadisticas']['total_paradas'] ?? 0 }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Peso Total:</th>
                            <td>{{ number_format($resumen['estadisticas']['total_peso'] ?? 0, 2) }} kg</td>
                        </tr>
                        <tr>
                            <th>Tiempo Total:</th>
                            <td>
                                @if(isset($resumen['estadisticas']['tiempo_total_minutos']))
                                    {{ floor($resumen['estadisticas']['tiempo_total_minutos'] / 60) }}h 
                                    {{ $resumen['estadisticas']['tiempo_total_minutos'] % 60 }}min
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Horarios -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-clock"></i> Horarios</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <h4>Salida de Planta</h4>
                    <p class="h3 text-info">
                        @if(isset($resumen['ruta']['hora_salida']))
                            {{ date('H:i', strtotime($resumen['ruta']['hora_salida'])) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <h4>Última Entrega</h4>
                    <p class="h3 text-success">
                        @if(isset($resumen['ruta']['hora_fin']))
                            {{ date('H:i', strtotime($resumen['ruta']['hora_fin'])) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <h4>Estado</h4>
                    <p>
                        @php
                            $estadoClase = match($resumen['ruta']['estado'] ?? '') {
                                'completada' => 'success',
                                'en_transito' => 'warning',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-{{ $estadoClase }} badge-lg p-3">
                            {{ strtoupper(str_replace('_', ' ', $resumen['ruta']['estado'] ?? 'N/A')) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de entregas -->
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-boxes"></i> Detalle de Entregas</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Envío</th>
                        <th>Destino</th>
                        <th class="text-center">Hora Llegada</th>
                        <th class="text-center">Hora Entrega</th>
                        <th>Receptor</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resumen['paradas'] ?? [] as $parada)
                        <tr class="{{ $parada['estado'] == 'entregado' ? 'table-success' : '' }}">
                            <td class="text-center">
                                <strong>{{ $parada['orden'] }}</strong>
                            </td>
                            <td>
                                <strong>{{ $parada['envio_codigo'] ?? 'N/A' }}</strong><br>
                                <small>
                                    Peso: {{ number_format($parada['total_peso'] ?? 0, 2) }} kg |
                                    Cant: {{ $parada['total_cantidad'] ?? 0 }}
                                </small>
                            </td>
                            <td>
                                <strong>{{ $parada['almacen_nombre'] ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $parada['almacen_direccion'] ?? '' }}</small>
                            </td>
                            <td class="text-center">
                                @if(isset($parada['hora_llegada']))
                                    {{ date('H:i', strtotime($parada['hora_llegada'])) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($parada['hora_entrega']))
                                    {{ date('H:i', strtotime($parada['hora_entrega'])) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($parada['nombre_receptor']))
                                    <strong>{{ $parada['nombre_receptor'] }}</strong><br>
                                    <small>
                                        {{ $parada['cargo_receptor'] ?? '' }}
                                        @if(isset($parada['dni_receptor']))
                                            <br>CI: {{ $parada['dni_receptor'] }}
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($parada['estado'] == 'entregado')
                                    <span class="badge badge-success">✓ Entregado</span>
                                @elseif($parada['estado'] == 'en_destino')
                                    <span class="badge badge-info">En destino</span>
                                @else
                                    <span class="badge badge-warning">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Sin entregas registradas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Checklists -->
    @if(count($resumen['checklists'] ?? []) > 0)
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Checklists Completados</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($resumen['checklists'] as $checklist)
                    <div class="col-md-6">
                        <div class="card card-outline {{ $checklist['tipo'] == 'salida' ? 'card-primary' : 'card-success' }}">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    @if($checklist['tipo'] == 'salida')
                                        <i class="fas fa-sign-out-alt"></i> Checklist de Salida
                                    @else
                                        <i class="fas fa-clipboard-check"></i> Checklist de Entrega
                                    @endif
                                </h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $datos = is_string($checklist['datos']) ? json_decode($checklist['datos'], true) : $checklist['datos'];
                                @endphp
                                @if($datos)
                                    <ul class="list-unstyled mb-0">
                                        @foreach($datos as $key => $valor)
                                            <li>
                                                @if(is_bool($valor) || $valor === 'true' || $valor === true)
                                                    <i class="fas fa-check-circle text-success"></i>
                                                @elseif($valor === 'false' || $valor === false)
                                                    <i class="fas fa-times-circle text-danger"></i>
                                                @else
                                                    <i class="fas fa-info-circle text-info"></i>
                                                @endif
                                                {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                @if(!is_bool($valor) && $valor !== 'true' && $valor !== 'false')
                                                    : <strong>{{ $valor }}</strong>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Firmas -->
    <div class="card page-break-avoid">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-signature"></i> Firmas</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <div class="firma-box">
                        @php
                            $checklistSalida = collect($resumen['checklists'] ?? [])->where('tipo', 'salida')->first();
                        @endphp
                        @if($checklistSalida && isset($checklistSalida['firma_base64']))
                            <img src="{{ $checklistSalida['firma_base64'] }}" alt="Firma" style="max-height: 80px;">
                        @else
                            <div class="firma-linea"></div>
                        @endif
                    </div>
                    <p class="mt-2 mb-0"><strong>Transportista</strong></p>
                    <small>{{ $resumen['ruta']['transportista_nombre'] ?? 'N/A' }}</small>
                </div>
                <div class="col-md-4 text-center">
                    <div class="firma-box">
                        <div class="firma-linea"></div>
                    </div>
                    <p class="mt-2 mb-0"><strong>Supervisor de Planta</strong></p>
                    <small>Control de Salida</small>
                </div>
                <div class="col-md-4 text-center">
                    <div class="firma-box">
                        <div class="firma-linea"></div>
                    </div>
                    <p class="mt-2 mb-0"><strong>Receptor Final</strong></p>
                    <small>Última entrega</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="text-center text-muted mt-4">
        <small>
            Documento generado el {{ date('d/m/Y H:i') }} | 
            Sistema de Gestión de Rutas Multi-Entrega
        </small>
    </div>
</div>
@stop

@section('css')
<style>
    .badge-lg {
        font-size: 14px;
        padding: 8px 12px;
    }
    .firma-box {
        border: 1px dashed #ccc;
        padding: 20px;
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .firma-linea {
        width: 80%;
        border-bottom: 1px solid #333;
        height: 80px;
    }
    
    /* Estilos para impresión */
    @media print {
        .no-print {
            display: none !important;
        }
        .main-header, .main-sidebar, .main-footer {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
        .resumen-container {
            padding: 0 !important;
        }
        .page-break-avoid {
            page-break-inside: avoid;
        }
        @page {
            margin: 1cm;
        }
    }
</style>
@stop
