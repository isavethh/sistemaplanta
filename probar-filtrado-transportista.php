<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Envio;
use App\Models\EnvioAsignacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo " PRUEBA DE FILTRADO POR TRANSPORTISTA\n";
echo "========================================\n\n";

// Listar todos los transportistas
echo "📋 Transportistas en el sistema:\n";
$transportistas = User::where('tipo', 'transportista')->get();
foreach ($transportistas as $t) {
    echo "  - ID: {$t->id} | Nombre: {$t->name}\n";
}
echo "\n";

// Listar todos los envíos asignados
echo "📦 Envíos asignados en el sistema:\n";
$asignaciones = EnvioAsignacion::with(['envio', 'transportista'])->get();

if ($asignaciones->isEmpty()) {
    echo "  ⚠️  No hay envíos asignados\n\n";
} else {
    foreach ($asignaciones as $asig) {
        echo "  - Envío: {$asig->envio->codigo} | Transportista ID: {$asig->transportista_id} ({$asig->transportista->name}) | Estado: {$asig->envio->estado}\n";
    }
    echo "\n";
}

// Probar el filtrado para cada transportista
echo "🔍 Probando filtrado por transportista:\n\n";

foreach ($transportistas as $transportista) {
    echo "👤 Transportista: {$transportista->name} (ID: {$transportista->id})\n";
    
    $envios = Envio::select('envios.*', 
            'envio_asignaciones.transportista_id')
        ->join('envio_asignaciones', 'envios.id', '=', 'envio_asignaciones.envio_id')
        ->where('envio_asignaciones.transportista_id', '=', $transportista->id)
        ->whereIn('envios.estado', ['asignado', 'aceptado', 'en_transito'])
        ->get();
    
    if ($envios->isEmpty()) {
        echo "  ✅ Sin envíos asignados (correcto si no tiene)\n";
    } else {
        echo "  📦 Envíos asignados: {$envios->count()}\n";
        foreach ($envios as $envio) {
            echo "     - {$envio->codigo} (Estado: {$envio->estado})\n";
        }
    }
    echo "\n";
}

echo "========================================\n";
echo "  VERIFICACIÓN COMPLETADA\n";
echo "========================================\n\n";

echo "✅ Si cada transportista ve solo sus envíos, el filtrado funciona correctamente.\n";
echo "❌ Si todos ven los mismos envíos, hay un problema en el filtrado.\n\n";

echo "🔧 Para probar en la app:\n";
echo "  1. Inicia Laravel: php artisan serve --host=0.0.0.0 --port=8000\n";
echo "  2. Prueba el endpoint manualmente:\n";
echo "     curl http://10.26.5.55:8000/api/transportista/2/envios\n";
echo "  3. Abre la app y haz login con diferentes transportistas\n\n";







