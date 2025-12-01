<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Envio;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\EnvioAsignacion;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo " PRUEBA DE ASIGNACIÓN DE ENVÍO\n";
echo "========================================\n\n";

// Obtener datos necesarios
$envioPendiente = Envio::where('estado', 'pendiente')->first();
$transportista = User::where('tipo', 'transportista')->first();
$vehiculo = Vehiculo::where('disponible', true)->first();

echo "📦 Envío pendiente: " . ($envioPendiente ? $envioPendiente->codigo : 'NINGUNO') . "\n";
echo "🚗 Vehículo disponible: " . ($vehiculo ? $vehiculo->placa : 'NINGUNO') . "\n";
echo "👤 Transportista: " . ($transportista ? $transportista->name : 'NINGUNO') . "\n\n";

if (!$envioPendiente || !$transportista || !$vehiculo) {
    echo "❌ No hay datos suficientes para hacer la prueba.\n";
    echo "\nCreando datos de prueba...\n\n";
    
    // Crear transportista si no existe
    if (!$transportista) {
        $transportista = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@transport.com',
            'password' => bcrypt('password'),
            'tipo' => 'transportista',
            'telefono' => '77777777',
            'licencia' => 'LIC-12345',
            'disponible' => true,
        ]);
        echo "✅ Transportista creado: {$transportista->name}\n";
    }
    
    // Crear vehículo si no existe
    if (!$vehiculo) {
        $vehiculo = Vehiculo::create([
            'placa' => 'TEST-123',
            'marca' => 'Toyota',
            'modelo' => 'Hilux',
            'capacidad_carga' => 1000,
            'disponible' => true,
        ]);
        echo "✅ Vehículo creado: {$vehiculo->placa}\n";
    }
    
    exit(0);
}

echo "🔄 Intentando asignar envío...\n\n";

DB::beginTransaction();
try {
    // Crear asignación
    $asignacion = EnvioAsignacion::create([
        'envio_id' => $envioPendiente->id,
        'transportista_id' => $transportista->id,
        'vehiculo_id' => $vehiculo->id,
        'fecha_asignacion' => now(),
    ]);

    // Actualizar estado del envío
    $envioPendiente->update([
        'estado' => 'asignado',
        'fecha_asignacion' => now(),
    ]);

    DB::commit();
    
    echo "✅ ¡Asignación exitosa!\n\n";
    echo "Detalles:\n";
    echo "  - Envío: {$envioPendiente->codigo}\n";
    echo "  - Transportista: {$transportista->name}\n";
    echo "  - Vehículo: {$vehiculo->placa}\n";
    echo "  - Estado: {$envioPendiente->estado}\n";
    echo "  - Fecha: " . now()->format('d/m/Y H:i:s') . "\n\n";
    
    echo "🔍 Probando API de transportista...\n";
    
    // Simular llamada a API
    $enviosAsignados = Envio::select('envios.*', 
            'almacenes.nombre as almacen_nombre',
            'almacenes.direccion_completa',
            'almacenes.latitud',
            'almacenes.longitud')
        ->join('envio_asignaciones', 'envios.id', '=', 'envio_asignaciones.envio_id')
        ->leftJoin('almacenes', 'envios.almacen_destino_id', '=', 'almacenes.id')
        ->where('envio_asignaciones.transportista_id', $transportista->id)
        ->whereIn('envios.estado', ['asignado', 'aceptado', 'en_transito'])
        ->get();
    
    echo "📱 El transportista tiene " . $enviosAsignados->count() . " envío(s) asignado(s)\n\n";
    
    foreach ($enviosAsignados as $env) {
        echo "  📦 {$env->codigo}\n";
        echo "     Estado: {$env->estado}\n";
        echo "     Destino: {$env->almacen_nombre}\n";
        echo "     Dirección: {$env->direccion_completa}\n";
        echo "     Coordenadas: {$env->latitud}, {$env->longitud}\n\n";
    }
    
    echo "✅ ¡Todo funciona correctamente!\n";
    echo "\n🚀 Ahora puedes:\n";
    echo "  1. Iniciar Laravel: INICIAR-LARAVEL-PARA-APP.bat\n";
    echo "  2. Abrir la app móvil y hacer login como transportista\n";
    echo "  3. Ver el envío asignado en la app\n";
    echo "  4. Aceptar el envío\n";
    echo "  5. Ver la ruta en el mapa\n";
    echo "  6. Iniciar la simulación de ruta\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error al asignar: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}

