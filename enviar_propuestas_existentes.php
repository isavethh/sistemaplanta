<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Envio;
use App\Services\AlmacenIntegrationService;
use App\Services\PropuestaVehiculosService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== PROCESANDO ENVÍOS EXISTENTES PARA ENVIAR PROPUESTAS DE VEHÍCULOS ===\n\n";

// Obtener envíos que están asignados o en proceso pero no entregados
$envios = Envio::whereIn('estado', ['asignado', 'aceptado', 'en_transito'])
    ->whereHas('asignacion')
    ->with(['asignacion.transportista', 'asignacion.vehiculo', 'almacenDestino'])
    ->get();

echo "Total envíos encontrados: {$envios->count()}\n\n";

$almacenService = new AlmacenIntegrationService();
$propuestaService = new PropuestaVehiculosService();
$enviados = 0;
$errores = 0;

foreach ($envios as $envio) {
    echo "Procesando envío ID: {$envio->id}, Código: {$envio->codigo}\n";
    
    // Intentar extraer pedido_id desde observaciones
    $pedidoAlmacenId = null;
    $observaciones = $envio->observaciones ?? '';
    
    // Buscar patrones como "pedido_almacen_id: 123" o "pedido_id: 123"
    if (preg_match('/pedido[_\s]*almacen[_\s]*id[:\s]*(\d+)/i', $observaciones, $matches)) {
        $pedidoAlmacenId = (int) $matches[1];
    } elseif (preg_match('/pedido[_\s]*id[:\s]*(\d+)/i', $observaciones, $matches)) {
        $pedidoAlmacenId = (int) $matches[1];
    }
    
    // Si no se encontró, intentar buscar por código de envío en la API de almacenes
    if (!$pedidoAlmacenId) {
        try {
            $apiUrl = config('services.almacen.api_url', env('ALMACEN_API_URL', 'http://localhost:8002/api'));
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("{$apiUrl}/pedidos/buscar-por-envio", [
                'envio_codigo' => $envio->codigo,
                'envio_id' => $envio->id,
            ]);
            
            if ($response->successful() && $response->json('success')) {
                $pedidoAlmacenId = $response->json('data.id');
            }
        } catch (\Exception $e) {
            // Continuar sin pedido_id
        }
    }
    
    if (!$pedidoAlmacenId) {
        echo "  ⚠️  No se pudo encontrar pedido_id para este envío\n";
        echo "     Observaciones: " . substr($observaciones, 0, 100) . "\n";
        $errores++;
        continue;
    }
    
    echo "  ✓ Pedido ID encontrado: {$pedidoAlmacenId}\n";
    
    // Generar propuesta de vehículos
    try {
        $propuesta = $propuestaService->calcularPropuestaVehiculos($envio);
        
        if (empty($propuesta['vehiculos_propuestos'])) {
            echo "  ⚠️  No se pudo generar propuesta de vehículos\n";
            $errores++;
            continue;
        }
        
        echo "  ✓ Propuesta generada con " . count($propuesta['vehiculos_propuestos']) . " vehículo(s)\n";
        
        // Generar PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('envios.pdf.propuesta-vehiculos', compact('propuesta'));
        $propuestaVehiculosPdf = base64_encode($pdf->output());
        
        // Enviar a sistema-almacen-PSIII usando el método notifyAsignacion mejorado
        // Pero primero necesitamos simular una asignación
        $data = [
            'pedido_id' => $pedidoAlmacenId,
            'envio_id' => $envio->id,
            'envio_codigo' => $envio->codigo,
            'estado' => $envio->estado,
            'transportista' => [
                'id' => $envio->asignacion->transportista->id ?? null,
                'nombre' => $envio->asignacion->transportista->name ?? 'N/A',
                'email' => $envio->asignacion->transportista->email ?? null,
            ],
            'vehiculo' => [
                'id' => $envio->asignacion->vehiculo->id ?? null,
                'placa' => $envio->asignacion->vehiculo->placa ?? null,
                'marca' => $envio->asignacion->vehiculo->marca ?? null,
                'modelo' => $envio->asignacion->vehiculo->modelo ?? null,
            ],
            'fecha_asignacion' => $envio->asignacion->fecha_asignacion?->toIso8601String() ?? $envio->fecha_asignacion?->toIso8601String() ?? now()->toIso8601String(),
            'almacen_destino' => [
                'id' => $envio->almacenDestino->id ?? null,
                'nombre' => $envio->almacenDestino->nombre ?? null,
                'direccion' => $envio->almacenDestino->direccion ?? null,
            ],
            'documentos' => [
                'propuesta_vehiculos' => $propuestaVehiculosPdf,
            ],
        ];
        
        // Enviar directamente al endpoint de asignación
        $apiUrl = config('services.almacen.api_url', env('ALMACEN_API_URL', 'http://localhost:8002/api'));
        $endpoint = "{$apiUrl}/pedidos/{$pedidoAlmacenId}/asignacion-envio";
        
        echo "  → Enviando a: {$endpoint}\n";
        
        $response = \Illuminate\Support\Facades\Http::timeout(30)->post($endpoint, $data);
        
        if ($response->successful()) {
            echo "  ✅ Propuesta enviada exitosamente\n";
            $enviados++;
        } else {
            echo "  ❌ Error al enviar: " . $response->status() . " - " . $response->body() . "\n";
            $errores++;
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
        $errores++;
    }
    
    echo "\n";
}

echo "=== RESUMEN ===\n";
echo "Enviados exitosamente: {$enviados}\n";
echo "Errores: {$errores}\n";
echo "Total procesados: " . ($enviados + $errores) . "\n";

