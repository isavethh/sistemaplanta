@extends('adminlte::page')

@section('title', 'Resolución de Incidente')

@section('content_header')
    <h1><i class="fas fa-check-circle text-success"></i> Resolución de Incidente</h1>
@stop

@section('content')
@include('layouts.preloader-killer')

<div class="card">
    <div class="card-header bg-success">
        <h3 class="card-title">
            <i class="fas fa-file-contract"></i> Documento Oficial de Resolución
        </h3>
        <div class="card-tools">
            <a href="{{ route('reportes.resolucion-incidente.pdf', $incidente->id) }}" 
               class="btn btn-light btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
            <a href="{{ route('reportes.incidentes') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    <div class="card-body" style="background: white;">
        <div style="max-width: 900px; margin: 0 auto; padding: 20px;">
            
            <!-- Encabezado -->
            <div style="border: 3px solid #28a745; padding: 20px; margin-bottom: 30px; text-align: center;">
                <h2 style="color: #28a745; margin-bottom: 10px;">✅ ACTA DE RESOLUCIÓN DE INCIDENTE</h2>
                <p style="color: #666;">DOCUMENTO OFICIAL DE CIERRE</p>
                <p style="color: #666; font-size: 14px;">Sistema de Gestión Logística - Planta</p>
                <div style="margin-top: 15px;">
                    @if($incidente->estado === 'resuelto')
                        <span class="badge badge-success badge-lg">RESUELTO</span>
                    @elseif($incidente->estado === 'en_proceso')
                        <span class="badge badge-info badge-lg">EN PROCESO</span>
                    @else
                        <span class="badge badge-warning badge-lg">PENDIENTE</span>
                    @endif
                </div>
            </div>

            <!-- Datos del Incidente -->
            <div class="mb-4">
                <h4 class="bg-dark text-white p-2">📋 DATOS DEL INCIDENTE</h4>
                <table class="table table-bordered">
                    <tr>
                        <td class="bg-light font-weight-bold" width="25%">Número de Incidente:</td>
                        <td>INC-{{ str_pad($incidente->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="bg-light font-weight-bold" width="25%">Tipo:</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $incidente->tipo_incidente)) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-light font-weight-bold">Envío Afectado:</td>
                        <td>{{ $envio->codigo }}</td>
                        <td class="bg-light font-weight-bold">Almacén Destino:</td>
                        <td>{{ $almacen->nombre ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="bg-light font-weight-bold">Transportista:</td>
                        <td>{{ $transportista->name ?? 'N/A' }}</td>
                        <td class="bg-light font-weight-bold">Vehículo:</td>
                        <td>{{ $vehiculo->placa ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Descripción -->
            <div class="mb-4">
                <h4 class="bg-dark text-white p-2">📝 DESCRIPCIÓN DEL INCIDENTE</h4>
                <div class="alert alert-warning">
                    {{ $incidente->descripcion }}
                </div>
            </div>

            <!-- Cronología -->
            <div class="mb-4">
                <h4 class="bg-dark text-white p-2">⏰ CRONOLOGÍA</h4>
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-warning">{{ \Carbon\Carbon::parse($incidente->fecha_reporte)->format('d M Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-exclamation-triangle bg-warning"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($incidente->fecha_reporte)->format('H:i') }}</span>
                            <h3 class="timeline-header">🚨 REPORTE INICIAL</h3>
                            <div class="timeline-body">
                                Incidente reportado por el transportista {{ $transportista->name ?? 'N/A' }}<br>
                                Tipo: {{ ucfirst(str_replace('_', ' ', $incidente->tipo_incidente)) }}
                            </div>
                        </div>
                    </div>
                    
                    @if($incidente->estado === 'resuelto' && $incidente->fecha_resolucion)
                    <div class="time-label">
                        <span class="bg-success">{{ \Carbon\Carbon::parse($incidente->fecha_resolucion)->format('d M Y') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-check-circle bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($incidente->fecha_resolucion)->format('H:i') }}</span>
                            <h3 class="timeline-header">✅ RESOLUCIÓN CONFIRMADA</h3>
                            <div class="timeline-body">
                                Incidente cerrado satisfactoriamente.<br>
                                Tiempo de resolución: {{ \Carbon\Carbon::parse($incidente->fecha_reporte)->diffInDays(\Carbon\Carbon::parse($incidente->fecha_resolucion)) }} día(s)
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>

            <!-- Resolución -->
            @if($incidente->estado === 'resuelto' && $incidente->notas_resolucion)
            <div class="mb-4">
                <div class="alert alert-success" style="border: 2px solid #28a745;">
                    <h5 class="alert-heading">✅ RESOLUCIÓN Y ACCIONES TOMADAS</h5>
                    <p>{{ $incidente->notas_resolucion }}</p>
                    @if($incidente->fecha_resolucion)
                    <hr>
                    <p class="mb-0"><small><strong>Fecha de resolución:</strong> {{ \Carbon\Carbon::parse($incidente->fecha_resolucion)->format('d/m/Y H:i') }}</small></p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Resumen Administrativo -->
            <div class="mb-4">
                <h4 class="bg-dark text-white p-2">📊 RESUMEN ADMINISTRATIVO</h4>
                <table class="table table-bordered">
                    <tr>
                        <td class="bg-light font-weight-bold">Impacto en Entrega:</td>
                        <td>
                            @php
                                $tiempoRetraso = $incidente->fecha_resolucion && $envio->fecha_estimada_entrega 
                                    ? \Carbon\Carbon::parse($envio->fecha_estimada_entrega)->diffInHours(\Carbon\Carbon::parse($incidente->fecha_resolucion))
                                    : 0;
                            @endphp
                            {{ $tiempoRetraso > 0 ? "Retraso de {$tiempoRetraso} hora(s)" : 'Sin retraso significativo' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="bg-light font-weight-bold">Estado del Envío:</td>
                        <td>
                            @if($envio->estado === 'entregado')
                                <span class="badge badge-success">{{ ucfirst(str_replace('_', ' ', $envio->estado)) }}</span>
                            @elseif($envio->estado === 'en_transito')
                                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $envio->estado)) }}</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $envio->estado)) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="bg-light font-weight-bold">Observaciones Adicionales:</td>
                        <td>{{ $envio->observaciones ?? 'Ninguna' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Nota Legal -->
            <div class="alert alert-info">
                <strong>📌 NOTA LEGAL:</strong> Este documento certifica que el incidente INC-{{ str_pad($incidente->id, 5, '0', STR_PAD_LEFT) }} 
                ha sido gestionado conforme a los protocolos de seguridad y calidad establecidos. 
                Todas las partes involucradas han sido notificadas y se han tomado las medidas correctivas necesarias.
            </div>

            <!-- Firmas (simuladas para vista web) -->
            <div class="row mt-5">
                <div class="col-4 text-center">
                    <img src="{{ asset('images/sello-planta.svg') }}" style="width: 90px; opacity: 0.7;">
                    <hr>
                    <strong>SELLO OFICIAL</strong><br>
                    <small>Planta Principal</small>
                </div>
                <div class="col-4 text-center">
                    <img src="{{ asset('images/firma-generica.svg') }}" style="max-width: 110px; max-height: 45px;">
                    <hr>
                    <strong>SUPERVISOR DE OPERACIONES</strong><br>
                    <small>Responsable de Resolución</small>
                </div>
                <div class="col-4 text-center">
                    <img src="{{ asset('images/firma-generica.svg') }}" style="max-width: 110px; max-height: 45px;">
                    <hr>
                    <strong>TRANSPORTISTA</strong><br>
                    <small>{{ $transportista->name ?? 'N/A' }}</small>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-5 pt-3" style="border-top: 1px solid #ddd; font-size: 12px; color: #999;">
                <p><strong>Sistema de Gestión Logística - Planta</strong></p>
                <p>Bolivia | Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
                <p><em>Este documento certifica la resolución oficial del incidente según protocolos internos</em></p>
            </div>

        </div>
    </div>
</div>

@stop

@section('css')
<style>
.badge-lg {
    font-size: 1.1rem;
    padding: 8px 20px;
}
</style>
@stop

