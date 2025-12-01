<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use App\Models\Almacen;
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

            $planta = Almacen::where('es_planta', true)->first();

            // Generar HTML del documento
            $html = $this->generarHTML($envio, $planta);

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

    private function generarHTML($envio, $planta)
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
            <div class="codigo">' . $envio->codigo . '</div>
            <span class="estado">' . strtoupper($envio->estado) . '</span>
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
                <span class="info-value">' . $envio->asignacion->vehiculo->placa . ' - ' . $envio->asignacion->vehiculo->marca . ' ' . $envio->asignacion->vehiculo->modelo . '</span>
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
                        <td>$' . number_format($producto->precio_unitario, 2) . '</td>
                        <td>$' . number_format($producto->total_precio, 2) . '</td>
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
                    <span>$' . number_format($envio->total_precio, 2) . '</span>
                </div>
            </div>
        </div>

        ' . ($envio->observaciones ? '<div class="section">
            <div class="section-title">Observaciones</div>
            <p>' . nl2br(htmlspecialchars($envio->observaciones)) . '</p>
        </div>' : '') . '

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







