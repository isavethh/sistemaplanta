<?php
/**
 * Script para reiniciar un envío por código
 * Uso: php reiniciar-envio.php P100016
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Envio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Obtener código del envío desde argumentos de línea de comandos
$codigoEnvio = $argv[1] ?? null;

if (!$codigoEnvio) {
    echo "❌ Error: Debes proporcionar el código del envío\n";
    echo "Uso: php reiniciar-envio.php P100016\n";
    exit(1);
}

try {
    echo "🔍 Buscando envío con código: {$codigoEnvio}\n";
    
    $envio = Envio::where('codigo', $codigoEnvio)->first();
    
    if (!$envio) {
        echo "❌ Error: No se encontró un envío con código {$codigoEnvio}\n";
        exit(1);
    }
    
    echo "✅ Envío encontrado:\n";
    echo "   ID: {$envio->id}\n";
    echo "   Código: {$envio->codigo}\n";
    echo "   Estado actual: {$envio->estado}\n";
    echo "   Almacén destino: {$envio->almacen_destino_id}\n\n";
    
    // Reiniciar el envío
    echo "🔄 Reiniciando envío...\n";
    
    // 1. Cambiar estado a 'asignado' (estado antes de iniciar)
    $estadoAnterior = $envio->estado;
    $envio->estado = 'asignado';
    
    // 2. Limpiar fecha de inicio de tránsito
    $envio->fecha_inicio_transito = null;
    
    // 3. Limpiar fecha de entrega
    $envio->fecha_entrega = null;
    
    // 4. Guardar cambios
    $envio->save();
    
    echo "   ✓ Estado cambiado de '{$estadoAnterior}' a 'asignado'\n";
    echo "   ✓ Fecha de inicio de tránsito limpiada\n";
    echo "   ✓ Fecha de entrega limpiada\n";
    
    // 5. Limpiar datos de seguimiento
    try {
        $seguimientosEliminados = DB::table('seguimiento_envio')
            ->where('envio_id', $envio->id)
            ->delete();
        echo "   ✓ {$seguimientosEliminados} registros de seguimiento eliminados\n";
    } catch (\Exception $e) {
        echo "   ⚠️ No se pudo limpiar seguimiento: " . $e->getMessage() . "\n";
    }
    
    // 6. Limpiar datos de tracking en Node.js (si existe tabla)
    try {
        // Esto sería para limpiar en la base de datos de Node.js si es necesario
        // Por ahora solo limpiamos en Laravel
    } catch (\Exception $e) {
        // Ignorar si no existe
    }
    
    echo "\n✅ Envío {$codigoEnvio} reiniciado exitosamente\n";
    echo "\n📋 Estado final:\n";
    echo "   Código: {$envio->codigo}\n";
    echo "   Estado: {$envio->estado}\n";
    echo "   Fecha inicio tránsito: " . ($envio->fecha_inicio_transito ?? 'null') . "\n";
    echo "   Fecha entrega: " . ($envio->fecha_entrega ?? 'null') . "\n";
    
    echo "\n💡 El envío ahora está listo para ser iniciado nuevamente desde la app móvil.\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error al reiniciar envío: {$e->getMessage()}\n";
    echo "📋 Trace: {$e->getTraceAsString()}\n";
    exit(1);
}

