<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mis Incidentes Reportados</title>
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
            margin: 3px 0;
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
            color: #212529 !important; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        th { 
            background: #dc3545 !important; 
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
        .badge { 
            padding: 3px 6px; 
            border-radius: 3px; 
            font-size: 8px; 
            display: inline-block;
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>MIS INCIDENTES REPORTADOS</h1>
        <p>Transportista: {{ $transportista->name }}</p>
        <p>Período: {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</p>
        <p>Generado: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if(isset($estadisticas))
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
    @else
    <div class="stats-container">
        <div class="stat-box">
            <h3>{{ $incidentes->count() }}</h3>
            <p>Total Incidentes</p>
        </div>
        <div class="stat-box">
            <h3>{{ $incidentes->where('estado', 'pendiente')->count() }}</h3>
            <p>Pendientes</p>
        </div>
        <div class="stat-box">
            <h3>{{ $incidentes->where('estado', 'en_proceso')->count() }}</h3>
            <p>En Proceso</p>
        </div>
        <div class="stat-box">
            <h3>{{ $incidentes->where('estado', 'resuelto')->count() }}</h3>
            <p>Resueltos</p>
        </div>
    </div>
    @endif

    <h3>Detalle de Incidentes</h3>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Envío</th>
                <th>Almacén</th>
                <th>Descripción</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidentes as $inc)
            <tr>
                <td>{{ \Carbon\Carbon::parse($inc->fecha_reporte)->format('d/m/Y H:i') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $inc->tipo_incidente)) }}</td>
                <td>{{ $inc->envio_codigo ?? 'N/A' }}</td>
                <td>{{ $inc->almacen_nombre ?? 'N/A' }}</td>
                <td>{{ Str::limit($inc->descripcion ?? '', 60) }}</td>
                <td>
                    <span class="badge badge-{{ $inc->estado == 'resuelto' ? 'success' : ($inc->estado == 'en_proceso' ? 'info' : 'warning') }}">
                        {{ ucfirst(str_replace('_', ' ', $inc->estado)) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #6c757d !important;">
                    No se reportaron incidentes en este período
                </td>
            </tr>
            @endforelse
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
