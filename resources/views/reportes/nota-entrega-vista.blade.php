<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Entrega - {{ $envio->codigo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .document-container { max-width: 800px; margin: 20px auto; background: white; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .document-header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 30px; position: relative; }
        .document-header h1 { font-size: 1.8rem; margin-bottom: 5px; }
        .document-number { position: absolute; top: 20px; right: 20px; background: white; color: #28a745; padding: 10px 20px; font-weight: bold; border-radius: 5px; }
        .legal-notice { background: #e8f5e9; border-left: 4px solid #28a745; padding: 15px; margin: 20px; font-size: 0.9rem; }
        .section { margin: 20px; }
        .section-title { background: #343a40; color: white; padding: 10px 15px; margin-bottom: 15px; border-radius: 5px; }
        .info-table { width: 100%; }
        .info-table td { padding: 8px 10px; border-bottom: 1px solid #dee2e6; }
        .info-table .label { background: #f8f9fa; font-weight: 600; width: 30%; }
        .productos-table { width: 100%; border-collapse: collapse; }
        .productos-table th { background: #28a745; color: white; padding: 12px; }
        .productos-table td { padding: 10px 12px; border: 1px solid #dee2e6; }
        .productos-table tfoot td { background: #f8f9fa; font-weight: bold; }
        .signature-section { display: flex; justify-content: space-around; margin-top: 40px; padding: 20px; }
        .signature-box { text-align: center; width: 30%; }
        .signature-line { border-top: 2px solid #333; margin-top: 50px; padding-top: 10px; }
        .stamp-placeholder { width: 100px; height: 100px; border: 2px dashed #28a745; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #999; font-size: 0.8rem; }
        .footer { text-align: center; padding: 20px; background: #f8f9fa; border-top: 1px solid #dee2e6; font-size: 0.8rem; color: #666; }
    </style>
</head>
<body>
    <div class="container-fluid py-3 no-print">
        <div class="d-flex justify-content-center gap-3 mb-3">
            <a href="{{ route('reportes.nota-entrega') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="{{ route('reportes.nota-entrega.pdf', $envio->id) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
        </div>
    </div>

    <div class="document-container">
        <div class="document-header">
            <div class="document-number">N° {{ $envio->codigo }}</div>
            <h1><i class="fas fa-clipboard-check"></i> NOTA DE ENTREGA</h1>
            <p class="mb-0">Documento de Recepción de Mercancías</p>
            <small>Sistema de Gestión Logística - Planta</small>
        </div>

        <div class="legal-notice">
            <strong><i class="fas fa-gavel"></i> AVISO LEGAL:</strong> Este documento constituye constancia de la recepción 
            de mercancías conforme al Código de Comercio de Bolivia (Art. 815-819). La firma del receptor confirma 
            la recepción en conformidad de los productos detallados.
        </div>

        <div class="row mx-3">
            <div class="col-md-6">
                <div class="section">
                    <div class="section-title"><i class="fas fa-industry"></i> DATOS DEL REMITENTE (PLANTA)</div>
                    <table class="info-table">
                        <tr><td class="label">Empresa:</td><td>{{ $planta->nombre ?? 'Planta Principal' }}</td></tr>
                        <tr><td class="label">Dirección:</td><td>{{ $planta->direccion_completa ?? 'N/A' }}</td></tr>
                        <tr><td class="label">Coordenadas:</td><td>{{ $planta->latitud ?? 'N/A' }}, {{ $planta->longitud ?? 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="section">
                    <div class="section-title"><i class="fas fa-warehouse"></i> DATOS DEL DESTINATARIO</div>
                    <table class="info-table">
                        <tr><td class="label">Almacén:</td><td>{{ $envio->almacen_nombre }}</td></tr>
                        <tr><td class="label">Dirección:</td><td>{{ $envio->almacen_direccion }}</td></tr>
                        <tr><td class="label">Coordenadas:</td><td>{{ $envio->almacen_lat ?? 'N/A' }}, {{ $envio->almacen_lng ?? 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title"><i class="fas fa-truck"></i> DATOS DEL TRANSPORTE</div>
            <div class="row">
                <div class="col-md-6">
                    <table class="info-table">
                        <tr><td class="label">Transportista:</td><td>{{ $envio->transportista_nombre ?? 'N/A' }}</td></tr>
                        <tr><td class="label">Vehículo:</td><td>{{ $envio->vehiculo_placa ?? 'N/A' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="info-table">
                        <tr><td class="label">Fecha Salida:</td><td>{{ $envio->fecha_inicio_transito ? \Carbon\Carbon::parse($envio->fecha_inicio_transito)->format('d/m/Y H:i') : 'N/A' }}</td></tr>
                        <tr><td class="label">Fecha Entrega:</td><td class="text-success fw-bold">{{ $envio->fecha_entrega ? \Carbon\Carbon::parse($envio->fecha_entrega)->format('d/m/Y H:i') : 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title"><i class="fas fa-boxes"></i> DETALLE DE PRODUCTOS</div>
            <table class="productos-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Peso Unit. (kg)</th>
                        <th class="text-end">Peso Total (kg)</th>
                        <th class="text-end">Precio Unit. (Bs)</th>
                        <th class="text-end">Subtotal (Bs)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $index => $producto)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $producto->producto_nombre }}</td>
                        <td class="text-end">{{ number_format($producto->cantidad) }}</td>
                        <td class="text-end">{{ number_format($producto->peso_unitario ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($producto->total_peso ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($producto->precio_unitario ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($producto->total_precio ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>TOTALES</strong></td>
                        <td class="text-end"><strong>{{ number_format($envio->total_cantidad) }}</strong></td>
                        <td></td>
                        <td class="text-end"><strong>{{ number_format($envio->total_peso, 2) }}</strong></td>
                        <td></td>
                        <td class="text-end"><strong>Bs {{ number_format($envio->total_precio, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($envio->observaciones)
        <div class="section">
            <div class="section-title"><i class="fas fa-sticky-note"></i> OBSERVACIONES</div>
            <p class="p-3 bg-light border rounded">{{ $envio->observaciones }}</p>
        </div>
        @endif

        <div class="signature-section">
            <div class="signature-box">
                <img src="{{ asset('images/sello-planta.svg') }}" style="width: 100px; height: 100px; opacity: 0.7;">
                <div class="signature-line">
                    <strong>SELLO OFICIAL</strong><br>
                    <small>Planta Principal</small>
                </div>
            </div>
            <div class="signature-box">
                @if($envio->firma_transportista)
                    <img src="{{ $envio->firma_transportista }}" style="max-width: 120px; max-height: 80px;">
                @else
                    <img src="{{ asset('images/firma-generica.svg') }}" style="max-width: 120px; max-height: 60px;">
                @endif
                <div class="signature-line">
                    <strong>FIRMA TRANSPORTISTA</strong><br>
                    <small>{{ $envio->transportista_nombre ?? 'N/A' }}</small>
                </div>
            </div>
            <div class="signature-box">
                <img src="{{ asset('images/firma-generica.svg') }}" style="max-width: 120px; max-height: 60px;">
                <div class="signature-line">
                    <strong>RECIBIDO POR</strong><br>
                    <small>{{ $envio->almacen_nombre }}</small>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>Sistema de Gestión Logística - Planta</strong></p>
            <p>Bolivia | Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p><em>Este documento tiene validez legal según el Código de Comercio de Bolivia</em></p>
        </div>
    </div>
</body>
</html>

