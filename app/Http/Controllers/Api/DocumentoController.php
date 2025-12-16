<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use App\Models\Almacen;
use App\Models\PropuestaVehiculo;
use App\Services\PropuestaVehiculosService;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{
    /**
     * Generar documento PDF del envío
     * GET /api/envios/{id}/documento
     */
    public function generarDocumento($id)
    {
        try {
            $envio = Envio::with(['almacenDestino', 'productos', 'asignacion.transportista', 'asignacion.vehiculo'])
                ->findOrFail($id);

            // Asegurar que el envío tenga código
            if (empty($envio->codigo)) {
                $envio->codigo = \App\Models\Envio::generarCodigo();
                $envio->save();
            }

            // Verificar si tiene propuesta aprobada por Trazabilidad
            // Primero verificar si viene de Trazabilidad
            $vieneDeTrazabilidad = strpos($envio->observaciones ?? '', 'ORIGEN: TRAZABILIDAD') !== false 
                || $envio->estado === 'pendiente_aprobacion_trazabilidad';
            
            if ($vieneDeTrazabilidad) {
                // Buscar propuesta (puede estar aprobada, rechazada o pendiente)
                $propuesta = \App\Models\PropuestaVehiculo::where('envio_id', $id)->first();
                
                // Si tiene propuesta (aprobada o pendiente), devolver el PDF de la propuesta
                if ($propuesta && in_array($propuesta->estado, ['aprobada', 'pendiente'])) {
                    $propuestaService = new \App\Services\PropuestaVehiculosService();
                    $propuestaData = $propuestaService->calcularPropuestaVehiculos($envio);
                    
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('envios.pdf.propuesta-vehiculos', [
                        'propuesta' => $propuestaData,
                        'envio' => $envio,
                        'aprobada' => $propuesta->estado === 'aprobada',
                    ]);
                    $pdf->setPaper('a4', 'portrait');
                    
                    return $pdf->stream('propuesta-' . ($propuesta->estado === 'aprobada' ? 'aprobada' : 'pendiente') . '-' . $envio->codigo . '.pdf');
                }
            }

            $planta = Almacen::where('es_planta', true)->first();

            // Obtener firma del transportista desde Node.js
            $firmaTransportista = $this->obtenerFirmaTransportista($envio);

            // Generar HTML del documento normal
            $html = $this->generarHTML($envio, $planta, $firmaTransportista);

            // Por ahora retornamos HTML
            // En producción usarías una librería como DomPDF o Snappy para generar PDF
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'inline; filename="envio-' . $envio->codigo . '.html"');

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener firma del transportista
     * Prioridad: 1) firma_transportista del envío (si es base64), 2) Node.js API, 3) null
     */
    private function obtenerFirmaTransportista(Envio $envio): ?string
    {
        // Primero verificar si hay una firma base64 guardada directamente en el envío
        if ($envio->firma_transportista) {
            // Verificar si es base64 (empieza con data:image o es una cadena base64 válida)
            $firma = $envio->firma_transportista;
            
            // Si empieza con "data:image", es base64 completo
            if (strpos($firma, 'data:image') === 0) {
                // Extraer solo la parte base64
                $firma = preg_replace('#^data:image/[^;]+;base64,#', '', $firma);
            }
            
            // Verificar si parece ser base64 válido (solo caracteres base64 y longitud razonable)
            if (preg_match('/^[A-Za-z0-9+\/]+=*$/', $firma) && strlen($firma) > 100) {
                \Log::info("Firma base64 encontrada en envío", [
                    'envio_id' => $envio->id,
                    'envio_codigo' => $envio->codigo,
                    'firma_length' => strlen($firma)
                ]);
                return $firma;
            }
            
            // Si no es base64, es texto y no la usamos aquí (se mostrará como texto en el documento)
            \Log::debug("Firma en envío es texto, no base64", [
                'envio_id' => $envio->id
            ]);
        }
        
        // Si no hay firma en el envío, buscar en Node.js
        try {
            $nodeApiUrl = env('NODE_API_URL', 'http://bomberos.dasalas.shop/api');
            
            // Intentar primero con el ID del envío
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
                'envio_id' => $envio->id,
                'tipo' => 'salida'
            ]);
            
            if ($response->successful()) {
                $checklists = $response->json();
                $checklistSalida = collect($checklists['checklists'] ?? [])->first();
                $firma = $checklistSalida['firma_base64'] ?? null;
                if ($firma) {
                    \Log::info("Firma obtenida desde Node.js (por ID)", [
                        'envio_id' => $envio->id,
                        'envio_codigo' => $envio->codigo,
                        'tiene_firma' => true
                    ]);
                    return $firma;
                }
            }
            
            // Si no se encontró con el ID, intentar con el código del envío
            if ($envio->codigo) {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
                    'envio_codigo' => $envio->codigo,
                    'tipo' => 'salida'
                ]);
                
                if ($response->successful()) {
                    $checklists = $response->json();
                    $checklistSalida = collect($checklists['checklists'] ?? [])->first();
                    $firma = $checklistSalida['firma_base64'] ?? null;
                    if ($firma) {
                        \Log::info("Firma obtenida desde Node.js (por código)", [
                            'envio_id' => $envio->id,
                            'envio_codigo' => $envio->codigo,
                            'tiene_firma' => true
                        ]);
                        return $firma;
                    }
                }
            }
            
            // Si aún no se encontró, intentar buscar todos los checklists y filtrar
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists");
            
            if ($response->successful()) {
                $checklists = $response->json();
                $allChecklists = $checklists['checklists'] ?? [];
                
                // Buscar por ID o código
                $checklistSalida = collect($allChecklists)->first(function($checklist) use ($envio) {
                    return ($checklist['envio_id'] == $envio->id || $checklist['envio_codigo'] == $envio->codigo) 
                        && ($checklist['tipo'] == 'salida' || $checklist['tipo'] == 'checklist_salida');
                });
                
                $firma = $checklistSalida['firma_base64'] ?? null;
                if ($firma) {
                    \Log::info("Firma obtenida desde Node.js (búsqueda completa)", [
                        'envio_id' => $envio->id,
                        'envio_codigo' => $envio->codigo,
                        'tiene_firma' => true
                    ]);
                    return $firma;
                }
            }
            
            \Log::warning("No se encontró firma base64 para documento", [
                'envio_id' => $envio->id,
                'envio_codigo' => $envio->codigo
            ]);
        } catch (\Exception $e) {
            \Log::warning("Error obteniendo firma desde Node.js: " . $e->getMessage(), [
                'envio_id' => $envio->id,
                'envio_codigo' => $envio->codigo ?? null
            ]);
        }
        
        return null;
    }

    private function generarHTML($envio, $planta, $firmaTransportista = null)
    {
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento de Envío - ' . ($envio->codigo ?? 'N/A') . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Arial", "Helvetica", sans-serif;
            padding: 20px;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 4px solid #4CAF50;
            padding-bottom: 25px;
            margin-bottom: 35px;
            position: relative;
        }
        .header::after {
            content: "";
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: #4CAF50;
        }
        .header h1 {
            color: #4CAF50;
            font-size: 32px;
            margin-bottom: 12px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .codigo {
            font-size: 26px;
            font-weight: bold;
            color: #333;
            margin: 12px 0;
            letter-spacing: 1px;
        }
        .estado {
            display: inline-block;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: bold;
            color: white;
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            box-shadow: 0 3px 8px rgba(33, 150, 243, 0.3);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section {
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(to right, #f9f9f9 0%, #ffffff 100%);
            border-left: 5px solid #4CAF50;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        .section-title::before {
            content: "📦";
            margin-right: 12px;
            font-size: 26px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
            transition: background 0.2s;
        }
        .info-row:hover {
            background: rgba(76, 175, 80, 0.05);
            padding-left: 10px;
            padding-right: 10px;
            margin-left: -10px;
            margin-right: -10px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            font-size: 14px;
        }
        .info-value {
            color: #333;
            font-size: 14px;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-radius: 5px;
            overflow: hidden;
            table-layout: fixed; /* Forzar ancho fijo para evitar desbordamiento */
        }
        th, td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        th {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            font-size: 13px;
        }
        /* Anchos específicos para columnas */
        th:nth-child(1), td:nth-child(1) {
            width: 35%; /* Producto - más espacio */
        }
        th:nth-child(2), td:nth-child(2) {
            width: 12%; /* Cantidad */
            text-align: center;
        }
        th:nth-child(3), td:nth-child(3) {
            width: 15%; /* Peso */
            text-align: right;
        }
        th:nth-child(4), td:nth-child(4) {
            width: 18%; /* Precio Unit */
            text-align: right;
        }
        th:nth-child(5), td:nth-child(5) {
            width: 20%; /* Total */
            text-align: right;
        }
        tbody tr {
            transition: background 0.2s;
        }
        tbody tr:hover {
            background: #f0f9f0;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        .totales {
            margin-top: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            border-radius: 8px;
            border: 2px solid #4CAF50;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.1);
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 15px;
            color: #333;
        }
        .total-final {
            font-size: 22px;
            font-weight: bold;
            color: #4CAF50;
            border-top: 3px solid #4CAF50;
            padding-top: 15px;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .firma-sello-container {
            display: flex;
            justify-content: space-around;
            align-items: flex-start;
            padding: 40px 20px;
            gap: 60px;
            margin-top: 30px;
            border-top: 2px solid #4CAF50;
            padding-top: 40px;
        }
        .firma-box, .sello-box {
            flex: 1;
            text-align: center;
            min-width: 250px;
        }
        .firma-imagen {
            max-width: 240px;
            max-height: 150px;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin: 0 auto 20px;
            display: block;
        }
        .firma-placeholder {
            width: 240px;
            height: 150px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 15px;
            background: #f9f9f9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .firma-placeholder-text {
            color: #666;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }
        .sello-circular {
            width: 200px;
            height: 200px;
            border: 4px solid #4CAF50;
            border-radius: 50%;
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f0f9f0 0%, #ffffff 100%);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.25);
            position: relative;
            margin: 0 auto 20px;
        }
        .sello-header {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 9px;
            color: #4CAF50;
            font-weight: bold;
            background: white;
            padding: 3px 10px;
            border-radius: 4px;
            white-space: nowrap;
        }
        .sello-content {
            text-align: center;
            padding: 25px 15px;
        }
        .sello-star {
            font-size: 14px;
            color: #4CAF50;
            margin-bottom: 8px;
        }
        .sello-titulo {
            font-size: 18px;
            color: #4CAF50;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        .sello-autorizado {
            font-size: 13px;
            color: #4CAF50;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .sello-year {
            font-size: 12px;
            color: #4CAF50;
        }
        .firma-line, .sello-line {
            border-top: 3px solid #333;
            padding-top: 15px;
            margin-top: 15px;
            display: inline-block;
            min-width: 240px;
        }
        .firma-label, .sello-label {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .firma-nombre, .sello-nombre {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        .firma-container-solo {
            margin-top: 40px;
            padding: 30px 20px;
            border-top: 2px solid #4CAF50;
            padding-top: 40px;
            text-align: center;
        }
        .firma-box-solo {
            display: inline-block;
            text-align: center;
            max-width: 400px;
        }
        .firma-imagen-solo {
            max-width: 300px;
            max-height: 180px;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin: 0 auto 20px;
            display: block;
        }
        .firma-placeholder-solo {
            width: 300px;
            height: 180px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 15px;
            background: #f9f9f9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .firma-placeholder-text-solo {
            color: #666;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
        .firma-line-solo {
            border-top: 3px solid #333;
            padding-top: 15px;
            margin-top: 15px;
            display: inline-block;
            min-width: 300px;
        }
        .firma-label-solo {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .firma-nombre-solo {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            body {
                background: white;
                padding: 10px;
            }
            .container {
                box-shadow: none;
                padding: 20px;
            }
            .firma-sello-container {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏭 DOCUMENTO OFICIAL DE ENVÍO</h1>
            <div class="codigo">' . ($envio->codigo ?? 'N/A') . '</div>
            <span class="estado">' . strtoupper($envio->estado ?? 'PENDIENTE') . '</span>
        </div>

        <!-- Información General -->
        <div class="section">
            <div class="section-title">Información General</div>
            <div class="info-row">
                <span class="info-label">Fecha de Creación:</span>
                <span class="info-value">' . date('d/m/Y H:i', strtotime($envio->created_at)) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Estimada de Entrega:</span>
                <span class="info-value">' . ($envio->fecha_estimada_entrega ? date('d/m/Y', strtotime($envio->fecha_estimada_entrega)) : 'N/A') . '</span>
            </div>
            ' . ($envio->hora_estimada ? '<div class="info-row">
                <span class="info-label">Hora Estimada:</span>
                <span class="info-value">' . $envio->hora_estimada . '</span>
            </div>' : '') . '
        </div>

        <!-- Origen y Destino -->
        <div class="section">
            <div class="section-title">Origen y Destino</div>
            <div class="info-row">
                <span class="info-label">📍 Origen (Planta):</span>
                <span class="info-value">' . ($planta->direccion_completa ?? 'Planta Principal') . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">📍 Destino (Almacén):</span>
                <span class="info-value">' . ($envio->almacenDestino->direccion_completa ?? $envio->almacenDestino->nombre) . '</span>
            </div>
        </div>

        <!-- Transportista y Vehículo -->
        ' . ($envio->asignacion ? '<div class="section">
            <div class="section-title">Transporte</div>
            <div class="info-row">
                <span class="info-label">🚗 Transportista:</span>
                <span class="info-value">' . $envio->asignacion->transportista->name . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">🚙 Vehículo:</span>
                <span class="info-value">' . $envio->asignacion->vehiculo->placa . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">📅 Fecha de Asignación:</span>
                <span class="info-value">' . date('d/m/Y H:i', strtotime($envio->asignacion->fecha_asignacion)) . '</span>
            </div>
        </div>' : '') . '

        <!-- Productos -->
        <div class="section">
            <div class="section-title">Productos del Envío</div>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Peso (kg)</th>
                        <th>Precio Unit.</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($envio->productos as $producto) {
            $html .= '<tr>
                        <td style="word-break: break-word; max-width: 200px;">' . htmlspecialchars($producto->producto_nombre) . '</td>
                        <td style="text-align: center;">' . $producto->cantidad . '</td>
                        <td style="text-align: right;">' . number_format($producto->peso_unitario ?? $producto->total_peso ?? 0, 2) . '</td>
                        <td style="text-align: right;">Bs ' . number_format($producto->precio_unitario ?? 0, 2) . '</td>
                        <td style="text-align: right; font-weight: bold;">Bs ' . number_format($producto->total_precio ?? 0, 2) . '</td>
                    </tr>';
        }

        $html .= '</tbody>
            </table>

            <div class="totales">
                <div class="total-row">
                    <span>Total Cantidad:</span>
                    <span><strong>' . $envio->total_cantidad . ' unidades</strong></span>
                </div>
                <div class="total-row">
                    <span>Total Peso:</span>
                    <span><strong>' . number_format($envio->total_peso, 2) . ' kg</strong></span>
                </div>
                <div class="total-row total-final">
                    <span>TOTAL:</span>
                    <span>Bs ' . number_format($envio->total_precio, 2) . '</span>
                </div>
            </div>
        </div>

        ' . ($envio->observaciones ? '<div class="section">
            <div class="section-title">Observaciones</div>
            <p>' . nl2br(htmlspecialchars($envio->observaciones)) . '</p>
        </div>' : '') . '

        <!-- Solo Firma del Transportista (sin sello para móvil) -->
        <div class="firma-container-solo">
            <div class="firma-box-solo">
                ' . ($firmaTransportista ? 
                    '<img src="data:image/png;base64,' . $firmaTransportista . '" alt="Firma Transportista" class="firma-imagen-solo">' 
                    : '<div class="firma-placeholder-solo">
                        <div class="firma-placeholder-text-solo">' . ($envio->asignacion && $envio->asignacion->transportista ? htmlspecialchars($envio->asignacion->transportista->name) : 'Sin firma') . '</div>
                    </div>') . '
                <div class="firma-line-solo">
                    <div class="firma-label-solo">FIRMA TRANSPORTISTA</div>
                    <div class="firma-nombre-solo">' . ($envio->asignacion && $envio->asignacion->transportista ? htmlspecialchars($envio->asignacion->transportista->name) : 'N/A') . '</div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Documento generado el ' . date('d/m/Y H:i:s') . '</p>
            <p>Sistema de Gestión de Envíos - Planta</p>
        </div>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>';

        return $html;
    }
}







