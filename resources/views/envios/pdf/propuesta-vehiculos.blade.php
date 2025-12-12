<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Propuesta de Vehículos - {{ $propuesta['envio']->codigo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        
        .document-header {
            border: 2px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            position: relative;
        }
        .document-header h1 {
            color: #007bff;
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
            background: #007bff;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .notice-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .notice-box strong {
            color: #856404;
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
        th { background: #007bff; color: white; padding: 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border: 1px solid #dee2e6; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals-box {
            background: #f8f9fa;
            border: 1px solid #007bff;
            padding: 10px;
            margin-top: 15px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        
        .vehiculo-card {
            border: 2px solid #007bff;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
        }
        .vehiculo-header {
            background: #007bff;
            color: white;
            padding: 8px;
            margin: -10px -10px 10px -10px;
            font-weight: bold;
        }
        .vehiculo-info {
            display: table;
            width: 100%;
        }
        .vehiculo-row {
            display: table-row;
        }
        .vehiculo-cell {
            display: table-cell;
            padding: 3px 5px;
        }
        .vehiculo-label {
            font-weight: bold;
            width: 40%;
        }
        
        .productos-table {
            margin-top: 10px;
        }
        .productos-table th {
            background: #28a745;
            font-size: 9px;
        }
        .productos-table td {
            font-size: 9px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-pendiente {
            background: #ffc107;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="document-header">
        <div class="document-number">N° {{ $propuesta['envio']->codigo }}</div>
        <h1>PROPUESTA DE VEHÍCULOS PARA ENVÍO</h1>
        <div class="subtitle">DOCUMENTO DE PLANIFICACIÓN DE TRANSPORTE</div>
        <div class="subtitle">Sistema de Gestión Logística - Planta</div>
    </div>

    <div class="notice-box">
        <strong>⚠️ IMPORTANTE:</strong> Este documento contiene la propuesta de vehículos calculada según las 
        especificaciones de productos y empaquetado del envío. Trazabilidad debe revisar y aprobar o rechazar 
        esta propuesta antes de que se proceda con la asignación del transportista.
    </div>

    <div class="two-columns">
        <div class="column">
            <div class="section">
                <div class="section-title">DATOS DEL ENVÍO</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell info-label">Código:</div>
                        <div class="info-cell">{{ $propuesta['envio']->codigo }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Almacén Destino:</div>
                        <div class="info-cell">{{ $propuesta['envio']->almacenDestino->nombre ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Fecha Estimada Entrega:</div>
                        <div class="info-cell">{{ \Carbon\Carbon::parse($propuesta['envio']->fecha_estimada_entrega)->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Estado:</div>
                        <div class="info-cell">
                            <span class="status-badge status-pendiente">PENDIENTE APROBACIÓN TRAZABILIDAD</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="section">
                <div class="section-title">RESUMEN DE CARGA</div>
                <div class="totals-box">
                    <div class="totals-row">
                        <strong>Peso Total:</strong>
                        <strong>{{ number_format($propuesta['totales']['peso_kg'], 2) }} kg</strong>
                    </div>
                    <div class="totals-row">
                        <strong>Volumen Total:</strong>
                        <strong>{{ number_format($propuesta['totales']['volumen_m3'], 2) }} m³</strong>
                    </div>
                    <div class="totals-row">
                        <strong>Cantidad de Productos:</strong>
                        <strong>{{ number_format($propuesta['totales']['cantidad_productos']) }} unidades</strong>
                    </div>
                    <div class="totals-row">
                        <strong>Tipo Transporte Requerido:</strong>
                        <strong>{{ $propuesta['tipo_transporte_requerido']->nombre ?? 'Estándar' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DETALLE DE PRODUCTOS</div>
        <table class="productos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Peso Unit. (kg)</th>
                    <th class="text-right">Peso Total (kg)</th>
                    <th>Dimensiones (cm)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($propuesta['productos'] as $index => $producto)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $producto->producto_nombre }}</td>
                    <td class="text-right">{{ number_format($producto->cantidad) }}</td>
                    <td class="text-right">{{ number_format($producto->peso_unitario ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($producto->total_peso ?? 0, 2) }}</td>
                    <td class="text-center">
                        @if($producto->largo_producto_cm && $producto->ancho_producto_cm && $producto->alto_producto_cm)
                            {{ $producto->largo_producto_cm }} × {{ $producto->ancho_producto_cm }} × {{ $producto->alto_producto_cm }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">VEHÍCULOS PROPUESTOS</div>
        
        @if(empty($propuesta['vehiculos_propuestos']))
            <div style="padding: 20px; text-align: center; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
                <strong>⚠️ No se encontraron vehículos disponibles que cumplan con los requisitos del envío.</strong><br>
                Por favor, contacte con el administrador del sistema.
            </div>
        @else
            @foreach($propuesta['vehiculos_propuestos'] as $index => $item)
            <div class="vehiculo-card">
                <div class="vehiculo-header">
                    VEHÍCULO {{ $index + 1 }} - {{ $item['vehiculo']->placa }}
                </div>
                <div class="vehiculo-info">
                    <div class="vehiculo-row">
                        <div class="vehiculo-cell vehiculo-label">Placa:</div>
                        <div class="vehiculo-cell">{{ $item['vehiculo']->placa }}</div>
                    </div>
                    <div class="vehiculo-row">
                        <div class="vehiculo-cell vehiculo-label">Marca/Modelo:</div>
                        <div class="vehiculo-cell">{{ $item['vehiculo']->marca }} {{ $item['vehiculo']->modelo }} ({{ $item['vehiculo']->anio }})</div>
                    </div>
                    <div class="vehiculo-row">
                        <div class="vehiculo-cell vehiculo-label">Tipo de Transporte:</div>
                        <div class="vehiculo-cell">{{ $item['tipo_transporte']->nombre ?? 'N/A' }}</div>
                    </div>
                    <div class="vehiculo-row">
                        <div class="vehiculo-cell vehiculo-label">Tamaño:</div>
                        <div class="vehiculo-cell">{{ $item['tamano']->nombre ?? 'N/A' }}</div>
                    </div>
                    <div class="vehiculo-row">
                        <div class="vehiculo-cell vehiculo-label">Capacidad Máxima:</div>
                        <div class="vehiculo-cell">{{ number_format($item['vehiculo']->capacidad_carga ?? 0, 2) }} kg / {{ number_format($item['vehiculo']->capacidad_volumen ?? 0, 2) }} m³</div>
                    </div>
                    <div class="vehiculo-row">
                        <div class="vehiculo-cell vehiculo-label">Carga Asignada:</div>
                        <div class="vehiculo-cell">
                            <strong>{{ number_format($item['peso_asignado_kg'], 2) }} kg</strong> 
                            / <strong>{{ number_format($item['volumen_asignado_m3'], 2) }} m³</strong>
                        </div>
                    </div>
                    <div class="vehiculo-row">
                        <div class="vehiculo-cell vehiculo-label">% de Uso:</div>
                        <div class="vehiculo-cell">
                            <strong>{{ number_format($item['porcentaje_uso'], 1) }}%</strong>
                        </div>
                    </div>
                    @if($item['vehiculo']->transportista)
                    <div class="vehiculo-row">
                        <div class="vehiculo-cell vehiculo-label">Transportista Asignado:</div>
                        <div class="vehiculo-cell">{{ $item['vehiculo']->transportista->name ?? 'N/A' }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        @endif
    </div>

    @if($propuesta['envio']->observaciones)
    <div class="section">
        <div class="section-title">OBSERVACIONES</div>
        <p style="padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6;">
            {{ $propuesta['envio']->observaciones }}
        </p>
    </div>
    @endif

    <div class="footer">
        <p><strong>Sistema de Gestión Logística - Planta</strong></p>
        <p>Bolivia | Documento generado el {{ $propuesta['fecha_generacion']->format('d/m/Y H:i:s') }}</p>
        <p>Este documento debe ser revisado y aprobado por Trazabilidad antes de proceder con la asignación del transportista.</p>
        <p style="margin-top: 10px; color: #dc3545;">
            <strong>Estado actual: PENDIENTE DE APROBACIÓN</strong>
        </p>
    </div>
</body>
</html>

