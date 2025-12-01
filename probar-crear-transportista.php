<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo " PRUEBA DE CREACIÓN DE TRANSPORTISTA\n";
echo "========================================\n\n";

// Verificar transportistas existentes
echo "📋 Transportistas actuales en la base de datos:\n";
$transportistas = User::where('tipo', 'transportista')
    ->orWhere('role', 'transportista')
    ->get();

if ($transportistas->isEmpty()) {
    echo "  ⚠️  No hay transportistas registrados\n\n";
} else {
    foreach ($transportistas as $t) {
        echo "  - ID: {$t->id} | Nombre: {$t->name} | Email: {$t->email} | Licencia: " . ($t->licencia ?? 'Sin licencia') . "\n";
    }
    echo "\n";
}

// Intentar crear un nuevo transportista de prueba
echo "🔄 Intentando crear un nuevo transportista de prueba...\n\n";

$nombrePrueba = "Carlos Mendoza TEST";
$emailPrueba = "carlos.mendoza.test@transport.com";

// Verificar si ya existe
$existe = User::where('email', $emailPrueba)->first();
if ($existe) {
    echo "⚠️  Ya existe un usuario con el email {$emailPrueba}\n";
    echo "   Eliminando para crear uno nuevo...\n";
    $existe->delete();
}

try {
    $nuevoTransportista = User::create([
        'name' => $nombrePrueba,
        'email' => $emailPrueba,
        'password' => Hash::make('password123'),
        'role' => 'transportista',
        'tipo' => 'transportista',
        'licencia' => 'B',
        'telefono' => '77888999',
        'disponible' => true,
    ]);

    echo "✅ ¡Transportista creado exitosamente!\n\n";
    echo "Detalles:\n";
    echo "  - ID: {$nuevoTransportista->id}\n";
    echo "  - Nombre: {$nuevoTransportista->name}\n";
    echo "  - Email: {$nuevoTransportista->email}\n";
    echo "  - Licencia: {$nuevoTransportista->licencia}\n";
    echo "  - Teléfono: {$nuevoTransportista->telefono}\n";
    echo "  - Disponible: " . ($nuevoTransportista->disponible ? 'Sí' : 'No') . "\n\n";

} catch (\Exception $e) {
    echo "❌ Error al crear transportista:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n\n";
}

// Verificar nuevamente la lista
echo "📋 Transportistas después de la prueba:\n";
$transportistas = User::where('tipo', 'transportista')
    ->orWhere('role', 'transportista')
    ->get();

foreach ($transportistas as $t) {
    echo "  - ID: {$t->id} | Nombre: {$t->name} | Email: {$t->email} | Licencia: " . ($t->licencia ?? 'Sin licencia') . "\n";
}

echo "\n✅ Prueba completada\n\n";
echo "🔍 Ahora verifica:\n";
echo "  1. Abre: http://localhost:8000/transportistas\n";
echo "  2. Deberías ver todos los transportistas listados\n";
echo "  3. Intenta crear uno nuevo desde el formulario\n";
echo "  4. Si falla, revisa: storage/logs/laravel.log\n\n";







