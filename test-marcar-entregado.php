<?php

/**
 * Script de prueba para verificar el endpoint de marcar como entregado
 * Uso: php test-marcar-entregado.php [envio_id]
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

$envioId = $argv[1] ?? 145; // ID de prueba por defecto
$baseUrl = 'http://192.168.0.129:8001/api';

echo "🧪 Probando endpoint: POST {$baseUrl}/envios/{$envioId}/entregado\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// 1. Verificar que el envío existe
echo "1️⃣ Verificando que el envío existe...\n";
try {
    $response = Http::timeout(5)->get("{$baseUrl}/envios/{$envioId}");
    
    if ($response->successful()) {
        $envio = $response->json();
        $codigo = $envio['codigo'] ?? $envio['data']['codigo'] ?? 'N/A';
        $estado = $envio['estado'] ?? $envio['data']['estado'] ?? 'N/A';
        echo "   ✅ Envío encontrado: {$codigo} - Estado: {$estado}\n";
    } else {
        echo "   ❌ Error al obtener envío: HTTP {$response->status()}\n";
        echo "   Respuesta: " . $response->body() . "\n";
        echo "   ⚠️ Continuando con la prueba de todas formas...\n";
    }
} catch (\Exception $e) {
    echo "   ⚠️ Error de conexión al verificar envío: {$e->getMessage()}\n";
    echo "   Continuando con la prueba de todas formas...\n";
}

echo "\n";

// 2. Probar el endpoint sin autenticación (como lo hace la app móvil)
echo "2️⃣ Probando POST /envios/{$envioId}/entregado (sin autenticación)...\n";
try {
    $response = Http::timeout(10)
        ->withoutVerifying()
        ->post("{$baseUrl}/envios/{$envioId}/entregado", []);
    
    echo "   Status: HTTP {$response->status()}\n";
    echo "   Headers: " . json_encode($response->headers(), JSON_PRETTY_PRINT) . "\n";
    echo "   Body: " . $response->body() . "\n";
    
    if ($response->successful()) {
        echo "   ✅ Éxito!\n";
        $data = $response->json();
        echo "   Respuesta: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "   ❌ Error: HTTP {$response->status()}\n";
        $error = $response->json();
        echo "   Detalles: " . json_encode($error, JSON_PRETTY_PRINT) . "\n";
    }
} catch (\Illuminate\Http\Client\ConnectionException $e) {
    echo "   ❌ Error de conexión: {$e->getMessage()}\n";
    echo "   Verifica que Laravel esté corriendo en 0.0.0.0:8001\n";
} catch (\Exception $e) {
    echo "   ❌ Error: {$e->getMessage()}\n";
    echo "   Tipo: " . get_class($e) . "\n";
}

echo "\n";

// 3. Verificar CORS
echo "3️⃣ Verificando configuración CORS...\n";
try {
    $response = Http::timeout(5)
        ->withoutVerifying()
        ->withOptions(['allow_redirects' => false])
        ->options("{$baseUrl}/envios/{$envioId}/entregado");
    
    echo "   Status: HTTP {$response->status()}\n";
    $headers = $response->headers();
    echo "   CORS Headers:\n";
    foreach ($headers as $key => $value) {
        if (stripos($key, 'access-control') !== false) {
            echo "     {$key}: " . (is_array($value) ? implode(', ', $value) : $value) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "   ⚠️ No se pudo verificar CORS: {$e->getMessage()}\n";
}

echo "\n";

// 4. Verificar logs de Laravel
echo "4️⃣ Revisando logs recientes...\n";
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    $logContent = file_get_contents($logPath);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -20);
    echo "   Últimas 20 líneas del log:\n";
    foreach ($recentLines as $line) {
        if (stripos($line, 'entregado') !== false || stripos($line, 'envio') !== false || stripos($line, 'error') !== false) {
            echo "     " . substr($line, 0, 150) . "\n";
        }
    }
} else {
    echo "   ⚠️ Archivo de log no encontrado\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Prueba completada\n";

