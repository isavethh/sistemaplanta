<?php
/**
 * Script para simular el flujo completo de integración:
 * Almacenes -> Trazabilidad -> plantaCruds -> Asignar Transporte -> Asignar Transportista
 * 
 * Uso: php simular-flujo-completo.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "🚀 Iniciando simulación del flujo completo...\n\n";

// Configuración de URLs
$almacenUrl = 'http://localhost:8002';
$trazabilidadUrl = 'http://localhost:8000';
$plantaCrudsUrl = 'http://localhost:8001';

$errores = [];
$exitos = [];

try {
    // ============================================
    // PASO 1: Crear pedido en Almacenes
    // ============================================
    echo "📦 PASO 1: Creando pedido en Almacenes...\n";
    
    $pedidoData = [
        'codigo_comprobante' => 'P' . date('YmdHis'),
        'fecha' => now()->format('Y-m-d'),
        'almacen_id' => 1, // Asumiendo que existe almacén con ID 1
        'proveedor_id' => 1, // Planta
        'productos' => [
            [
                'producto_trazabilidad_id' => 17, // ID de producto en Trazabilidad
                'producto_nombre' => 'Mantequilla de Maní Univalle 350 g',
                'cantidad' => 10,
            ],
            [
                'producto_trazabilidad_id' => 18, // Otro producto
                'producto_nombre' => 'Producto de Prueba',
                'cantidad' => 5,
            ],
        ],
    ];
    
    $response = Http::timeout(10)->post("{$almacenUrl}/api/pedidos", $pedidoData);
    
    if (!$response->successful()) {
        throw new Exception("Error al crear pedido en Almacenes: " . $response->body());
    }
    
    $pedidoAlmacen = $response->json();
    $pedidoId = $pedidoAlmacen['data']['id'] ?? $pedidoAlmacen['id'] ?? null;
    
    if (!$pedidoId) {
        throw new Exception("No se pudo obtener el ID del pedido creado");
    }
    
    echo "✅ Pedido creado en Almacenes: ID {$pedidoId}\n\n";
    $exitos[] = "Pedido creado en Almacenes (ID: {$pedidoId})";
    
    // ============================================
    // PASO 2: Enviar pedido a Trazabilidad
    // ============================================
    echo "📤 PASO 2: Enviando pedido a Trazabilidad...\n";
    
    $response = Http::timeout(10)->post("{$almacenUrl}/api/pedidos/{$pedidoId}/enviar-trazabilidad");
    
    if (!$response->successful()) {
        throw new Exception("Error al enviar pedido a Trazabilidad: " . $response->body());
    }
    
    $trazabilidadResponse = $response->json();
    $pedidoTrazabilidadId = $trazabilidadResponse['data']['pedido_id'] ?? $trazabilidadResponse['pedido_id'] ?? null;
    
    if (!$pedidoTrazabilidadId) {
        throw new Exception("No se pudo obtener el ID del pedido en Trazabilidad");
    }
    
    echo "✅ Pedido enviado a Trazabilidad: ID {$pedidoTrazabilidadId}\n\n";
    $exitos[] = "Pedido enviado a Trazabilidad (ID: {$pedidoTrazabilidadId})";
    
    // Esperar un poco para que se procese
    sleep(2);
    
    // ============================================
    // PASO 3: Aprobar pedido en Trazabilidad
    // ============================================
    echo "✅ PASO 3: Aprobando pedido en Trazabilidad...\n";
    
    $response = Http::timeout(10)->post("{$trazabilidadUrl}/api/pedidos/{$pedidoTrazabilidadId}/aprobar");
    
    if (!$response->successful()) {
        throw new Exception("Error al aprobar pedido en Trazabilidad: " . $response->body());
    }
    
    echo "✅ Pedido aprobado en Trazabilidad\n\n";
    $exitos[] = "Pedido aprobado en Trazabilidad";
    
    // Esperar un poco
    sleep(2);
    
    // ============================================
    // PASO 4: Crear lote y almacenar en Trazabilidad
    // ============================================
    echo "📦 PASO 4: Creando lote y almacenando en Trazabilidad...\n";
    
    // Primero necesitamos obtener los productos del pedido
    $response = Http::timeout(10)->get("{$trazabilidadUrl}/api/pedidos/{$pedidoTrazabilidadId}");
    
    if (!$response->successful()) {
        throw new Exception("Error al obtener pedido de Trazabilidad: " . $response->body());
    }
    
    $pedidoTrazabilidad = $response->json()['data'] ?? $response->json();
    $productos = $pedidoTrazabilidad['productos'] ?? [];
    
    if (empty($productos)) {
        throw new Exception("El pedido no tiene productos");
    }
    
    // Crear lote para cada producto
    $loteIds = [];
    foreach ($productos as $producto) {
        $loteData = [
            'producto_id' => $producto['producto_id'] ?? $producto['id'],
            'cantidad' => $producto['cantidad'] ?? 10,
            'categoria_id' => 1, // Asumiendo categoría 1
            'peso_kg' => 100, // Peso en kg
            'fecha_produccion' => now()->format('Y-m-d'),
        ];
        
        $response = Http::timeout(10)->post("{$trazabilidadUrl}/api/lotes", $loteData);
        
        if ($response->successful()) {
            $lote = $response->json()['data'] ?? $response->json();
            $loteId = $lote['id'] ?? $lote['lote_id'] ?? null;
            if ($loteId) {
                $loteIds[] = $loteId;
            }
        }
    }
    
    if (empty($loteIds)) {
        throw new Exception("No se pudieron crear lotes");
    }
    
    echo "✅ Lotes creados: " . implode(', ', $loteIds) . "\n";
    
    // Almacenar el pedido (esto debería crear el envío en plantaCruds)
    $almacenajeData = [
        'pedido_id' => $pedidoTrazabilidadId,
        'lotes' => $loteIds,
        'pickup_latitude' => -17.7833,
        'pickup_longitude' => -63.1821,
    ];
    
    $response = Http::timeout(15)->post("{$trazabilidadUrl}/api/almacenaje/almacenar", $almacenajeData);
    
    if (!$response->successful()) {
        throw new Exception("Error al almacenar en Trazabilidad: " . $response->body());
    }
    
    $almacenajeResponse = $response->json();
    $envioId = $almacenajeResponse['data']['envio_id'] ?? $almacenajeResponse['envio_id'] ?? null;
    
    if (!$envioId) {
        throw new Exception("No se pudo obtener el ID del envío creado en plantaCruds");
    }
    
    echo "✅ Pedido almacenado. Envío creado en plantaCruds: ID {$envioId}\n\n";
    $exitos[] = "Envío creado en plantaCruds (ID: {$envioId})";
    
    // Esperar un poco
    sleep(2);
    
    // ============================================
    // PASO 5: Aprobar propuesta de vehículos en Trazabilidad
    // ============================================
    echo "🚗 PASO 5: Aprobando propuesta de vehículos en Trazabilidad...\n";
    
    $response = Http::timeout(10)->post("{$plantaCrudsUrl}/api/envios/{$envioId}/aprobar-rechazar", [
        'accion' => 'aprobar',
        'observaciones' => 'Propuesta aprobada automáticamente por script de simulación',
    ]);
    
    if (!$response->successful()) {
        throw new Exception("Error al aprobar propuesta: " . $response->body());
    }
    
    echo "✅ Propuesta de vehículos aprobada\n\n";
    $exitos[] = "Propuesta de vehículos aprobada";
    
    // Esperar un poco
    sleep(2);
    
    // ============================================
    // PASO 6: Asignar vehículo en plantaCruds
    // ============================================
    echo "🚛 PASO 6: Asignando vehículo en plantaCruds...\n";
    
    // Obtener lista de vehículos disponibles
    $response = Http::timeout(10)->get("{$plantaCrudsUrl}/api/vehiculos");
    
    if (!$response->successful()) {
        throw new Exception("Error al obtener vehículos: " . $response->body());
    }
    
    $vehiculos = $response->json()['data'] ?? $response->json();
    $vehiculoId = null;
    
    if (is_array($vehiculos) && count($vehiculos) > 0) {
        $vehiculoId = $vehiculos[0]['id'] ?? $vehiculos[0]['vehiculo_id'] ?? null;
    }
    
    if (!$vehiculoId) {
        throw new Exception("No hay vehículos disponibles");
    }
    
    echo "✅ Vehículo seleccionado: ID {$vehiculoId}\n";
    
    // ============================================
    // PASO 7: Asignar transportista (isaveth)
    // ============================================
    echo "👤 PASO 7: Asignando transportista (isaveth)...\n";
    
    // Buscar usuario isaveth
    $user = DB::table('users')
        ->where('email', 'like', '%isaveth%')
        ->orWhere('name', 'like', '%isaveth%')
        ->first();
    
    if (!$user) {
        // Buscar por ID 2 (asumiendo que isaveth es el usuario 2)
        $user = DB::table('users')->where('id', 2)->first();
    }
    
    if (!$user) {
        throw new Exception("No se encontró el usuario isaveth");
    }
    
    $transportistaId = $user->id;
    echo "✅ Transportista encontrado: {$user->name} (ID: {$transportistaId})\n";
    
    // Primero asignar el transportista al vehículo si no está asignado
    $vehiculo = DB::table('vehiculos')->where('id', $vehiculoId)->first();
    if ($vehiculo && !$vehiculo->transportista_id) {
        DB::table('vehiculos')
            ->where('id', $vehiculoId)
            ->update(['transportista_id' => $transportistaId]);
        echo "✅ Transportista asignado al vehículo\n";
    }
    
    // Asignar envío
    $asignacionData = [
        'envio_id' => $envioId,
        'vehiculo_id' => $vehiculoId,
        'transportista_id' => $transportistaId,
    ];
    
    $response = Http::timeout(10)->post("{$plantaCrudsUrl}/api/asignaciones/asignar", $asignacionData);
    
    if (!$response->successful()) {
        throw new Exception("Error al asignar envío: " . $response->body());
    }
    
    echo "✅ Envío asignado a transportista\n\n";
    $exitos[] = "Envío asignado a transportista (ID: {$transportistaId})";
    
    // ============================================
    // RESUMEN
    // ============================================
    echo "═══════════════════════════════════════════════════════\n";
    echo "✅ SIMULACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "═══════════════════════════════════════════════════════\n\n";
    
    echo "📊 Resumen:\n";
    echo "  • Pedido Almacenes ID: {$pedidoId}\n";
    echo "  • Pedido Trazabilidad ID: {$pedidoTrazabilidadId}\n";
    echo "  • Envío plantaCruds ID: {$envioId}\n";
    echo "  • Vehículo ID: {$vehiculoId}\n";
    echo "  • Transportista ID: {$transportistaId} ({$user->name})\n\n";
    
    echo "✅ Pasos completados:\n";
    foreach ($exitos as $exito) {
        echo "  ✓ {$exito}\n";
    }
    
    echo "\n🎉 ¡Flujo completo simulado exitosamente!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: {$e->getMessage()}\n";
    echo "📋 Trace: {$e->getTraceAsString()}\n";
    exit(1);
}

