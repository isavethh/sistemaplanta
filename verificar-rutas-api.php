<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

echo "========================================\n";
echo " VERIFICACIÓN DE RUTAS API\n";
echo "========================================\n\n";

echo "🔍 Buscando rutas de la API...\n\n";

$routes = Route::getRoutes();
$apiRoutes = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'api/') === 0) {
        $methods = implode('|', $route->methods());
        $apiRoutes[] = [
            'method' => $methods,
            'uri' => $uri,
            'name' => $route->getName() ?? '-',
            'action' => $route->getActionName()
        ];
    }
}

if (empty($apiRoutes)) {
    echo "❌ No se encontraron rutas API\n";
    echo "   Ejecuta: php artisan route:clear\n\n";
    exit(1);
}

echo "✅ Encontradas " . count($apiRoutes) . " rutas API\n\n";

// Filtrar rutas importantes
$rutasImportantes = [
    'api/public/transportistas-login',
    'api/transportista/{id}/envios',
    'api/envios/{id}',
    'api/envios/{id}/documento',
    'api/envios/{id}/aceptar',
    'api/envios/{id}/rechazar',
    'api/envios/{id}/iniciar',
    'api/envios/{id}/simular-movimiento',
];

echo "📋 Rutas importantes para la app:\n\n";

foreach ($rutasImportantes as $ruta) {
    $encontrada = false;
    foreach ($apiRoutes as $apiRoute) {
        if ($apiRoute['uri'] === $ruta) {
            echo "  ✅ {$apiRoute['method']} /{$ruta}\n";
            $encontrada = true;
            break;
        }
    }
    if (!$encontrada) {
        echo "  ❌ /{$ruta} - NO ENCONTRADA\n";
    }
}

echo "\n📋 Todas las rutas API:\n\n";
foreach ($apiRoutes as $route) {
    echo "  {$route['method']} /{$route['uri']}\n";
}

echo "\n========================================\n";
echo "  VERIFICACIÓN COMPLETADA\n";
echo "========================================\n\n";

echo "🔧 Si faltan rutas:\n";
echo "  1. php artisan route:clear\n";
echo "  2. php artisan config:clear\n";
echo "  3. php artisan cache:clear\n\n";







