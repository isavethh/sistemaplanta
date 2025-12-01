// 1. Obtener datos necesarios
$almacenes = \App\Models\Almacen::where('activo', true)->get();
$transportistas = \App\Models\User::where('role', 'transportista')->where('disponible', true)->get();
$vehiculos = \App\Models\Vehiculo::where('disponible', true)->get();

echo "=== Datos disponibles ===\n";
echo "Almacenes: " . $almacenes->count() . "\n";
echo "Transportistas: " . $transportistas->count() . "\n";
echo "Vehículos: " . $vehiculos->count() . "\n\n";

if ($almacenes->isEmpty() || $transportistas->isEmpty()) {
    echo "❌ ERROR: No hay almacenes o transportistas disponibles\n";
    exit;
}

// 2. Crear envíos de prueba
echo "=== Creando envíos ===\n";

$codigo1 = 'ENV-' . date('Ymd') . '-TEST1';
$envio1 = \App\Models\Envio::create([
    'codigo' => $codigo1,
    'almacen_destino_id' => $almacenes->first()->id,
    'categoria' => 'Alimentos',
    'fecha_creacion' => now(),
    'fecha_estimada_entrega' => now()->addDays(2),
    'hora_estimada' => '14:00',
    'estado' => 'asignado',
    'total_cantidad' => 10,
    'total_peso' => 250.5,
    'total_precio' => 1500.00,
    'observaciones' => 'Envío de prueba 1 - Creado desde script',
]);
echo "✅ Envío 1 creado: {$codigo1}\n";

// Crear productos para el envío 1
\App\Models\EnvioProducto::create([
    'envio_id' => $envio1->id,
    'producto_nombre' => 'Producto de prueba A',
    'cantidad' => 10,
    'peso_unitario' => 25.05,
    'precio_unitario' => 150.00,
    'total_peso' => 250.5,
    'total_precio' => 1500.00,
]);

$codigo2 = 'ENV-' . date('Ymd') . '-TEST2';
$envio2 = \App\Models\Envio::create([
    'codigo' => $codigo2,
    'almacen_destino_id' => $almacenes->skip(1)->first()->id ?? $almacenes->first()->id,
    'categoria' => 'Bebidas',
    'fecha_creacion' => now(),
    'fecha_estimada_entrega' => now()->addDays(1),
    'hora_estimada' => '10:00',
    'estado' => 'asignado',
    'total_cantidad' => 5,
    'total_peso' => 120.0,
    'total_precio' => 800.00,
    'observaciones' => 'Envío de prueba 2 - Creado desde script',
]);
echo "✅ Envío 2 creado: {$codigo2}\n";

// Crear productos para el envío 2
\App\Models\EnvioProducto::create([
    'envio_id' => $envio2->id,
    'producto_nombre' => 'Producto de prueba B',
    'cantidad' => 5,
    'peso_unitario' => 24.0,
    'precio_unitario' => 160.00,
    'total_peso' => 120.0,
    'total_precio' => 800.00,
]);

echo "\n=== Asignando envíos a transportistas ===\n";

// 3. Asignar envíos a transportistas
if ($transportistas->count() > 0) {
    $transportista1 = $transportistas->first();
    $vehiculo1 = $vehiculos->where('transportista_id', $transportista1->id)->first() ?? $vehiculos->first();
    
    $asignacion1 = \App\Models\EnvioAsignacion::create([
        'envio_id' => $envio1->id,
        'transportista_id' => $transportista1->id,
        'vehiculo_id' => $vehiculo1->id ?? null,
        'fecha_asignacion' => now(),
        'observaciones' => 'Asignación automática de prueba',
    ]);
    echo "✅ Envío 1 asignado a: {$transportista1->name} (ID: {$transportista1->id})\n";
}

if ($transportistas->count() > 1) {
    $transportista2 = $transportistas->skip(1)->first();
    $vehiculo2 = $vehiculos->where('transportista_id', $transportista2->id)->first() ?? $vehiculos->skip(1)->first();
    
    $asignacion2 = \App\Models\EnvioAsignacion::create([
        'envio_id' => $envio2->id,
        'transportista_id' => $transportista2->id,
        'vehiculo_id' => $vehiculo2->id ?? null,
        'fecha_asignacion' => now(),
        'observaciones' => 'Asignación automática de prueba',
    ]);
    echo "✅ Envío 2 asignado a: {$transportista2->name} (ID: {$transportista2->id})\n";
} else {
    // Si solo hay un transportista, asignarle el segundo envío también
    $transportista1 = $transportistas->first();
    $vehiculo1 = $vehiculos->where('transportista_id', $transportista1->id)->first() ?? $vehiculos->first();
    
    $asignacion2 = \App\Models\EnvioAsignacion::create([
        'envio_id' => $envio2->id,
        'transportista_id' => $transportista1->id,
        'vehiculo_id' => $vehiculo1->id ?? null,
        'fecha_asignacion' => now(),
        'observaciones' => 'Asignación automática de prueba',
    ]);
    echo "✅ Envío 2 también asignado a: {$transportista1->name} (ID: {$transportista1->id})\n";
}

echo "\n=== Resumen de envíos creados ===\n";

$enviosConAsignacion = \App\Models\Envio::with(['asignacion.transportista', 'almacenDestino'])
    ->whereIn('codigo', [$codigo1, $codigo2])
    ->get();

foreach ($enviosConAsignacion as $envio) {
    echo "\n📦 Código: {$envio->codigo}\n";
    echo "   Estado: {$envio->estado}\n";
    echo "   Destino: {$envio->almacenDestino->nombre}\n";
    if ($envio->asignacion) {
        echo "   Transportista: {$envio->asignacion->transportista->name}\n";
    }
}

echo "\n✅ ¡Envíos de prueba creados exitosamente!\n";
echo "\n🔄 Ahora puedes ver estos envíos en la app móvil\n";

