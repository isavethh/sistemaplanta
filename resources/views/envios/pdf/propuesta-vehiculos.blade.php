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
            margin-bottom: 20px;
            padding: 0;
            background: #ffffff;
            border-radius: 5px;
            overflow: hidden;
        }
        .vehiculo-header {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 11px;
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
        
        /* ============================================
           ESTILOS PARA DIBUJOS DE CAMIONES
        ============================================= */
        .truck-visual-container {
            background: #ffffff;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            position: relative;
        }
        
        .truck-wrapper {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .truck-drawing {
            display: table-cell;
            vertical-align: middle;
            width: 55%;
            text-align: center;
            padding-right: 15px;
        }
        
        .truck-info-side {
            display: table-cell;
            vertical-align: middle;
            width: 45%;
            padding-left: 15px;
            border-left: 1px solid #dee2e6;
        }
        
        .truck-visual {
            position: relative;
            width: 220px;
            height: 100px;
            margin: 0 auto;
            display: inline-block;
        }
        
        .truck-cabin {
            position: absolute;
            right: 0;
            bottom: 20px;
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #1976D2, #1565C0);
            border-radius: 6px 6px 0 0;
            border: 2px solid #0D47A1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .truck-window {
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            height: 20px;
            background: linear-gradient(135deg, #81D4FA, #B3E5FC);
            border-radius: 3px;
            border: 1px solid #0D47A1;
        }
        
        .truck-cargo {
            position: absolute;
            left: 0;
            bottom: 20px;
            width: 160px;
            height: 85px;
            background: linear-gradient(135deg, #ECEFF1, #CFD8DC);
            border: 3px solid #546E7A;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .truck-cargo-fill {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 0%;
            background: linear-gradient(0deg, #4CAF50, #66BB6A, #81C784);
            border-top: 2px solid #2E7D32;
            z-index: 0;
        }
        
        .truck-cargo-fill.warning {
            background: linear-gradient(0deg, #FFC107, #FFD54F, #FFE082);
            border-top-color: #F57C00;
        }
        
        .truck-cargo-fill.danger {
            background: linear-gradient(0deg, #F44336, #E57373, #EF5350);
            border-top-color: #C62828;
        }
        
        /* Caja 3D dentro del contenedor */
        .caja-3d-container {
            position: absolute;
            top: 6px;
            left: 6px;
            right: 6px;
            bottom: 6px;
            display: table;
            width: calc(100% - 12px);
            height: calc(100% - 12px);
        }
        
        .caja-3d-wrapper {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        
        .caja-3d {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #8B4513 0%, #A0522D 50%, #8B4513 100%);
            border: 2px solid #654321;
            border-radius: 3px;
            position: relative;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.4), 2px 2px 6px rgba(0,0,0,0.3);
            display: table;
        }
        
        .caja-3d::before {
            content: '';
            position: absolute;
            top: 8%;
            left: 8%;
            right: 8%;
            bottom: 8%;
            border: 1px dashed rgba(255,255,255,0.3);
            border-radius: 2px;
        }
        
        .productos-dentro {
            display: table-cell;
            vertical-align: middle;
            padding: 4px;
            position: relative;
            z-index: 1;
        }
        
        .productos-grid {
            display: table;
            width: 100%;
            height: 100%;
            margin: 0 auto;
        }
        
        .productos-row {
            display: table-row;
        }
        
        .productos-cell {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            padding: 1px;
        }
        
        .producto-item-mini {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border: 1px solid #FF8C00;
            border-radius: 1px;
            margin: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.3), inset 0 1px 1px rgba(255,255,255,0.3);
        }
        
        .cargo-percentage {
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
            background: rgba(255,255,255,0.95);
            padding: 2px 5px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            z-index: 15;
        }
        
        .truck-wheels {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: table;
            width: 100%;
        }
        
        .wheel-container {
            display: table-cell;
            text-align: center;
        }
        
        .wheel {
            display: inline-block;
            width: 20px;
            height: 20px;
            background: #333;
            border: 3px solid #555;
            border-radius: 50%;
            position: relative;
        }
        
        .wheel::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: #666;
            border-radius: 50%;
        }
        
        .truck-label {
            margin-top: 5px;
            font-size: 9px;
            font-weight: bold;
            color: #333;
        }
        
        .vehiculo-card {
            page-break-inside: avoid;
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
        
        <div style="background: #e3f2fd; padding: 8px; margin-bottom: 15px; border-left: 4px solid #2196F3; font-size: 9px;">
            <strong>Leyenda Visual:</strong> Los camiones muestran gráficamente la carga asignada. 
            El color <span style="color: #28a745; font-weight: bold;">verde</span> indica carga normal (0-70%), 
            <span style="color: #ffc107; font-weight: bold;">amarillo</span> indica alta carga (70-90%), 
            y <span style="color: #dc3545; font-weight: bold;">rojo</span> indica carga crítica (90%+). 
            Las cajas naranjas representan los productos a transportar.
        </div>
        
        @if(empty($propuesta['vehiculos_propuestos']))
            <div style="padding: 20px; text-align: center; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
                <strong>⚠️ No se encontraron vehículos disponibles que cumplan con los requisitos del envío.</strong><br>
                Por favor, contacte con el administrador del sistema.
            </div>
        @else
            @foreach($propuesta['vehiculos_propuestos'] as $index => $item)
            @php
                $porcentajeUso = $item['porcentaje_uso'];
                $cargoClass = 'success';
                if ($porcentajeUso > 90) {
                    $cargoClass = 'danger';
                } elseif ($porcentajeUso > 70) {
                    $cargoClass = 'warning';
                }
                
                // Calcular productos a mostrar en la caja 3D según peso y cantidad
                // Si es el primer vehículo, mostrar todos los productos proporcionalmente
                // Si hay múltiples vehículos, distribuir según el peso asignado
                $productosEnVehiculo = [];
                $totalItems = 0;
                
                if (count($propuesta['vehiculos_propuestos']) == 1) {
                    // Un solo vehículo: mostrar todos los productos
                    foreach ($propuesta['productos'] as $p) {
                        $cantidad = max(1, floor($p->cantidad));
                        for ($i = 0; $i < min($cantidad, 20); $i++) { // Máximo 20 por producto
                            $productosEnVehiculo[] = $p;
                            $totalItems++;
                            if ($totalItems >= 25) break 2; // Máximo 25 items totales
                        }
                    }
                } else {
                    // Múltiples vehículos: distribuir proporcionalmente
                    $factorDistribucion = $item['peso_asignado_kg'] / max($propuesta['totales']['peso_kg'], 1);
                    foreach ($propuesta['productos'] as $p) {
                        $cantidad = max(1, floor($p->cantidad * $factorDistribucion));
                        for ($i = 0; $i < min($cantidad, 10); $i++) {
                            $productosEnVehiculo[] = $p;
                            $totalItems++;
                            if ($totalItems >= 25) break 2;
                        }
                    }
                }
                
                $itemsAMostrar = min(count($productosEnVehiculo), 25);
                
                // Determinar grid según cantidad
                if ($itemsAMostrar <= 9) {
                    $columnas = 3;
                } elseif ($itemsAMostrar <= 16) {
                    $columnas = 4;
                } elseif ($itemsAMostrar <= 25) {
                    $columnas = 5;
                } else {
                    $columnas = 5;
                }
            @endphp
            
            <div class="vehiculo-card">
                <div class="vehiculo-header">
                    VEHÍCULO {{ $index + 1 }} - {{ $item['vehiculo']->placa }}
                </div>
                
                <div style="padding: 15px;">
                    <!-- Dibujo del Camión -->
                    <div class="truck-visual-container">
                    <div class="truck-wrapper">
                        <div class="truck-drawing">
                            <div class="truck-visual">
                                <!-- Cabina del camión -->
                                <div class="truck-cabin">
                                    <div class="truck-window"></div>
                                </div>
                                
                                <!-- Contenedor de carga con caja 3D -->
                                <div class="truck-cargo">
                                    <div class="truck-cargo-fill {{ $cargoClass }}" style="height: {{ min($porcentajeUso, 100) }}%;"></div>
                                    
                                    <!-- Caja 3D con productos -->
                                    <div class="caja-3d-container">
                                        <div class="caja-3d-wrapper">
                                            <div class="caja-3d">
                                                <div class="productos-dentro">
                                                    <div class="productos-grid">
                                                        @php
                                                            $itemIndex = 0;
                                                            $filasNecesarias = ceil($itemsAMostrar / $columnas);
                                                        @endphp
                                                        @for($fila = 0; $fila < $filasNecesarias && $itemIndex < $itemsAMostrar; $fila++)
                                                        <div class="productos-row">
                                                            @for($col = 0; $col < $columnas && $itemIndex < $itemsAMostrar; $col++)
                                                            <div class="productos-cell">
                                                                <div class="producto-item-mini" title="{{ $productosEnVehiculo[$itemIndex]->producto_nombre ?? 'Producto' }}"></div>
                                                            </div>
                                                            @php $itemIndex++; @endphp
                                                            @endfor
                                                        </div>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Porcentaje de uso -->
                                    <div class="cargo-percentage">{{ number_format($porcentajeUso, 1) }}%</div>
                                </div>
                                
                                <!-- Ruedas -->
                                <div class="truck-wheels">
                                    <div class="wheel-container">
                                        <div class="wheel"></div>
                                    </div>
                                    <div class="wheel-container">
                                        <div class="wheel"></div>
                                    </div>
                                    <div class="wheel-container">
                                        <div class="wheel"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="truck-label">{{ $item['vehiculo']->placa }}</div>
                        </div>
                        
                        <div class="truck-info-side">
                            <div style="font-size: 9px; line-height: 1.8;">
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #007bff; font-size: 10px;">Especificaciones:</strong><br>
                                    <span style="color: #666;">Tipo:</span> <strong>{{ $item['tipo_transporte']->nombre ?? 'N/A' }}</strong><br>
                                    <span style="color: #666;">Tamaño:</span> <strong>{{ $item['tamano']->nombre ?? 'N/A' }}</strong><br>
                                    <span style="color: #666;">Capacidad:</span> <strong>{{ number_format($item['vehiculo']->capacidad_carga ?? 0, 0) }} kg</strong><br>
                                    <span style="color: #666;">Volumen:</span> <strong>{{ number_format($item['vehiculo']->capacidad_volumen ?? 0, 1) }} m³</strong>
                                </div>
                                
                                <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #dee2e6;">
                                    <strong style="color: #28a745; font-size: 10px;">Carga Asignada:</strong><br>
                                    <span style="color: #666;">Peso:</span> <strong>{{ number_format($item['peso_asignado_kg'], 1) }} kg</strong><br>
                                    <span style="color: #666;">Volumen:</span> <strong>{{ number_format($item['volumen_asignado_m3'], 2) }} m³</strong><br>
                                    <span style="color: #666;">Uso:</span> <strong style="color: {{ $porcentajeUso > 90 ? '#dc3545' : ($porcentajeUso > 70 ? '#ffc107' : '#28a745') }}; font-size: 11px;">{{ number_format($porcentajeUso, 1) }}%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    
                    <!-- Información detallada -->
                    <div class="vehiculo-info" style="margin-top: 10px; border-top: 1px solid #dee2e6; padding-top: 8px;">
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
                    @if($item['vehiculo']->transportista)
                    <div class="vehiculo-row">
                        <div class="vehiculo-cell vehiculo-label">Transportista Asignado:</div>
                        <div class="vehiculo-cell">{{ $item['vehiculo']->transportista->name ?? 'N/A' }}</div>
                    </div>
                    @endif
                    </div>
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

