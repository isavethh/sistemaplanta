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
     * Obtener firma del transportista desde Node.js
     */
    private function obtenerFirmaTransportista(Envio $envio): ?string
    {
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
                    \Log::info("Firma obtenida para documento", [
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
                        \Log::info("Firma obtenida para documento (por código)", [
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
                    \Log::info("Firma obtenida para documento (búsqueda completa)", [
                        'envio_id' => $envio->id,
                        'envio_codigo' => $envio->codigo,
                        'tiene_firma' => true
                    ]);
                    return $firma;
                }
            }
            
            \Log::warning("No se encontró firma para documento", [
                'envio_id' => $envio->id,
                'envio_codigo' => $envio->codigo
            ]);
        } catch (\Exception $e) {
            \Log::warning("Error obteniendo firma para documento: " . $e->getMessage(), [
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
    <title>Documento de Envío - ' . $envio->codigo . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4CAF50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .codigo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        .estado {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
            background: #2196F3;
        }
        .section {
            margin: 25px 0;
            padding: 20px;
            background: #f9f9f9;
            border-left: 4px solid #4CAF50;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .section-title::before {
            content: "📦";
            margin-right: 10px;
            font-size: 24px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #4CAF50;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .totales {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 5px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 16px;
        }
        .total-final {
            font-size: 20px;
            font-weight: bold;
            color: #4CAF50;
            border-top: 2px solid #4CAF50;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            body {
                background: white;
            }
            .container {
                box-shadow: none;
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
                        <td>' . $producto->producto_nombre . '</td>
                        <td>' . $producto->cantidad . '</td>
                        <td>' . number_format($producto->peso_unitario, 2) . '</td>
                        <td>Bs ' . number_format($producto->precio_unitario, 2) . '</td>
                        <td>Bs ' . number_format($producto->total_precio, 2) . '</td>
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

        <!-- Firma del Transportista -->
        <div class="section">
            <div class="section-title">Firma del Transportista</div>
            <div style="text-align: center; padding: 20px;">
                ' . ($firmaTransportista ? '<img src="data:image/png;base64,' . $firmaTransportista . '" alt="Firma Transportista" style="max-width: 200px; max-height: 120px; border: 2px solid #ddd; border-radius: 4px; padding: 10px; background: white;">' : '<div style="width: 200px; height: 120px; border: 2px dashed #ccc; display: inline-block; border-radius: 4px; padding: 10px; background: #f9f9f9;">
                    <span style="color: #999; font-size: 12px; display: block; margin-top: 40px;">Sin firma</span>
                </div>') . '
                <div style="margin-top: 15px; border-top: 2px solid #333; padding-top: 10px; display: inline-block; min-width: 200px;">
                    <strong>FIRMA TRANSPORTISTA</strong><br>
                    <small>' . ($envio->asignacion && $envio->asignacion->transportista ? $envio->asignacion->transportista->name : 'N/A') . '</small>
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







