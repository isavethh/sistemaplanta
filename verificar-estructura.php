<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "========================================\n";
echo " VERIFICANDO ESTRUCTURA DE BASE DE DATOS\n";
echo "========================================\n\n";

// Verificar columnas de almacenes (PostgreSQL)
echo "📋 Columnas de tabla 'almacenes':\n";
$almacenesColumns = DB::select("
    SELECT column_name, data_type, is_nullable
    FROM information_schema.columns
    WHERE table_name = 'almacenes'
    ORDER BY ordinal_position
");
foreach ($almacenesColumns as $col) {
    echo "  - {$col->column_name} ({$col->data_type})\n";
}

// Verificar columnas de direcciones
echo "\n📋 Columnas de tabla 'direcciones':\n";
$direccionesColumns = DB::select("
    SELECT column_name, data_type, is_nullable
    FROM information_schema.columns
    WHERE table_name = 'direcciones'
    ORDER BY ordinal_position
");
foreach ($direccionesColumns as $col) {
    echo "  - {$col->column_name} ({$col->data_type})\n";
}

// Verificar columnas de envio_asignaciones
echo "\n📋 Columnas de tabla 'envio_asignaciones':\n";
$asignacionesColumns = DB::select("
    SELECT column_name, data_type, is_nullable
    FROM information_schema.columns
    WHERE table_name = 'envio_asignaciones'
    ORDER BY ordinal_position
");
foreach ($asignacionesColumns as $col) {
    echo "  - {$col->column_name} ({$col->data_type})\n";
}

// Verificar columnas de users
echo "\n📋 Columnas de tabla 'users':\n";
$usersColumns = DB::select("
    SELECT column_name, data_type, is_nullable
    FROM information_schema.columns
    WHERE table_name = 'users'
    ORDER BY ordinal_position
");
foreach ($usersColumns as $col) {
    echo "  - {$col->column_name} ({$col->data_type})\n";
}

// Contar registros
echo "\n📊 Conteo de registros:\n";
echo "  - Almacenes: " . DB::table('almacenes')->count() . "\n";
echo "  - Direcciones: " . DB::table('direcciones')->count() . "\n";
echo "  - Transportistas: " . DB::table('users')->where('tipo', 'transportista')->count() . "\n";
echo "  - Vehículos: " . DB::table('vehiculos')->count() . "\n";
echo "  - Envíos pendientes: " . DB::table('envios')->where('estado', 'pendiente')->count() . "\n";
echo "  - Envíos asignados: " . DB::table('envios')->where('estado', 'asignado')->count() . "\n";

echo "\n✅ Verificación completada\n";

