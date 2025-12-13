<?php

namespace App\Services;

use App\Models\Envio;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlmacenIntegrationService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.almacen.api_url', env('ALMACEN_API_URL', 'http://localhost:8002/api'));
    }

    /**
     * Enviar información de asignación de envío a sistema-almacen-PSIII
     * 
     * @param Envio $envio
     * @return bool
     */
    public function notifyAsignacion(Envio $envio): bool
    {
        try {
            // Cargar relaciones necesarias
            $envio->load(['asignacion.transportista', 'asignacion.vehiculo', 'almacenDestino']);

            // Extraer información del pedido original desde las observaciones
            $pedidoAlmacenId = $this->extractPedidoAlmacenId($envio);
            $webhookUrl = $this->extractWebhookUrl($envio);

            if (!$pedidoAlmacenId && !$webhookUrl) {
                Log::info('Envío no tiene relación con pedido de almacenes, no se notifica', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                ]);
                return false;
            }

            // Verificar que haya asignación y transportista
            if (!$envio->asignacion) {
                Log::warning('Envío no tiene asignación, no se puede notificar', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                ]);
                return false;
            }

            // Obtener transportista a través de la relación
            $transportista = $envio->asignacion->transportista;
            
            if (!$transportista) {
                // Intentar obtener transportista a través del vehículo
                $transportista = $envio->asignacion->vehiculo?->transportista;
            }

            if (!$transportista || !$transportista->id) {
                Log::warning('Envío no tiene transportista asignado, no se puede notificar', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                    'asignacion_id' => $envio->asignacion->id ?? null,
                    'vehiculo_id' => $envio->asignacion->vehiculo_id ?? null,
                ]);
                return false;
            }

            // Preparar datos de asignación
            $data = [
                'pedido_id' => $pedidoAlmacenId,
                'envio_id' => $envio->id,
                'envio_codigo' => $envio->codigo,
                'estado' => 'asignado',
                'transportista' => [
                    'id' => $transportista->id,
                    'nombre' => $transportista->name ?? null,
                    'email' => $transportista->email ?? null,
                ],
                'vehiculo' => [
                    'id' => $envio->asignacion->vehiculo->id ?? null,
                    'placa' => $envio->asignacion->vehiculo->placa ?? null,
                    'marca' => $envio->asignacion->vehiculo->marca ?? null,
                    'modelo' => $envio->asignacion->vehiculo->modelo ?? null,
                ],
                'fecha_asignacion' => $envio->asignacion->fecha_asignacion?->toIso8601String() ?? $envio->fecha_asignacion?->toIso8601String() ?? now()->toIso8601String(),
                'fecha_estimada_entrega' => $envio->fecha_estimada_entrega?->format('Y-m-d'),
                'almacen_destino' => [
                    'id' => $envio->almacenDestino->id ?? null,
                    'nombre' => $envio->almacenDestino->nombre ?? null,
                    'direccion' => $envio->almacenDestino->direccion ?? null,
                ],
            ];

            // Si hay webhook_url, usarlo directamente
            if ($webhookUrl) {
                Log::info('Enviando notificación de asignación a almacenes vía webhook', [
                    'webhook_url' => $webhookUrl,
                    'envio_id' => $envio->id,
                ]);

                $response = Http::timeout(10)->post($webhookUrl, $data);

                if ($response->successful()) {
                    Log::info('Notificación de asignación enviada exitosamente', [
                        'envio_id' => $envio->id,
                        'pedido_id' => $pedidoAlmacenId,
                    ]);
                    return true;
                } else {
                    Log::warning('Error al enviar notificación de asignación vía webhook', [
                        'envio_id' => $envio->id,
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                }
            }

            // Si no hay webhook_url pero hay pedido_id, usar el endpoint estándar
            if ($pedidoAlmacenId) {
                $endpoint = "{$this->apiUrl}/pedidos/{$pedidoAlmacenId}/asignacion-envio";
                
                Log::info('Enviando notificación de asignación a almacenes vía API', [
                    'endpoint' => $endpoint,
                    'envio_id' => $envio->id,
                    'pedido_id' => $pedidoAlmacenId,
                ]);

                $response = Http::timeout(10)->post($endpoint, $data);

                if ($response->successful()) {
                    Log::info('Notificación de asignación enviada exitosamente', [
                        'envio_id' => $envio->id,
                        'pedido_id' => $pedidoAlmacenId,
                    ]);
                    return true;
                } else {
                    Log::warning('Error al enviar notificación de asignación vía API', [
                        'envio_id' => $envio->id,
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error al notificar asignación a almacenes', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Extraer el ID del pedido de almacenes desde las observaciones del envío
     * 
     * @param Envio $envio
     * @return int|null
     */
    private function extractPedidoAlmacenId(Envio $envio): ?int
    {
        $observaciones = $envio->observaciones ?? '';
        
        // Buscar patrones como "pedido_almacen_id: 123" o "pedido_id: 123"
        if (preg_match('/pedido[_\s]*almacen[_\s]*id[:\s]*(\d+)/i', $observaciones, $matches)) {
            return (int) $matches[1];
        }
        
        if (preg_match('/pedido[_\s]*id[:\s]*(\d+)/i', $observaciones, $matches)) {
            return (int) $matches[1];
        }
        
        // Buscar en el formato que viene de Trazabilidad: "Pedido: P1000001" o "Pedido desde Sistema Almacén - P1000001"
        // Nota: Esto es el código del pedido, no el ID directamente
        // Pero podemos intentar buscarlo en la base de datos de almacenes si tenemos acceso
        if (preg_match('/Pedido[^:]*:\s*P(\d+)/i', $observaciones, $matches)) {
            $codigoPedido = 'P' . $matches[1];
            // Intentar obtener el ID desde la API de almacenes
            try {
                $response = Http::timeout(5)->get("{$this->apiUrl}/pedidos/buscar-por-codigo", [
                    'codigo' => $codigoPedido
                ]);
                
                if ($response->successful() && $response->json('success')) {
                    return $response->json('data.id');
                }
            } catch (\Exception $e) {
                Log::debug("No se pudo buscar pedido por código: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Extraer la URL del webhook desde las observaciones del envío
     * 
     * @param Envio $envio
     * @return string|null
     */
    private function extractWebhookUrl(Envio $envio): ?string
    {
        $observaciones = $envio->observaciones ?? '';
        
        // Buscar URL en las observaciones
        if (preg_match('/webhook[_\s]*url[:\s]*([^\s\n]+)/i', $observaciones, $matches)) {
            return trim($matches[1]);
        }
        
        // Buscar cualquier URL HTTP/HTTPS
        if (preg_match('/(https?:\/\/[^\s\n]+)/i', $observaciones, $matches)) {
            $url = trim($matches[1]);
            // Verificar que sea una URL válida y que apunte a almacenes
            if (filter_var($url, FILTER_VALIDATE_URL) && 
                (strpos($url, 'localhost:8002') !== false || strpos($url, 'almacen') !== false)) {
                return $url;
            }
        }

        return null;
    }
}

