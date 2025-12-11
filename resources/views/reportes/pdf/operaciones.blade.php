<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Operaciones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 10px; 
            color: #212529 !important; 
            background: #ffffff;
        }
        .header { 
            background: #007bff !important; 
            color: #ffffff !important; 
            padding: 20px; 
            margin-bottom: 20px; 
        }
        .header h1 { 
            font-size: 20px; 
            margin-bottom: 5px; 
            color: #ffffff !important; 
        }
        .header p { 
            font-size: 11px; 
            color: #ffffff !important; 
            opacity: 0.95;
        }
        .stats-container { 
            display: table; 
            width: 100%; 
            margin-bottom: 20px; 
        }
        .stat-box { 
            display: table-cell; 
            width: 25%; 
            padding: 10px; 
            text-align: center; 
            background: #f8f9fa !important; 
            border: 1px solid #dee2e6; 
        }
        .stat-box h3 { 
            font-size: 18px; 
            color: #007bff !important; 
            margin-bottom: 3px; 
        }
        .stat-box p { 
            font-size: 9px; 
            color: #495057 !important; 
            text-transform: uppercase; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th { 
            background: #343a40 !important; 
            color: #ffffff !important; 
            padding: 8px 5px; 
            text-align: left; 
            font-size: 9px; 
        }
        td { 
            padding: 6px 5px; 
            border-bottom: 1px solid #dee2e6; 
            font-size: 9px; 
            color: #212529 !important;
        }
        tr:nth-child(even) { 
            background: #f8f9fa !important; 
        }
        tr:nth-child(even) td {
            color: #212529 !important;
        }
        .badge { 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-size: 8px; 
            display: inline-block;
        }
        .badge-warning { 
            background: #ffc107 !important; 
            color: #212529 !important; 
        }
        .badge-info { 
            background: #17a2b8 !important; 
            color: #ffffff !important; 
        }
        .badge-success { 
            background: #28a745 !important; 
            color: #ffffff !important; 
        }
        .badge-danger { 
            background: #dc3545 !important; 
            color: #ffffff !important; 
        }
        .badge-primary { 
            background: #007bff !important; 
            color: #ffffff !important; 
        }
        .text-right { 
            text-align: right; 
        }
        strong {
            color: #212529 !important;
        }
        .footer { 
            position: fixed; 
            bottom: 10px; 
            left: 0; 
            right: 0; 
            text-align: center; 
            font-size: 8px; 
            color: #6c757d !important; 
        }
        .page-break { 
            page-break-after: always; 
        }
        .signature-section { 
            margin-top: 40px; 
            display: table; 
            width: 100%; 
        }
        .signature-box { 
            display: table-cell; 
            width: 50%; 
            text-align: center; 
            padding: 10px; 
        }
        .stamp-img { 
            width: 80px; 
            height: 80px; 
            margin: 0 auto; 
            opacity: 0.7; 
        }
        .firma-img { 
            max-width: 100px; 
            max-height: 40px; 
            margin: 10px auto; 
            display: block; 
        }
        .signature-line { 
            border-top: 1px solid #212529; 
            margin-top: 30px; 
            padding-top: 5px; 
            font-size: 9px; 
            color: #212529 !important;
        }
        h3 {
            color: #212529 !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE OPERACIONES DE TRANSPORTE</h1>
        <p>Sistema de Gestión Logística - Planta</p>
        <p>Período: {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</p>
        <p>Generado: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <h3>{{ number_format($estadisticas['total_envios']) }}</h3>
            <p>Total Envíos</p>
        </div>
        <div class="stat-box">
            <h3>{{ number_format($estadisticas['entregados']) }}</h3>
            <p>Entregados</p>
        </div>
        <div class="stat-box">
            <h3>{{ number_format($estadisticas['total_peso'], 1) }} kg</h3>
            <p>Peso Total</p>
        </div>
        <div class="stat-box">
            <h3>Bs {{ number_format($estadisticas['total_valor'], 2) }}</h3>
            <p>Valor Total</p>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <h3>{{ $estadisticas['pendientes'] }}</h3>
            <p>Pendientes</p>
        </div>
        <div class="stat-box">
            <h3>{{ $estadisticas['en_transito'] }}</h3>
            <p>En Tránsito</p>
        </div>
        <div class="stat-box">
            <h3>{{ $estadisticas['cancelados'] }}</h3>
            <p>Cancelados</p>
        </div>
        <div class="stat-box">
            <h3>{{ number_format($estadisticas['total_items']) }}</h3>
            <p>Total Items</p>
        </div>
    </div>

    <h3 style="margin: 15px 0 10px; font-size: 12px;">Detalle de Envíos</h3>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Almacén</th>
                <th>Transportista</th>
                <th>Vehículo</th>
                <th class="text-right">Cant.</th>
                <th class="text-right">Peso (kg)</th>
                <th class="text-right">Valor (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($envios as $envio)
            <tr>
                <td><strong>{{ $envio->codigo }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($envio->fecha_creacion)->format('d/m/Y') }}</td>
                <td>
                    <span class="badge badge-{{ $envio->estado == 'entregado' ? 'success' : ($envio->estado == 'en_transito' ? 'info' : ($envio->estado == 'pendiente' ? 'warning' : 'danger')) }}">
                        {{ ucfirst(str_replace('_', ' ', $envio->estado)) }}
                    </span>
                </td>
                <td>{{ $envio->almacen_nombre ?? 'N/A' }}</td>
                <td>{{ $envio->transportista_nombre ?? 'Sin asignar' }}</td>
                <td>{{ $envio->vehiculo_placa ?? '-' }}</td>
                <td class="text-right">{{ number_format($envio->total_cantidad) }}</td>
                <td class="text-right">{{ number_format($envio->total_peso, 2) }}</td>
                <td class="text-right">{{ number_format($envio->total_precio, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <img src="{{ public_path('images/sello-planta.svg') }}" class="stamp-img" alt="Sello Oficial">
            <div class="signature-line">
                <strong>SELLO OFICIAL</strong><br>
                <small>Planta Principal</small>
            </div>
        </div>
        <div class="signature-box">
            <img src="{{ public_path('images/firma-generica.svg') }}" class="firma-img" alt="Firma Autorizada">
            <div class="signature-line">
                <strong>FIRMA AUTORIZADA</strong><br>
                <small>Gerente de Operaciones</small>
            </div>
        </div>
    </div>

    <div class="footer">
        Sistema de Gestión Logística - Planta | Bolivia | Documento generado automáticamente
    </div>
</body>
</html>

