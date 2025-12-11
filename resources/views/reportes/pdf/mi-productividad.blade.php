<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mi Productividad</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 10px; 
            color: #212529 !important; 
            background: #ffffff;
        }
        .header { 
            background: #17a2b8 !important; 
            color: #ffffff !important; 
            padding: 20px; 
            margin-bottom: 20px; 
        }
        .header h1 { 
            font-size: 18px; 
            margin-bottom: 5px; 
            color: #ffffff !important; 
        }
        .header p { 
            font-size: 10px; 
            color: #ffffff !important; 
            margin: 3px 0;
        }
        .stats-container { 
            display: table; 
            width: 100%; 
            margin-bottom: 20px; 
        }
        .stat-box { 
            display: table-cell; 
            width: 16.66%; 
            padding: 10px; 
            text-align: center; 
            background: #f8f9fa !important; 
            border: 1px solid #dee2e6; 
            vertical-align: top;
        }
        .stat-box h3 { 
            font-size: 16px; 
            color: #17a2b8 !important; 
            margin-bottom: 3px; 
        }
        .stat-box p { 
            font-size: 9px; 
            color: #212529 !important; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th { 
            background: #17a2b8 !important; 
            color: #ffffff !important; 
            padding: 8px 6px; 
            text-align: left; 
            font-size: 9px; 
            font-weight: bold;
        }
        td { 
            padding: 6px; 
            border-bottom: 1px solid #dee2e6; 
            font-size: 9px; 
            color: #212529 !important;
            background: #ffffff !important;
        }
        tr:nth-child(even) td { 
            background: #f8f9fa !important; 
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
            font-size: 12px;
            margin: 15px 0 10px;
        }
        strong {
            color: #212529 !important;
        }
        small {
            color: #212529 !important;
        }
        .chart-container {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa !important;
            border: 1px solid #dee2e6;
        }
        .chart-title {
            font-size: 12px;
            font-weight: bold;
            color: #212529 !important;
            margin-bottom: 15px;
            text-align: center;
        }
        .chart-bar-container {
            margin-bottom: 15px;
        }
        .chart-bar-label {
            font-size: 8px;
            color: #212529 !important;
            margin-bottom: 3px;
            display: flex;
            justify-content: space-between;
        }
        .chart-bar-wrapper {
            width: 100%;
            height: 20px;
            background: #e9ecef !important;
            border: 1px solid #dee2e6;
            position: relative;
            overflow: hidden;
        }
        .chart-bar {
            height: 100%;
            background: #17a2b8 !important;
            display: inline-block;
            vertical-align: top;
        }
        .chart-bar-entregados {
            background: #28a745 !important;
            float: left;
        }
        .chart-bar-pendientes {
            background: #ffc107 !important;
            float: left;
        }
        .chart-legend {
            margin-top: 10px;
            font-size: 8px;
            color: #212529 !important;
        }
        .legend-item {
            display: inline-block;
            margin-right: 15px;
        }
        .legend-color {
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 5px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MI PRODUCTIVIDAD</h1>
        <p>Transportista: {{ $transportista->name }}</p>
        <p>Período: {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</p>
        <p>Generado: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <h3>{{ $estadisticas->total_envios }}</h3>
            <p>Envíos Asignados</p>
        </div>
        <div class="stat-box">
            <h3>{{ $estadisticas->entregas_completadas }}</h3>
            <p>Completados</p>
        </div>
        <div class="stat-box">
            <h3>{{ $estadisticas->en_transito }}</h3>
            <p>En Tránsito</p>
        </div>
        <div class="stat-box">
            <h3>{{ $estadisticas->tasa_efectividad }}%</h3>
            <p>Efectividad</p>
        </div>
        <div class="stat-box">
            <h3>{{ number_format($estadisticas->total_peso_transportado, 0) }}</h3>
            <p>Kg Transportados</p>
        </div>
        <div class="stat-box">
            <h3>{{ $estadisticas->total_incidentes }}</h3>
            <p>Incidentes</p>
        </div>
    </div>

    @if(count($enviosPorMes) > 0)
    <div class="chart-container">
        <div class="chart-title">Gráfico de Envíos por Mes</div>
        
        @php
            $maxTotal = max(array_column($enviosPorMes, 'total'));
            if ($maxTotal == 0) $maxTotal = 1; // Evitar división por cero
        @endphp
        
        @foreach($enviosPorMes as $mes)
        <div class="chart-bar-container">
            <div class="chart-bar-label">
                <span><strong>{{ $mes['mes'] }}</strong></span>
                <span>Total: {{ $mes['total'] }} | Entregados: {{ $mes['entregados'] }} | Pendientes: {{ $mes['total'] - $mes['entregados'] }}</span>
            </div>
            <div class="chart-bar-wrapper">
                @if($mes['entregados'] > 0)
                <div class="chart-bar chart-bar-entregados" style="width: {{ ($mes['entregados'] / $maxTotal) * 100 }}%;"></div>
                @endif
                @if(($mes['total'] - $mes['entregados']) > 0)
                <div class="chart-bar chart-bar-pendientes" style="width: {{ (($mes['total'] - $mes['entregados']) / $maxTotal) * 100 }}%;"></div>
                @endif
            </div>
        </div>
        @endforeach
        
        <div class="chart-legend">
            <div class="legend-item">
                <span class="legend-color" style="background: #28a745 !important;"></span>
                <span>Entregados</span>
            </div>
            <div class="legend-item">
                <span class="legend-color" style="background: #ffc107 !important;"></span>
                <span>Pendientes</span>
            </div>
        </div>
    </div>
    
    <h3>Tabla de Envíos por Mes</h3>
    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th>Total Envíos</th>
                <th>Entregados</th>
                <th>Pendientes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enviosPorMes as $mes)
            <tr>
                <td>{{ $mes['mes'] }}</td>
                <td>{{ $mes['total'] }}</td>
                <td>{{ $mes['entregados'] }}</td>
                <td>{{ $mes['total'] - $mes['entregados'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <img src="{{ public_path('images/sello-planta.svg') }}" class="stamp-img" alt="Sello Oficial">
            <div class="signature-line">
                <strong>SELLO OFICIAL</strong><br>
                <small>Planta Principal</small>
            </div>
        </div>
        <div class="signature-box">
            <img src="{{ public_path('images/firma-generica.svg') }}" class="firma-img" alt="Firma Transportista">
            <div class="signature-line">
                <strong>TRANSPORTISTA</strong><br>
                <small>{{ $transportista->name }}</small>
            </div>
        </div>
    </div>

    <div class="footer">
        Sistema de Gestión Logística - Planta | Bolivia | Documento generado automáticamente
    </div>
</body>
</html>

