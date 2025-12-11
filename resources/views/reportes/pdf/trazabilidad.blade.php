<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Trazabilidad - {{ $envio->codigo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            color: #007bff;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 14px;
            color: #333;
        }
        .info-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-row strong {
            display: inline-block;
            width: 150px;
        }
        .estado-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .bg-success { background-color: #28a745; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-info { background-color: #17a2b8; }
        .bg-secondary { background-color: #6c757d; }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-top: 20px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #007bff;
        }
        .timeline {
            margin: 15px 0;
        }
        .timeline-event {
            margin-bottom: 15px;
            padding-left: 25px;
            position: relative;
            border-left: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .timeline-event:before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: #007bff;
            border: 2px solid white;
        }
        .timeline-event.success:before { background: #28a745; }
        .timeline-event.warning:before { background: #ffc107; }
        .timeline-event.danger:before { background: #dc3545; }
        .timeline-event.info:before { background: #17a2b8; }
        
        .timeline-time {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        .timeline-title {
            font-size: 11px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 3px;
        }
        .timeline-desc {
            font-size: 9px;
            color: #555;
            margin-bottom: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        table tfoot {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .summary-boxes {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
        }
        .summary-box {
            flex: 1;
            text-align: center;
            padding: 10px;
            margin: 0 5px;
            border-radius: 5px;
            color: white;
        }
        .summary-box .number {
            font-size: 16px;
            font-weight: bold;
        }
        .summary-box .label {
            font-size: 9px;
        }
        .firma-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .firma-box {
            text-align: center;
            width: 45%;
        }
        .firma-line {
            border-top: 2px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        .firma-stamp {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-5 { margin-bottom: 5px; }
        .mb-10 { margin-bottom: 10px; }
        .mt-10 { margin-top: 10px; }
    </style>
</head>
<body>
    <!-- ENCABEZADO -->
    <div class="header">
        <h1>REPORTE DE TRAZABILIDAD COMPLETA</h1>
        <h2>Sistema de Gestion Logistica - Planta Principal</h2>
        <p style="font-size: 9px; color: #666;">Generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- INFORMACIÓN DEL ENVÍO -->
    <div class="info-box">
        <div class="info-row">
            <strong>Codigo Envio:</strong> <span style="font-size: 12px; font-weight: bold; color: #007bff;">{{ $envio->codigo }}</span>
        </div>
        <div class="info-row">
            <strong>Origen:</strong> {{ $planta->nombre ?? 'N/A' }}
        </div>
        <div class="info-row">
            <strong>Destino:</strong> {{ $envio->almacenDestino->nombre ?? 'N/A' }}
        </div>
        <div class="info-row">
            <strong>Fecha Creacion:</strong> {{ \Carbon\Carbon::parse($envio->fecha_creacion)->format('d/m/Y H:i:s') }}
        </div>
        @if($envio->fecha_entrega)
        <div class="info-row">
            <strong>Fecha Entrega:</strong> {{ \Carbon\Carbon::parse($envio->fecha_entrega)->format('d/m/Y H:i:s') }}
        </div>
        @endif
        <div class="info-row">
            <strong>Estado Actual:</strong> 
            <span class="estado-badge bg-{{ $envio->estado == 'entregado' ? 'success' : ($envio->estado == 'en_transito' ? 'warning' : 'secondary') }}">
                {{ strtoupper(str_replace('_', ' ', $envio->estado)) }}
            </span>
        </div>
    </div>

    <!-- LÍNEA DE TIEMPO -->
    <div class="section-title">LINEA DE TIEMPO - HISTORIAL COMPLETO</div>
    <div class="timeline">
        @forelse($envio->historial as $evento)
        <div class="timeline-event {{ $evento->color }}">
            <div class="timeline-time">
                {{ \Carbon\Carbon::parse($evento->fecha_hora)->format('d/m/Y H:i:s') }}
            </div>
            <div class="timeline-title">
                {{ strtoupper(str_replace('_', ' ', $evento->evento)) }}
            </div>
            @if($evento->descripcion)
            <div class="timeline-desc">
                {{ $evento->descripcion }}
            </div>
            @endif
            @if($evento->usuario)
            <div class="timeline-desc">
                Por: {{ $evento->usuario->name }}
            </div>
            @endif
            @if($evento->datos_extra)
                @if(isset($evento->datos_extra['latitud']) && isset($evento->datos_extra['longitud']))
                <div class="timeline-desc">
                    Ubicacion: {{ $evento->datos_extra['latitud'] }}, {{ $evento->datos_extra['longitud'] }}
                </div>
                @endif
                @if(isset($evento->datos_extra['vehiculo']))
                <div class="timeline-desc">
                    Vehiculo: {{ $evento->datos_extra['vehiculo'] }}
                </div>
                @endif
            @endif
        </div>
        @empty
        <p class="text-center" style="color: #999;">No hay eventos registrados en el historial.</p>
        @endforelse
    </div>

    <!-- INCIDENTES (si existen) -->
    @if($incidentes->count() > 0)
    <div class="section-title">INCIDENTES REPORTADOS</div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Descripcion</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidentes as $inc)
            <tr>
                <td>{{ \Carbon\Carbon::parse($inc->created_at)->format('d/m/Y H:i') }}</td>
                <td>{{ ucfirst($inc->tipo_incidente) }}</td>
                <td>{{ $inc->descripcion }}</td>
                <td>{{ ucfirst($inc->estado) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- RESUMEN -->
    <div class="section-title">RESUMEN DE LA OPERACION</div>
    <table style="border: none; font-size: 10px;">
        <tr>
            <td style="border: none; width: 25%; background: #17a2b8; color: white; text-align: center; padding: 15px;">
                <div style="font-size: 16px; font-weight: bold;">{{ $tiempoTotal ?? 'N/A' }}</div>
                <div style="font-size: 9px;">Tiempo Total</div>
            </td>
            <td style="border: none; width: 25%; background: #ffc107; text-align: center; padding: 15px;">
                <div style="font-size: 16px; font-weight: bold;">{{ $tiempoTransito ?? 'N/A' }}</div>
                <div style="font-size: 9px;">En Transito</div>
            </td>
            <td style="border: none; width: 25%; background: #dc3545; color: white; text-align: center; padding: 15px;">
                <div style="font-size: 16px; font-weight: bold;">{{ $incidentes->count() }}</div>
                <div style="font-size: 9px;">Incidentes</div>
            </td>
            <td style="border: none; width: 25%; background: #28a745; color: white; text-align: center; padding: 15px;">
                <div style="font-size: 14px; font-weight: bold;">
                    @if($envio->estado == 'entregado') COMPLETADO
                    @else EN PROCESO
                    @endif
                </div>
                <div style="font-size: 9px;">Estado Final</div>
            </td>
        </tr>
    </table>

    <!-- PRODUCTOS -->
    <div class="section-title">PRODUCTOS DEL ENVIO</div>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Peso Unit.</th>
                <th>Total Peso</th>
                <th>Precio Unit.</th>
                <th>Total Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($envio->productos as $prod)
            <tr>
                <td>{{ $prod->producto_nombre }}</td>
                <td>{{ $prod->cantidad }}</td>
                <td>{{ number_format($prod->peso_unitario, 2) }} kg</td>
                <td><strong>{{ number_format($prod->total_peso, 2) }} kg</strong></td>
                <td>Bs {{ number_format($prod->precio_unitario, 2) }}</td>
                <td><strong>Bs {{ number_format($prod->total_precio, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>TOTALES:</strong></td>
                <td><strong>{{ number_format($envio->total_peso, 2) }} kg</strong></td>
                <td></td>
                <td><strong>Bs {{ number_format($envio->total_precio, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- FIRMAS -->
    <div class="firma-section">
        <div class="firma-box">
            <img src="{{ public_path('images/sello-planta.svg') }}" alt="Sello" class="firma-stamp">
            <div class="firma-line">
                <strong>SELLO OFICIAL</strong><br>
                <small>Planta Principal</small>
            </div>
        </div>
        <div class="firma-box">
            <img src="{{ public_path('images/firma-generica.svg') }}" alt="Firma" style="width: 120px; margin: 10px auto;">
            <div class="firma-line">
                <strong>FIRMA SUPERVISOR</strong><br>
                <small>Gerente de Operaciones</small>
            </div>
        </div>
    </div>

    <!-- PIE DE PÁGINA -->
    <div style="margin-top: 30px; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
        <p><strong>Sistema de Gestion Logistica - Planta Principal</strong></p>
        <p>Santa Cruz de la Sierra, Bolivia | Generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <p>Este documento es un reporte completo de trazabilidad del envio {{ $envio->codigo }}</p>
    </div>
</body>
</html>

