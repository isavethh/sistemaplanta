<?php
/**
 * Script para verificar que los servicios estén corriendo
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 Verificando servicios...\n\n";

// 1. Verificar Laravel
echo "1️⃣ Verificando Laravel (puerto 8001)...\n";
try {
    $response = Http::timeout(5)->get('http://localhost:8001/api/ping');
    if ($response->successful()) {
        echo "   ✅ Laravel está corriendo\n";
    } else {
        echo "   ⚠️ Laravel responde pero con error: " . $response->status() . "\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Laravel no está corriendo: " . $e->getMessage() . "\n";
    echo "   💡 Ejecuta: php artisan serve --host=0.0.0.0 --port=8001\n";
}

// 2. Verificar Node.js (WebSocket)
echo "\n2️⃣ Verificando Node.js WebSocket (puerto 3000)...\n";
try {
    $response = Http::timeout(5)->get('http://localhost:3000/health');
    if ($response->successful()) {
        echo "   ✅ Node.js está corriendo\n";
        $data = $response->json();
        echo "   📋 Estado: " . ($data['status'] ?? 'unknown') . "\n";
    } else {
        echo "   ⚠️ Node.js responde pero con error: " . $response->status() . "\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Node.js no está corriendo: " . $e->getMessage() . "\n";
    echo "   💡 Ejecuta: cd ../applanta/backend && npm start\n";
}

// 3. Verificar endpoint de transportista
echo "\n3️⃣ Verificando endpoint de transportista...\n";
try {
    $response = Http::timeout(10)->get('http://localhost:8001/api/transportista/2/envios');
    if ($response->successful()) {
        $data = $response->json();
        echo "   ✅ Endpoint funciona correctamente\n";
        echo "   📦 Envíos encontrados: " . ($data['total'] ?? 0) . "\n";
    } else {
        echo "   ❌ Error " . $response->status() . ": " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error al verificar endpoint: " . $e->getMessage() . "\n";
}

echo "\n✅ Verificación completada\n";

