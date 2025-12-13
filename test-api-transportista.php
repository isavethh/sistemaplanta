<?php

/**
 * Script de prueba para verificar que la API de transportistas funciona
 * Ejecutar: php test-api-transportista.php
 */

$ip = '192.168.0.129';
$port = 8001;
$transportistaId = 1; // Cambiar si es necesario

$url = "http://{$ip}:{$port}/api/transportista/{$transportistaId}/envios";

echo "========================================\n";
echo "TEST: API Transportista Envíos\n";
echo "========================================\n\n";

echo "URL: {$url}\n";
echo "Transportista ID: {$transportistaId}\n\n";

// Test 1: Verificar que el servidor responde
echo "1. Verificando servidor...\n";
$pingUrl = "http://{$ip}:{$port}/api/ping";
$ch = curl_init($pingUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$pingResponse = curl_exec($ch);
$pingHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$pingError = curl_error($ch);
curl_close($ch);

if ($pingHttpCode === 200) {
    echo "   ✅ Servidor responde correctamente\n";
    $pingData = json_decode($pingResponse, true);
    echo "   Mensaje: " . ($pingData['message'] ?? 'OK') . "\n";
} else {
    echo "   ❌ Servidor NO responde (HTTP {$pingHttpCode})\n";
    if ($pingError) {
        echo "   Error: {$pingError}\n";
    }
    echo "\n   ⚠️  El servidor no está accesible. Verifica:\n";
    echo "      - Laravel corriendo: php artisan serve --host=0.0.0.0 --port=8001\n";
    echo "      - Firewall puerto 8001 abierto\n";
    exit(1);
}

// Test 2: Verificar endpoint de transportista
echo "\n2. Verificando endpoint de transportista...\n";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

echo "   HTTP Code: {$httpCode}\n";
echo "   Tiempo de respuesta: " . round($curlInfo['total_time'], 2) . "s\n";

if ($error) {
    echo "   ❌ Error de conexión: {$error}\n";
    exit(1);
}

if ($httpCode === 200) {
    $data = json_decode($response, true);
    
    if ($data && isset($data['success'])) {
        echo "   ✅ Endpoint responde correctamente\n";
        echo "   Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        echo "   Total envíos: " . ($data['total'] ?? 0) . "\n";
        
        if (isset($data['data']) && is_array($data['data'])) {
            echo "   Envíos encontrados: " . count($data['data']) . "\n";
            
            if (count($data['data']) > 0) {
                echo "\n   Primeros envíos:\n";
                foreach (array_slice($data['data'], 0, 3) as $index => $envio) {
                    echo "   " . ($index + 1) . ". ID: {$envio['id']}, Código: {$envio['codigo']}, Estado: {$envio['estado']}\n";
                }
            } else {
                echo "   ⚠️  No hay envíos asignados a este transportista\n";
            }
        }
    } else {
        echo "   ⚠️  Respuesta no tiene formato esperado\n";
        echo "   Respuesta: " . substr($response, 0, 200) . "\n";
    }
} else {
    echo "   ❌ Error HTTP {$httpCode}\n";
    echo "   Respuesta: " . substr($response, 0, 500) . "\n";
    exit(1);
}

// Test 3: Verificar CORS
echo "\n3. Verificando headers CORS...\n";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$headersResponse = curl_exec($ch);
curl_close($ch);

$hasCors = strpos($headersResponse, 'Access-Control-Allow-Origin') !== false;
if ($hasCors) {
    echo "   ✅ Headers CORS presentes\n";
} else {
    echo "   ⚠️  Headers CORS NO encontrados\n";
}

// Test 4: Verificar desde otra IP (simular dispositivo móvil)
echo "\n4. Verificando accesibilidad desde red...\n";
$testIps = ['192.168.0.129', '127.0.0.1'];
foreach ($testIps as $testIp) {
    $testUrl = "http://{$testIp}:{$port}/api/ping";
    $ch = curl_init($testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    $testResponse = curl_exec($ch);
    $testCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($testCode === 200) {
        echo "   ✅ Accesible desde {$testIp}\n";
    } else {
        echo "   ❌ NO accesible desde {$testIp}\n";
    }
}

echo "\n========================================\n";
echo "RESUMEN:\n";
echo "========================================\n";
echo "✅ Si todos los tests pasan, la API funciona correctamente\n";
echo "❌ Si falla, revisa los errores arriba\n";
echo "\nPara probar desde la app móvil, asegúrate de:\n";
echo "1. Laravel corriendo en 0.0.0.0:8001\n";
echo "2. Firewall puerto 8001 abierto\n";
echo "3. Mismo WiFi en móvil y PC\n";
echo "4. IP correcta en api.js: {$ip}\n";

