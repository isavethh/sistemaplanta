<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resolucion de Incidente #{{ $incidente->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        
        .document-header {
            border: 3px solid #28a745;
            padding: 15px;
            margin-bottom: 20px;
            position: relative;
        }
        .document-header h1 {
            color: #28a745;
            font-size: 18px;
            text-align: center;
            margin-bottom: 5px;
        }
        .document-header .subtitle {
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .document-number {
            position: absolute;
            top: 10px;
            right: 15px;
            background: #28a745;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            margin: 10px 0;
        }
        .status-resuelto { background: #28a745; color: white; }
        .status-en_proceso { background: #17a2b8; color: white; }
        .status-pendiente { background: #ffc107; color: #333; }
        
        .section { margin-bottom: 15px; }
        .section-title {
            background: #343a40;
            color: white;
            padding: 5px 10px;
            font-size: 11px;
            margin-bottom: 10px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            border: 1px solid #dee2e6;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .info-label {
            font-weight: bold;
            width: 30%;
            background: #f8f9fa;
        }
        
        .timeline {
            border-left: 3px solid #28a745;
            padding-left: 20px;
            margin-left: 10px;
        }
        .timeline-item {
            margin-bottom: 15px;
            position: relative;
        }
        .timeline-item:before {
            content: '●';
            position: absolute;
            left: -27px;
            background: white;
            color: #28a745;
            font-size: 18px;
        }
        .timeline-date {
            font-weight: bold;
            color: #28a745;
            font-size: 10px;
        }
        .timeline-content {
            background: #f8f9fa;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #dee2e6;
        }
        
        .resolution-box {
            background: #d4edda;
            border: 2px solid #28a745;
            padding: 15px;
            margin: 15px 0;
        }
        .resolution-box h3 {
            color: #155724;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 10px;
        }
        .stamp-img {
            width: 90px;
            height: 90px;
            margin: 0 auto;
            opacity: 0.7;
        }
        .firma-img {
            max-width: 110px;
            max-height: 45px;
            margin: 10px auto;
            display: block;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 5px;
            font-size: 10px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="document-header">
        <div class="document-number">INC-{{ str_pad($incidente->id, 5, '0', STR_PAD_LEFT) }}</div>
        <h1>ACTA DE RESOLUCIÓN DE INCIDENTE</h1>
        <div class="subtitle">DOCUMENTO OFICIAL DE CIERRE</div>
        <div class="subtitle">Sistema de Gestión Logística - Planta</div>
        <div style="text-align: center; margin-top: 10px;">
            <span class="status-badge status-{{ $incidente->estado }}">
                {{ strtoupper(str_replace('_', ' ', $incidente->estado)) }}
            </span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATOS DEL INCIDENTE</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Número de Incidente:</div>
                <div class="info-cell">INC-{{ str_pad($incidente->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="info-cell info-label">Tipo de Incidente:</div>
                <div class="info-cell">{{ ucfirst(str_replace('_', ' ', $incidente->tipo_incidente)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Envío Afectado:</div>
                <div class="info-cell">{{ $envio->codigo }}</div>
                <div class="info-cell info-label">Almacén Destino:</div>
                <div class="info-cell">{{ $almacen->nombre ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Transportista:</div>
                <div class="info-cell">{{ $transportista->name ?? 'N/A' }}</div>
                <div class="info-cell info-label">Vehículo:</div>
                <div class="info-cell">{{ $vehiculo->placa ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DESCRIPCIÓN DEL INCIDENTE</div>
        <div style="padding: 10px; background: #fff3cd; border: 1px solid #ffc107;">
            {{ $incidente->descripcion }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">CRONOLOGÍA</div>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-date">{{ \Carbon\Carbon::parse($incidente->fecha_reporte)->format('d/m/Y H:i') }}</div>
                <div class="timeline-content">
                    <strong>REPORTE INICIAL</strong><br>
                    Incidente reportado por el transportista {{ $transportista->name ?? 'N/A' }}<br>
                    Tipo: {{ ucfirst(str_replace('_', ' ', $incidente->tipo_incidente)) }}
                </div>
            </div>
            
            @if($incidente->estado === 'resuelto' && $incidente->fecha_resolucion)
            <div class="timeline-item">
                <div class="timeline-date">{{ \Carbon\Carbon::parse($incidente->fecha_resolucion)->format('d/m/Y H:i') }}</div>
                <div class="timeline-content">
                    <strong>RESOLUCIÓN CONFIRMADA</strong><br>
                    Incidente cerrado satisfactoriamente.<br>
                    Tiempo de resolución: {{ \Carbon\Carbon::parse($incidente->fecha_reporte)->diffInDays(\Carbon\Carbon::parse($incidente->fecha_resolucion)) }} día(s)
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($incidente->estado === 'resuelto' && $incidente->notas_resolucion)
    <div class="resolution-box">
        <h3>RESOLUCIÓN Y ACCIONES TOMADAS</h3>
        <p>{{ $incidente->notas_resolucion }}</p>
        
        @if($incidente->fecha_resolucion)
        <p style="margin-top: 10px; font-size: 10px;">
            <strong>Fecha de resolución:</strong> {{ \Carbon\Carbon::parse($incidente->fecha_resolucion)->format('d/m/Y H:i') }}
        </p>
        @endif
    </div>
    @endif

    <div class="section">
        <div class="section-title">RESUMEN ADMINISTRATIVO</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Impacto en Entrega:</div>
                <div class="info-cell">
                    @php
                        $tiempoRetraso = $incidente->fecha_resolucion && $envio->fecha_estimada_entrega 
                            ? \Carbon\Carbon::parse($envio->fecha_estimada_entrega)->diffInHours(\Carbon\Carbon::parse($incidente->fecha_resolucion))
                            : 0;
                    @endphp
                    {{ $tiempoRetraso > 0 ? "Retraso de {$tiempoRetraso} hora(s)" : 'Sin retraso significativo' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Estado del Envío:</div>
                <div class="info-cell">{{ ucfirst(str_replace('_', ' ', $envio->estado)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Observaciones Adicionales:</div>
                <div class="info-cell">{{ $envio->observaciones ?? 'Ninguna' }}</div>
            </div>
        </div>
    </div>

    <div style="background: #e7f3ff; border-left: 4px solid #007bff; padding: 10px; margin: 15px 0; font-size: 9px;">
        <strong>NOTA LEGAL:</strong> Este documento certifica que el incidente INC-{{ str_pad($incidente->id, 5, '0', STR_PAD_LEFT) }} 
        ha sido gestionado conforme a los protocolos de seguridad y calidad establecidos. 
        Todas las partes involucradas han sido notificadas y se han tomado las medidas correctivas necesarias.
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <img src="{{ public_path('images/sello-planta.svg') }}" class="stamp-img" alt="Sello Oficial">
            <div class="signature-line">
                <strong>SELLO OFICIAL</strong><br>
                <small>Planta Principal</small>
            </div>
        </div>
        <div class="signature-box">
            <img src="{{ public_path('images/firma-generica.svg') }}" class="firma-img" alt="Firma Supervisor">
            <div class="signature-line">
                <strong>SUPERVISOR DE OPERACIONES</strong><br>
                <small>Responsable de Resolución</small>
            </div>
        </div>
        <div class="signature-box">
            <img src="{{ public_path('images/firma-generica.svg') }}" class="firma-img" alt="Firma Transportista">
            <div class="signature-line">
                <strong>TRANSPORTISTA</strong><br>
                <small>{{ $transportista->name ?? 'N/A' }}</small>
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Sistema de Gestión Logística - Planta</strong></p>
        <p>Bolivia | Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Este documento certifica la resolución oficial del incidente según protocolos internos</p>
    </div>
</body>
</html>

