<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cancelación de Envío - {{ $envio->codigo ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 4px solid #dc3545;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #dc3545;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .codigo {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        .estado-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
            background: #dc3545;
            font-size: 12px;
            text-transform: uppercase;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #dc3545;
            border-radius: 5px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
        }
        .info-row {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            width: 40%;
        }
        .info-value {
            color: #333;
            width: 60%;
            text-align: right;
        }
        .incidente-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .incidente-titulo {
            font-size: 14px;
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
        }
        .incidente-descripcion {
            color: #333;
            line-height: 1.8;
            margin-bottom: 10px;
        }
        .foto-incidente {
            max-width: 300px;
            max-height: 200px;
            border: 2px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }
        .firma-sello-container {
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            padding: 30px 20px;
            gap: 40px;
            margin-top: 30px;
            border-top: 2px solid #dc3545;
            padding-top: 30px;
        }
        .firma-box, .sello-box {
            flex: 1;
            text-align: center;
            min-width: 200px;
        }
        .firma-line, .sello-line {
            border-top: 3px solid #333;
            padding-top: 12px;
            margin-top: 15px;
            display: inline-block;
            min-width: 200px;
        }
        .firma-label, .sello-label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .firma-nombre, .sello-nombre {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .sello-circular {
            width: 160px;
            height: 160px;
            border: 3px solid #4CAF50;
            border-radius: 50%;
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f0f9f0 0%, #ffffff 100%);
            box-shadow: 0 3px 10px rgba(76, 175, 80, 0.2);
            position: relative;
            margin: 0 auto 15px;
        }
        .sello-header {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 8px;
            color: #4CAF50;
            font-weight: bold;
            background: white;
            padding: 2px 8px;
            border-radius: 3px;
            white-space: nowrap;
        }
        .sello-content {
            text-align: center;
            padding: 20px 10px;
        }
        .sello-star {
            font-size: 12px;
            color: #4CAF50;
            margin-bottom: 5px;
        }
        .sello-titulo {
            font-size: 14px;
            color: #4CAF50;
            font-weight: bold;
            margin-bottom: 8px;
            line-height: 1.2;
        }
        .sello-autorizado {
            font-size: 11px;
            color: #4CAF50;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .sello-year {
            font-size: 10px;
            color: #4CAF50;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        @media print {
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚫 DOCUMENTO DE CANCELACIÓN DE ENVÍO</h1>
        <div class="codigo">{{ $envio->codigo ?? 'N/A' }}</div>
        <span class="estado-badge">CANCELADO</span>
    </div>

    <!-- Información del Envío -->
    <div class="section">
        <div class="section-title">Información del Envío</div>
        <div class="info-row">
            <span class="info-label">Código de Envío:</span>
            <span class="info-value">{{ $envio->codigo ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de Creación:</span>
            <span class="info-value">{{ $envio->created_at ? $envio->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de Cancelación:</span>
            <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Almacén Destino:</span>
            <span class="info-value">{{ $envio->almacenDestino->nombre ?? 'N/A' }}</span>
        </div>
        @if($envio->asignacion && $envio->asignacion->transportista)
        <div class="info-row">
            <span class="info-label">Transportista:</span>
            <span class="info-value">{{ $envio->asignacion->transportista->name ?? 'N/A' }}</span>
        </div>
        @endif
    </div>

    <!-- Información del Incidente -->
    <div class="section">
        <div class="section-title">Detalles del Incidente</div>
        <div class="incidente-box">
            <div class="incidente-titulo">Tipo de Incidente:</div>
            <div class="incidente-descripcion">{{ $incidente->tipo_incidente ?? $datos['tipo_incidente'] ?? 'N/A' }}</div>
            
            <div class="incidente-titulo" style="margin-top: 15px;">Descripción:</div>
            <div class="incidente-descripcion">{{ $incidente->descripcion ?? $datos['descripcion'] ?? 'N/A' }}</div>
            
            @if($incidente->foto_url)
            <div class="incidente-titulo" style="margin-top: 15px;">Evidencia Fotográfica:</div>
            @php
                $fotoPath = storage_path('app/public/' . $incidente->foto_url);
                $fotoBase64 = null;
                if (file_exists($fotoPath)) {
                    $fotoContent = file_get_contents($fotoPath);
                    $fotoBase64 = base64_encode($fotoContent);
                }
            @endphp
            @if($fotoBase64)
            <img src="data:image/jpeg;base64,{{ $fotoBase64 }}" alt="Foto del incidente" class="foto-incidente">
            @else
            <div style="color: #999; font-style: italic;">Foto no disponible</div>
            @endif
            @endif
            
            @if($incidente->ubicacion_lat && $incidente->ubicacion_lng)
            <div class="info-row" style="margin-top: 15px;">
                <span class="info-label">Ubicación del Incidente:</span>
                <span class="info-value">{{ number_format($incidente->ubicacion_lat, 6) }}, {{ number_format($incidente->ubicacion_lng, 6) }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Firma y Sello -->
    <div class="firma-sello-container">
        <!-- Firma del Transportista -->
        <div class="firma-box">
            <div class="firma-line">
                <div class="firma-label">FIRMA TRANSPORTISTA</div>
                <div class="firma-nombre">{{ $envio->asignacion && $envio->asignacion->transportista ? $envio->asignacion->transportista->name : 'N/A' }}</div>
            </div>
        </div>
        
        <!-- Sello de Planta Principal -->
        <div class="sello-box">
            <div class="sello-circular">
                <div class="sello-header">SISTEMA DE GESTIÓN LOGÍSTICA</div>
                <div class="sello-content">
                    <div class="sello-star">⭐</div>
                    <div class="sello-titulo">PLANTA<br>PRINCIPAL</div>
                    <div class="sello-autorizado">Autorizado</div>
                    <div class="sello-year">{{ date('Y') }}</div>
                </div>
            </div>
            <div class="sello-line">
                <div class="sello-label">SELLO OFICIAL</div>
                <div class="sello-nombre">Planta Principal</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Este documento certifica la cancelación del envío debido a un incidente reportado durante el trayecto.</p>
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>

