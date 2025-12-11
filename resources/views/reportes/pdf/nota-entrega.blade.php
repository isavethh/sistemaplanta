<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota de Entrega - {{ $envio->codigo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        
        .document-header {
            border: 2px solid #28a745;
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
        
        .legal-notice {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 9px;
        }
        
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
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 5px;
            border-bottom: 1px solid #dee2e6;
        }
        .info-label {
            font-weight: bold;
            width: 30%;
            background: #f8f9fa;
        }
        
        .two-columns {
            display: table;
            width: 100%;
        }
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th { background: #28a745; color: white; padding: 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border: 1px solid #dee2e6; font-size: 10px; }
        .text-right { text-align: right; }
        
        .totals-box {
            background: #f8f9fa;
            border: 1px solid #28a745;
            padding: 10px;
            margin-top: 15px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        
        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 10px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
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
        
        .stamp-box {
            width: 100px;
            height: 100px;
            margin: 0 auto;
        }
        .stamp-box img {
            width: 100%;
            height: 100%;
            opacity: 0.7;
        }
        .firma-img {
            max-width: 120px;
            max-height: 50px;
            margin: 0 auto;
            display: block;
        }
    </style>
</head>
<body>
    <div class="document-header">
        <div class="document-number">N° {{ $envio->codigo }}</div>
        <h1>NOTA DE ENTREGA</h1>
        <div class="subtitle">DOCUMENTO DE RECEPCIÓN DE MERCANCÍAS</div>
        <div class="subtitle">Sistema de Gestión Logística - Planta</div>
    </div>

    <div class="legal-notice">
        <strong>AVISO LEGAL:</strong> Este documento constituye constancia de la recepción de mercancías conforme 
        al Código de Comercio de Bolivia (Art. 815-819). La firma del receptor confirma la recepción en 
        conformidad de los productos detallados.
    </div>

    <div class="two-columns">
        <div class="column">
            <div class="section">
                <div class="section-title">DATOS DEL REMITENTE (PLANTA)</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell info-label">Empresa:</div>
                        <div class="info-cell">{{ $planta->nombre ?? 'Planta Principal' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Dirección:</div>
                        <div class="info-cell">{{ $planta->direccion_completa ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Coordenadas:</div>
                        <div class="info-cell">{{ $planta->latitud ?? 'N/A' }}, {{ $planta->longitud ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="section">
                <div class="section-title">DATOS DEL DESTINATARIO</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell info-label">Almacén:</div>
                        <div class="info-cell">{{ $envio->almacen_nombre }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Dirección:</div>
                        <div class="info-cell">{{ $envio->almacen_direccion }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Coordenadas:</div>
                        <div class="info-cell">{{ $envio->almacen_lat ?? 'N/A' }}, {{ $envio->almacen_lng ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATOS DEL TRANSPORTE</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Transportista:</div>
                <div class="info-cell">{{ $envio->transportista_nombre ?? 'N/A' }}</div>
                <div class="info-cell info-label">Vehículo:</div>
                <div class="info-cell">{{ $envio->vehiculo_placa ?? 'N/A' }} {{ $envio->vehiculo_marca ?? '' }} {{ $envio->vehiculo_modelo ?? '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Fecha Salida:</div>
                <div class="info-cell">{{ $envio->fecha_inicio_transito ? \Carbon\Carbon::parse($envio->fecha_inicio_transito)->format('d/m/Y H:i') : 'N/A' }}</div>
                <div class="info-cell info-label">Fecha Entrega:</div>
                <div class="info-cell">{{ $envio->fecha_entrega ? \Carbon\Carbon::parse($envio->fecha_entrega)->format('d/m/Y H:i') : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DETALLE DE PRODUCTOS</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Peso Unit. (kg)</th>
                    <th class="text-right">Peso Total (kg)</th>
                    <th class="text-right">Precio Unit. (Bs)</th>
                    <th class="text-right">Subtotal (Bs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $index => $producto)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $producto->producto_nombre }}</td>
                    <td class="text-right">{{ number_format($producto->cantidad) }}</td>
                    <td class="text-right">{{ number_format($producto->peso_unitario ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($producto->total_peso ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($producto->precio_unitario ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($producto->total_precio ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td colspan="2">TOTALES</td>
                    <td class="text-right">{{ number_format($envio->total_cantidad) }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($envio->total_peso, 2) }}</td>
                    <td></td>
                    <td class="text-right">Bs {{ number_format($envio->total_precio, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($envio->observaciones)
    <div class="section">
        <div class="section-title">OBSERVACIONES</div>
        <p style="padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6;">
            {{ $envio->observaciones }}
        </p>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="stamp-box">
                <img src="{{ public_path('images/sello-planta.svg') }}" alt="Sello Oficial">
            </div>
            <div class="signature-line">
                SELLO OFICIAL<br>
                <small>Planta Principal</small>
            </div>
        </div>
        <div class="signature-box">
            @if($envio->firma_transportista)
                <img src="{{ $envio->firma_transportista }}" class="firma-img">
            @else
                <img src="{{ public_path('images/firma-generica.svg') }}" class="firma-img" alt="Firma">
            @endif
            <div class="signature-line">
                FIRMA TRANSPORTISTA<br>
                <small>{{ $envio->transportista_nombre ?? 'N/A' }}</small>
            </div>
        </div>
        <div class="signature-box">
            <img src="{{ public_path('images/firma-generica.svg') }}" class="firma-img" alt="Firma Receptor">
            <div class="signature-line">
                RECIBIDO POR<br>
                <small>{{ $envio->almacen_nombre }}</small>
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Sistema de Gestión Logística - Planta</strong></p>
        <p>Bolivia | Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Este documento tiene validez legal según el Código de Comercio de Bolivia</p>
    </div>
</body>
</html>

