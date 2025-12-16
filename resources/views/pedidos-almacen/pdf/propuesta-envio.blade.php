<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Propuesta de Envío - {{ $pedido->codigo }}</title>
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
        th { background: #28a745; color: white; padding: 8px; text-align: left; font-size: 10px; }
        td { padding: 6px 8px; border: 1px solid #dee2e6; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
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
        
        .recomendacion-box {
            border: 2px solid #007bff;
            margin-bottom: 15px;
            padding: 10px;
            background: #e3f2fd;
        }
        .recomendacion-title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
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
        <div class="document-number">N° {{ $pedido->codigo }}</div>
        <h1>PROPUESTA DE ENVÍO INTELIGENTE</h1>
        <div class="subtitle">DOCUMENTO DE CUBICAJE Y PLANIFICACIÓN</div>
        <div class="subtitle">Sistema PlanTrack - Planta</div>
    </div>

    <div class="notice-box">
        <strong>⚠️ IMPORTANTE:</strong> Este documento contiene la propuesta de envío calculada inteligentemente según las 
        especificaciones de productos, cubicaje, tipo de transporte requerido y recomendaciones de empaque. 
        Trazabilidad debe revisar y aprobar o rechazar esta propuesta antes de que se proceda con la asignación del transportista.
    </div>

    <div class="two-columns">
        <div class="column">
            <div class="section">
                <div class="section-title">DATOS DEL PEDIDO</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell info-label">Código Pedido:</div>
                        <div class="info-cell">{{ $pedido->codigo }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Almacén Destino:</div>
                        <div class="info-cell">{{ $pedido->almacen->nombre ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Dirección:</div>
                        <div class="info-cell">{{ $pedido->almacen->direccion_completa ?? 'Santa Cruz, Bolivia' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Fecha Requerida:</div>
                        <div class="info-cell">{{ $pedido->fecha_requerida->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Hora Requerida:</div>
                        <div class="info-cell">{{ $pedido->hora_requerida ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell info-label">Propietario:</div>
                        <div class="info-cell">{{ $pedido->propietario->name ?? 'N/A' }}</div>
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
                        <strong>{{ number_format($cubicaje['totales']['peso_kg'] ?? 0, 2) }} kg</strong>
                    </div>
                    <div class="totals-row">
                        <strong>Volumen Total:</strong>
                        <strong>{{ number_format($cubicaje['totales']['volumen_m3'] ?? 0, 2) }} m³</strong>
                    </div>
                    <div class="totals-row">
                        <strong>Cantidad de Productos:</strong>
                        <strong>{{ number_format($cubicaje['totales']['cantidad_productos'] ?? 0) }} unidades</strong>
                    </div>
                    <div class="totals-row">
                        <strong>Tipo Transporte:</strong>
                        <strong>{{ $cubicaje['tipo_transporte']->nombre ?? 'Estándar' }}</strong>
                    </div>
                </div>
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
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Total Precio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->productos as $index => $producto)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $producto->producto_nombre }}</td>
                    <td class="text-right">{{ number_format($producto->cantidad) }}</td>
                    <td class="text-right">{{ number_format($producto->peso_unitario ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($producto->total_peso ?? 0, 2) }}</td>
                    <td class="text-right">Bs {{ number_format($producto->precio_unitario ?? 0, 2) }}</td>
                    <td class="text-right">Bs {{ number_format($producto->total_precio ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td colspan="4" class="text-right">TOTALES:</td>
                    <td class="text-right">{{ number_format($pedido->productos->sum('total_peso'), 2) }} kg</td>
                    <td colspan="2" class="text-right">Bs {{ number_format($pedido->productos->sum('total_precio'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if(isset($cubicaje['tipo_transporte']))
    <div class="section">
        <div class="section-title">TIPO DE TRANSPORTE RECOMENDADO</div>
        <div class="recomendacion-box">
            <div class="recomendacion-title">
                <i class="fas fa-truck"></i> {{ $cubicaje['tipo_transporte']->nombre }}
            </div>
            @if($cubicaje['tipo_transporte']->descripcion)
                <p style="margin-top: 5px; font-size: 10px;">{{ $cubicaje['tipo_transporte']->descripcion }}</p>
            @endif
        </div>
    </div>
    @endif

    @if(isset($cubicaje['recomendacion_empaque']) && count($cubicaje['recomendacion_empaque']) > 0)
    <div class="section">
        <div class="section-title">RECOMENDACIÓN DE EMPAQUE</div>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Tipo de Empaque</th>
                    <th>Cantidad de Cajas</th>
                    <th>Dimensiones (cm)</th>
                    <th>Material</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cubicaje['recomendacion_empaque'] as $recomendacion)
                <tr>
                    <td>{{ $recomendacion['producto'] ?? 'N/A' }}</td>
                    <td>{{ $recomendacion['tipo_empaque']->nombre ?? 'N/A' }}</td>
                    <td class="text-center">{{ $recomendacion['cantidad_cajas'] ?? 0 }}</td>
                    <td class="text-center">
                        @if(isset($recomendacion['dimensiones_caja']))
                            {{ $recomendacion['dimensiones_caja']['largo_cm'] ?? 0 }} × 
                            {{ $recomendacion['dimensiones_caja']['ancho_cm'] ?? 0 }} × 
                            {{ $recomendacion['dimensiones_caja']['alto_cm'] ?? 0 }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $recomendacion['material'] ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($cubicaje['capacidad_requerida']))
    <div class="section">
        <div class="section-title">CAPACIDAD DE VEHÍCULO REQUERIDA</div>
        <div class="recomendacion-box">
            <div class="recomendacion-title">Especificaciones Mínimas del Vehículo</div>
            <div style="margin-top: 5px;">
                <p><strong>Capacidad de Carga:</strong> {{ number_format($cubicaje['capacidad_requerida']['peso_minimo_kg'] ?? 0, 2) }} kg</p>
                <p><strong>Capacidad de Volumen:</strong> {{ number_format($cubicaje['capacidad_requerida']['volumen_minimo_m3'] ?? 0, 2) }} m³</p>
                @if(isset($cubicaje['capacidad_requerida']['tamano_recomendado']))
                    <p><strong>Tamaño Recomendado:</strong> {{ $cubicaje['capacidad_requerida']['tamano_recomendado'] }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if(isset($cubicaje['velocidad_recomendada']))
    <div class="section">
        <div class="section-title">VELOCIDAD RECOMENDADA</div>
        <div class="recomendacion-box">
            <div class="recomendacion-title">Parámetros de Conducción</div>
            <div style="margin-top: 5px;">
                <p><strong>Velocidad Recomendada:</strong> {{ $cubicaje['velocidad_recomendada']['velocidad_recomendada_kmh'] ?? 60 }} km/h</p>
                <p><strong>Velocidad Máxima:</strong> {{ $cubicaje['velocidad_recomendada']['velocidad_maxima_kmh'] ?? 80 }} km/h</p>
                <p><strong>Velocidad Mínima:</strong> {{ $cubicaje['velocidad_recomendada']['velocidad_minima_kmh'] ?? 40 }} km/h</p>
                @if(isset($cubicaje['velocidad_recomendada']['razon']))
                    <p style="margin-top: 5px; font-size: 9px; color: #666;"><em>{{ $cubicaje['velocidad_recomendada']['razon'] }}</em></p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if(isset($cubicaje['recomendaciones']) && count($cubicaje['recomendaciones']) > 0)
    <div class="section">
        <div class="section-title">RECOMENDACIONES ADICIONALES</div>
        <div style="background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3;">
            <ul style="margin-left: 20px; font-size: 10px;">
                @foreach($cubicaje['recomendaciones'] as $recomendacion)
                <li style="margin-bottom: 5px;">{{ $recomendacion }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @if($pedido->observaciones)
    <div class="section">
        <div class="section-title">OBSERVACIONES DEL PEDIDO</div>
        <p style="padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6;">
            {{ $pedido->observaciones }}
        </p>
    </div>
    @endif

    <div class="footer">
        <p><strong>Sistema PlanTrack - Planta</strong></p>
        <p>Bolivia | Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Este documento debe ser revisado y aprobado por Trazabilidad antes de proceder con la asignación del transportista.</p>
        <p style="margin-top: 10px; color: #dc3545;">
            <strong>Estado actual: PENDIENTE DE APROBACIÓN</strong>
        </p>
    </div>
</body>
</html>

