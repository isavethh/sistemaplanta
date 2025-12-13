<?php
/**
 * Script para probar la conectividad de la API
 * Ejecutar: php test-conexion-api.php
 */

$ip = '192.168.0.129';
$port = 8001;
$baseUrl = "http://{$ip}:{$port}";

echo "=== Test de Conectividad API ===\n\n";
echo "IP: {$ip}\n";
echo "Puerto: {$port}\n";
echo "URL Base: {$baseUrl}\n\n";

// Test 1: Ping
echo "1. Probando /api/ping...\n";
$ch = curl_init("{$baseUrl}/api/ping");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "   ❌ Error: {$error}\n";
} else {
    echo "   ✅ HTTP Code: {$httpCode}\n";
    echo "   Response: " . substr($response, 0, 200) . "\n";
}

echo "\n";

// Test 2: Config
echo "2. Probando /api/config...\n";
$ch = curl_init("{$baseUrl}/api/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "   ❌ Error: {$error}\n";
} else {
    echo "   ✅ HTTP Code: {$httpCode}\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   API Base URL: " . ($data['api_base_url'] ?? 'N/A') . "\n";
    }
}

echo "\n";

// Test 3: Transportista envios
echo "3. Probando /api/transportista/1/envios...\n";
$ch = curl_init("{$baseUrl}/api/transportista/1/envios");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "   ❌ Error: {$error}\n";
} else {
    echo "   ✅ HTTP Code: {$httpCode}\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        echo "   Total: " . ($data['total'] ?? 0) . "\n";
    } else {
        echo "   Response: " . substr($response, 0, 200) . "\n";
    }
}

echo "\n";
echo "=== Test completado ===\n";

