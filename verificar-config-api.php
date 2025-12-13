<?php
/**
 * Script para verificar la configuración de la API
 * Ejecutar: php verificar-config-api.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Verificación de Configuración de API ===\n\n";

// Obtener IPs de la máquina
echo "📡 IPs de red disponibles:\n";
$ips = [];
if (PHP_OS_FAMILY === 'Windows') {
    exec('ipconfig | findstr /i "IPv4"', $output);
    foreach ($output as $line) {
        if (preg_match('/(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
            $ips[] = $matches[1];
            echo "  - {$matches[1]}\n";
        }
    }
} else {
    exec('hostname -I', $output);
    $ips = explode(' ', trim($output[0]));
    foreach ($ips as $ip) {
        if ($ip) echo "  - {$ip}\n";
    }
}

echo "\n";

// Verificar configuración actual
$appUrl = env('APP_URL', 'http://localhost');
$appMobileApiUrl = env('APP_MOBILE_API_URL', $appUrl . '/api');
$configApiUrl = config('services.app_mobile.api_base_url', $appUrl . '/api');

echo "⚙️  Configuración actual:\n";
echo "  APP_URL: {$appUrl}\n";
echo "  APP_MOBILE_API_URL: {$appMobileApiUrl}\n";
echo "  config('services.app_mobile.api_base_url'): {$configApiUrl}\n";

echo "\n";

// Detectar si está usando localhost
if (strpos($appUrl, 'localhost') !== false || strpos($appUrl, '127.0.0.1') !== false) {
    echo "⚠️  ADVERTENCIA: Estás usando localhost/127.0.0.1\n";
    echo "   La app móvil NO puede conectarse a localhost.\n";
    echo "   Debes usar una IP de red local (ej: 192.168.0.129)\n\n";
    
    if (!empty($ips)) {
        $suggestedIp = $ips[0]; // Usar la primera IP encontrada
        echo "💡 Sugerencia: Usa esta IP en tu archivo .env:\n";
        echo "   APP_URL=http://{$suggestedIp}:8001\n";
        echo "   APP_MOBILE_API_URL=http://{$suggestedIp}:8001/api\n\n";
    }
} else {
    echo "✅ La configuración parece correcta (no usa localhost)\n\n";
}

// Verificar que el servidor pueda escuchar en todas las interfaces
echo "🔍 Verificando que el servidor pueda escuchar en todas las interfaces...\n";
echo "   Para iniciar el servidor en todas las interfaces, usa:\n";
echo "   php artisan serve --host=0.0.0.0 --port=8001\n\n";

// Mostrar endpoints disponibles
echo "📋 Endpoints disponibles:\n";
echo "   GET  {$configApiUrl}/config\n";
echo "   GET  {$configApiUrl}/ping\n";
echo "   GET  {$configApiUrl}/transportista/{id}/envios\n";
echo "   POST {$configApiUrl}/envios/{id}/aceptar\n";
echo "   POST {$configApiUrl}/envios/{id}/rechazar\n\n";

echo "✅ Verificación completada\n";

