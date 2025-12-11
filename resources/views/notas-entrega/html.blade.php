<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Entrega - {{ $nota->numero_nota }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            padding: 0;
            background: #ffffff;
            color: #333;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            border-bottom: 3px solid #2E7D32;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: #2E7D32;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        
        .company-subtitle {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .document-title {
            background: #2E7D32;
            color: white;
            padding: 15px 30px;
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 3px;
        }
        
        .document-number {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            color: #2E7D32;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }
        
        .info-section {
            margin-bottom: 30px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .info-header {
            background: #f5f5f5;
            padding: 12px 20px;
            border-bottom: 2px solid #e0e0e0;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 13px;
            color: #2E7D32;
            letter-spacing: 1px;
        }
        
        .info-content {
            padding: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-size: 13px;
            color: #666;
            font-weight: 600;
            min-width: 180px;
        }
        
        .info-value {
            font-size: 14px;
            color: #333;
            font-weight: 400;
            text-align: right;
        }
        
        .section-title {
            background: #f5f5f5;
            padding: 12px 20px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 13px;
            color: #2E7D32;
            letter-spacing: 1px;
            margin: 30px 0 20px 0;
            border-left: 4px solid #2E7D32;
        }
        
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 2px solid #e0e0e0;
        }
        
        .productos-table thead {
            background: #2E7D32;
            color: white;
        }
        
        .productos-table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-right: 1px solid #388E3C;
        }
        
        .productos-table th:last-child {
            border-right: none;
        }
        
        .productos-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        
        .productos-table tbody tr {
            background: white;
        }
        
        .productos-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totales-container {
            margin-top: 30px;
            border: 2px solid #2E7D32;
            background: #f5f5f5;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 20px;
            border-bottom: 1px solid #d0d0d0;
            font-size: 14px;
        }
        
        .total-row:last-child {
            border-bottom: none;
        }
        
        .total-label {
            font-weight: 600;
            color: #333;
        }
        
        .total-value {
            font-weight: 700;
            color: #333;
        }
        
        .grand-total {
            background: #2E7D32;
            color: white !important;
            font-size: 18px;
            font-weight: 700;
        }
        
        .grand-total .total-label,
        .grand-total .total-value {
            color: white;
        }
        
        .firma-section {
            margin-top: 40px;
            padding: 20px;
            background: #E8F5E9;
            border: 2px solid #4CAF50;
            border-radius: 5px;
        }
        
        .firma-title {
            font-size: 14px;
            font-weight: 700;
            color: #2E7D32;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .firma-content {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.8;
            color: #333;
            white-space: pre-line;
        }
        
        .firma-badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 3px solid #2E7D32;
            text-align: center;
        }
        
        .footer-note {
            color: #666;
            font-size: 11px;
            line-height: 1.6;
            margin: 8px 0;
        }
        
        .footer-company {
            font-weight: 700;
            color: #2E7D32;
            font-size: 14px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .observaciones {
            padding: 20px;
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            margin: 20px 30px;
            border-radius: 4px;
        }
        
        .observaciones strong {
            color: #f57c00;
            display: block;
            margin-bottom: 10px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
            }
        }
        
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .productos-table {
                font-size: 12px;
            }
            
            .productos-table th,
            .productos-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado de la Empresa -->
        <div class="header">
            <div class="company-info">
                <div class="company-name">PLANTA LOGÍSTICA</div>
                <div class="company-subtitle">Sistema de Gestión y Control de Envíos</div>
            </div>
        </div>
        
        <!-- Título del Documento -->
        <div class="document-title">NOTA DE ENTREGA</div>
        <div class="document-number">N° {{ $nota->numero_nota }}</div>
        
        <!-- Información del Documento -->
        <div class="info-section">
            <div class="info-header">INFORMACIÓN DEL DOCUMENTO</div>
            <div class="info-content">
                <div class="info-row">
                    <span class="info-label">Fecha de Emisión:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($nota->fecha_emision ?? $nota->created_at)->format('d \d\e F \d\e Y, H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Código de Envío:</span>
                    <span class="info-value">{{ $envio->envio_codigo ?? $envio->codigo ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado del Envío:</span>
                    <span class="info-value">{{ strtoupper($envio->envio_estado ?? $envio->estado ?? 'pendiente') }}</span>
                </div>
            </div>
        </div>
        
        <!-- Información del Cliente -->
        <div class="info-section">
            <div class="info-header">DATOS DEL CLIENTE / ALMACÉN</div>
            <div class="info-content">
                <div class="info-row">
                    <span class="info-label">Nombre / Razón Social:</span>
                    <span class="info-value">{{ $envio->almacen_nombre ?? $nota->almacen_nombre ?? 'No especificado' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ubicación:</span>
                    <span class="info-value">{{ $envio->almacen_direccion ?? $nota->almacen_direccion ?? 'No especificada' }}</span>
                </div>
                @if($envio->transportista_nombre ?? null)
                <div class="info-row">
                    <span class="info-label">Transportista Asignado:</span>
                    <span class="info-value">{{ $envio->transportista_nombre }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Detalle de Productos -->
        <h2 class="section-title">DETALLE DE PRODUCTOS</h2>
        <table class="productos-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">DESCRIPCIÓN</th>
                    <th style="width: 15%;" class="text-center">CANTIDAD</th>
                    <th style="width: 15%;" class="text-right">PRECIO UNIT.</th>
                    <th style="width: 10%;" class="text-right">PESO (KG)</th>
                    <th style="width: 15%;" class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $index => $producto)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $producto->producto_nombre ?? 'Producto sin nombre' }}</strong></td>
                    <td class="text-center">{{ $producto->cantidad }} uds.</td>
                    <td class="text-right">Bs. {{ number_format($producto->precio_unitario ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($producto->peso_unitario ?? 0, 2) }}</td>
                    <td class="text-right"><strong>Bs. {{ number_format($producto->total_precio ?? 0, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Totales -->
        @php
            $subtotal = floatval($nota->subtotal ?? $nota->total_precio ?? 0);
            $ivaPorcentaje = floatval($nota->porcentaje_iva ?? 13);
            $iva = ($subtotal * $ivaPorcentaje) / 100;
            $total = $subtotal + $iva;
        @endphp
        <div class="totales-container">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">Bs. {{ number_format($subtotal, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">IVA ({{ $ivaPorcentaje }}%):</span>
                <span class="total-value">Bs. {{ number_format($iva, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span class="total-label">TOTAL A PAGAR:</span>
                <span class="total-value">Bs. {{ number_format($total, 2) }}</span>
            </div>
        </div>
        
        @if($envio->firma_transportista ?? null)
        <!-- Firma Digital del Transportista -->
        <div class="firma-section">
            <div class="firma-title">FIRMA DIGITAL DEL TRANSPORTISTA</div>
            <div class="firma-content">{{ $envio->firma_transportista }}</div>
            <div class="firma-badge">DOCUMENTO FIRMADO DIGITALMENTE</div>
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <p class="footer-company">PLANTA LOGÍSTICA</p>
            <p class="footer-note">Documento generado automáticamente el {{ now()->format('d \d\e F \d\e Y, \a \l\a\s H:i') }}</p>
            <p class="footer-note">Este documento constituye una Nota de Entrega oficial del sistema.</p>
            <p class="footer-note">Para consultas o aclaraciones, contacte con el departamento de logística.</p>
        </div>
    </div>
</body>
</html>

