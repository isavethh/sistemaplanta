<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Envio;

echo "🔍 Buscando envíos...\n\n";

// Buscar por código exacto
$envio = Envio::where('codigo', 'P100016')->first();
if ($envio) {
    echo "✅ Envío encontrado (código exacto):\n";
    echo "   ID: {$envio->id}\n";
    echo "   Código: {$envio->codigo}\n";
    echo "   Estado: {$envio->estado}\n";
    exit(0);
}

// Buscar por código parcial
$envios = Envio::where('codigo', 'like', '%100016%')
    ->orWhere('codigo', 'like', '%P100%')
    ->get();

if ($envios->count() > 0) {
    echo "📋 Envíos encontrados (búsqueda parcial):\n";
    foreach ($envios as $e) {
        echo "   ID: {$e->id}, Código: {$e->codigo}, Estado: {$e->estado}\n";
    }
} else {
    echo "❌ No se encontró envío con código P100016\n\n";
    echo "📋 Últimos 10 envíos:\n";
    $ultimos = Envio::orderBy('id', 'desc')->take(10)->get();
    foreach ($ultimos as $e) {
        echo "   ID: {$e->id}, Código: {$e->codigo}, Estado: {$e->estado}\n";
    }
}

