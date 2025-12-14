<?php

/**
 * Script de diagnóstico de conectividad para la app móvil
 * Verifica que todos los endpoints necesarios estén accesibles
 */

$baseUrl = 'http://192.168.0.129:8001/api';
$endpoints = [
    'GET /transportista/2/envios' => "{$baseUrl}/transportista/2/envios",
    'POST /envios/145/entregado' => "{$baseUrl}/envios/145/entregado",
    'GET /health' => 'http://192.168.0.129:8001/health',
];

echo "🔍 DIAGNÓSTICO DE CONECTIVIDAD\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Base URL: {$baseUrl}\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($endpoints as $name => $url) {
    echo "📡 Probando: {$name}\n";
    echo "   URL: {$url}\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if (strpos($name, 'POST') !== false) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
    }
    
    $startTime = microtime(true);
    $response = curl_exec($ch);
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ Error de conexión: {$error}\n";
        echo "   ⏱️  Tiempo: {$duration}ms\n";
    } else {
        echo "   ✅ HTTP {$httpCode}\n";
        echo "   ⏱️  Tiempo: {$duration}ms\n";
        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            if ($data) {
                echo "   📦 Respuesta válida JSON\n";
                if (isset($data['success'])) {
                    echo "   ✓ Success: " . ($data['success'] ? 'true' : 'false') . "\n";
                }
            }
        } else {
            echo "   ⚠️  Respuesta: " . substr($response, 0, 200) . "\n";
        }
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Diagnóstico completado\n";

