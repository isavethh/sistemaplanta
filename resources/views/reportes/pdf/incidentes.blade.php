<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Incidentes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 10px; 
            color: #212529 !important; 
            background: #ffffff;
        }
        .header { 
            background: #dc3545 !important; 
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
            font-size: 16px; 
            color: #dc3545 !important; 
            margin-bottom: 3px; 
        }
        .stat-box p { 
            font-size: 9px; 
            color: #495057 !important; 
        }
        .section-title { 
            background: #343a40 !important; 
            color: #ffffff !important; 
            padding: 8px 10px; 
            font-size: 11px; 
            margin: 15px 0 10px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th { 
            background: #dc3545 !important; 
            color: #ffffff !important; 
            padding: 6px; 
            text-align: left; 
            font-size: 9px; 
        }
        td { 
            padding: 5px 6px; 
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
            padding: 2px 5px; 
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
        .tipo-list { 
            list-style: none; 
            padding: 0; 
        }
        .tipo-list li { 
            padding: 5px 10px; 
            border-bottom: 1px solid #dee2e6; 
            display: flex; 
            justify-content: space-between; 
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
        strong {
            color: #212529 !important;
        }
        small {
            color: #212529 !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE INCIDENTES DE TRANSPORTE</h1>
        <p>Sistema de Gestión Logística - Planta</p>
        <p>Período: {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</p>
        <p>Generado: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <h3>{{ $estadisticas['total'] }}</h3>
            <p>Total Incidentes</p>
        </div>
        <div class="stat-box">
            <h3>{{ $estadisticas['pendientes'] }}</h3>
            <p>Pendientes</p>
        </div>
        <div class="stat-box">
            <h3>{{ $estadisticas['en_proceso'] }}</h3>
            <p>En Proceso</p>
        </div>
        <div class="stat-box">
            <h3>{{ $estadisticas['resueltos'] }}</h3>
            <p>Resueltos</p>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-box" style="width: 100%;">
            <h3>{{ $estadisticas['tiempo_promedio_resolucion'] }}</h3>
            <p>Tiempo Promedio de Resolución</p>
        </div>
    </div>

    <div class="section-title">Distribución por Tipo de Incidente</div>
    <ul class="tipo-list">
        @foreach($porTipo as $tipo)
        <li>
            <span>{{ ucfirst(str_replace('_', ' ', $tipo->tipo_incidente)) }}</span>
            <span class="badge badge-danger">{{ $tipo->total }}</span>
        </li>
        @endforeach
    </ul>

    <div class="section-title">Detalle de Incidentes</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha Reporte</th>
                <th>Tipo</th>
                <th>Envío</th>
                <th>Almacén</th>
                <th>Transportista</th>
                <th>Estado</th>
                <th>Resolución</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidentes as $inc)
            <tr>
                <td>#{{ $inc->id }}</td>
                <td>{{ \Carbon\Carbon::parse($inc->fecha_reporte)->format('d/m/Y H:i') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $inc->tipo_incidente)) }}</td>
                <td>{{ $inc->envio_codigo ?? 'N/A' }}</td>
                <td>{{ $inc->almacen_nombre ?? 'N/A' }}</td>
                <td>{{ $inc->transportista_nombre ?? 'N/A' }}</td>
                <td>
                    <span class="badge badge-{{ $inc->estado == 'resuelto' ? 'success' : ($inc->estado == 'en_proceso' ? 'info' : 'warning') }}">
                        {{ ucfirst(str_replace('_', ' ', $inc->estado)) }}
                    </span>
                </td>
                <td>{{ $inc->fecha_resolucion ? \Carbon\Carbon::parse($inc->fecha_resolucion)->format('d/m/Y') : '-' }}</td>
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
                <small>Jefe de Seguridad y Calidad</small>
            </div>
        </div>
    </div>

    <div class="footer">
        Sistema de Gestión Logística - Planta | Bolivia | Documento generado automáticamente
    </div>
</body>
</html>
