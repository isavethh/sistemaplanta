<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productividad</title>
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
            padding: 25px; 
            margin-bottom: 20px; 
        }
        .header h1 { 
            font-size: 20px; 
            margin-bottom: 8px; 
            color: #ffffff !important; 
            font-weight: bold;
        }
        .header p { 
            font-size: 10px; 
            color: #ffffff !important; 
            margin: 3px 0;
        }
        .header-info {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }
        .header-info-item {
            margin: 5px 0;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            margin-bottom: 15px;
        }
        th { 
            background: #17a2b8 !important; 
            color: #ffffff !important; 
            padding: 10px 8px; 
            text-align: left; 
            font-size: 9px; 
            font-weight: bold;
            border: 1px solid #138496;
        }
        td { 
            padding: 8px; 
            border: 1px solid #dee2e6; 
            font-size: 9px; 
            color: #212529 !important;
            background: #ffffff !important;
        }
        tr:nth-child(even) td { 
            background: #f8f9fa !important; 
            color: #212529 !important;
        }
        .text-right { 
            text-align: right; 
        }
        .text-center { 
            text-align: center; 
        }
        .badge { 
            padding: 3px 8px; 
            border-radius: 4px; 
            font-size: 8px; 
            display: inline-block;
            font-weight: bold;
        }
        .badge-gold { 
            background: #ffc107 !important; 
            color: #212529 !important; 
        }
        .badge-silver { 
            background: #adb5bd !important; 
            color: #212529 !important; 
        }
        .badge-bronze { 
            background: #cd7f32 !important; 
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
        .badge-info {
            background: #17a2b8 !important;
            color: #ffffff !important;
        }
        .progress-bar { 
            background: #e9ecef !important; 
            height: 18px; 
            border-radius: 4px; 
            overflow: hidden; 
            border: 1px solid #dee2e6;
        }
        .progress-fill { 
            height: 100%; 
            text-align: center; 
            color: #ffffff !important; 
            font-size: 8px; 
            line-height: 18px; 
            font-weight: bold;
        }
        .progress-success { 
            background: #28a745 !important; 
        }
        .progress-warning { 
            background: #ffc107 !important; 
            color: #212529 !important;
        }
        .progress-danger { 
            background: #dc3545 !important; 
        }
        .stats-box {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa !important;
            border: 2px solid #17a2b8;
        }
        .stats-box h4 {
            font-size: 12px;
            margin-bottom: 10px;
            color: #17a2b8 !important;
            font-weight: bold;
        }
        .stats-grid {
            display: table;
            width: 100%;
        }
        .stats-row {
            display: table-row;
        }
        .stats-item {
            display: table-cell;
            padding: 8px;
            background: #ffffff !important;
            border: 1px solid #dee2e6;
            width: 50%;
            vertical-align: top;
        }
        .stats-item strong {
            display: block;
            color: #17a2b8 !important;
            font-size: 9px;
        }
        .stats-item span {
            display: block;
            font-size: 11px;
            color: #212529 !important;
            font-weight: bold;
            margin-top: 3px;
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
        h3 {
            color: #212529 !important;
            font-size: 13px;
            margin: 15px 0 10px;
        }
        h4 {
            color: #212529 !important;
        }
        p {
            color: #212529 !important;
        }
        .ranking-badge {
            font-weight: bold;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE PRODUCTIVIDAD DE TRANSPORTISTAS</h1>
        <p>Sistema de Gestión Logística - Planta</p>
        <div class="header-info">
            <div class="header-info-item">
                <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</p>
            </div>
            <div class="header-info-item">
                <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>
    </div>

    <h3>Ranking de Transportistas por Desempeño</h3>
    
    <table>
        <thead>
            <tr>
                <th style="width: 35px;">#</th>
                <th>Transportista</th>
                <th>Email</th>
                <th class="text-center" style="width: 70px;">Total Envíos</th>
                <th class="text-center" style="width: 70px;">Completados</th>
                <th class="text-center" style="width: 70px;">En Tránsito</th>
                <th class="text-center" style="width: 60px;">Incidentes</th>
                <th class="text-center" style="width: 120px;">Efectividad</th>
                <th class="text-right" style="width: 80px;">Peso (kg)</th>
                <th class="text-right" style="width: 70px;">Items</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transportistas as $index => $t)
            <tr>
                <td class="text-center">
                    @if($index == 0)
                        <span class="badge badge-gold ranking-badge">1</span>
                    @elseif($index == 1)
                        <span class="badge badge-silver ranking-badge">2</span>
                    @elseif($index == 2)
                        <span class="badge badge-bronze ranking-badge">3</span>
                    @else
                        <strong>{{ $index + 1 }}</strong>
                    @endif
                </td>
                <td><strong>{{ $t->name }}</strong></td>
                <td><small>{{ $t->email }}</small></td>
                <td class="text-center">
                    <span class="badge badge-info">{{ $t->total_envios }}</span>
                </td>
                <td class="text-center">
                    <span class="badge badge-success">{{ $t->entregas_completadas }}</span>
                </td>
                <td class="text-center">
                    <span class="badge badge-info">{{ $t->en_transito ?? 0 }}</span>
                </td>
                <td class="text-center">
                    @if(($t->total_incidentes ?? 0) > 0)
                        <span class="badge badge-danger">{{ $t->total_incidentes }}</span>
                    @else
                        <span class="badge badge-success">0</span>
                    @endif
                </td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill {{ $t->tasa_efectividad >= 80 ? 'progress-success' : ($t->tasa_efectividad >= 50 ? 'progress-warning' : 'progress-danger') }}" 
                             style="width: {{ max($t->tasa_efectividad, 5) }}%;">
                            {{ $t->tasa_efectividad }}%
                        </div>
                    </div>
                </td>
                <td class="text-right"><strong>{{ number_format($t->total_peso_transportado, 1) }}</strong></td>
                <td class="text-right">{{ number_format($t->total_items_transportados ?? 0, 0) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px;">
                    <p style="color: #6c757d !important;">No hay datos de transportistas en el período seleccionado</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(isset($estadisticasGlobales) && $transportistas->isNotEmpty())
    <div class="stats-box">
        <h4>Resumen del Período</h4>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-item">
                    <strong>Total Transportistas Activos</strong>
                    <span>{{ $estadisticasGlobales['total_transportistas'] }}</span>
                </div>
                <div class="stats-item">
                    <strong>Total Envíos Gestionados</strong>
                    <span>{{ $estadisticasGlobales['total_envios_periodo'] }}</span>
                </div>
            </div>
            <div class="stats-row">
                <div class="stats-item">
                    <strong>Total Entregas Completadas</strong>
                    <span>{{ $estadisticasGlobales['total_entregas'] }}</span>
                </div>
                <div class="stats-item">
                    <strong>Total En Tránsito</strong>
                    <span>{{ $estadisticasGlobales['total_en_transito'] ?? 0 }}</span>
                </div>
            </div>
            <div class="stats-row">
                <div class="stats-item">
                    <strong>Total Incidentes</strong>
                    <span>{{ $estadisticasGlobales['total_incidentes'] ?? 0 }}</span>
                </div>
                <div class="stats-item">
                    <strong>Peso Total Transportado</strong>
                    <span>{{ number_format($estadisticasGlobales['total_peso'] ?? 0, 1) }} kg</span>
                </div>
            </div>
            <div class="stats-row">
                <div class="stats-item">
                    <strong>Items Total Transportados</strong>
                    <span>{{ number_format($estadisticasGlobales['total_items'] ?? 0, 0) }}</span>
                </div>
                <div class="stats-item">
                    <strong>Tasa Efectividad Global</strong>
                    <span>{{ $estadisticasGlobales['tasa_efectividad_global'] }}%</span>
                </div>
            </div>
        </div>
    </div>
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
            <img src="{{ public_path('images/firma-generica.svg') }}" class="firma-img" alt="Firma Autorizada">
            <div class="signature-line">
                <strong>FIRMA AUTORIZADA</strong><br>
                <small>Gerente de Recursos Humanos</small>
            </div>
        </div>
    </div>

    <div class="footer">
        Sistema de Gestión Logística - Planta | Bolivia | Documento generado automáticamente
    </div>
</body>
</html>
