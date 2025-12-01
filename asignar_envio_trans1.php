// Crear un envío y asignarlo al transportista ID 1

$codigo = 'ENV-' . date('Ymd') . '-TRANS1';
$almacen = \App\Models\Almacen::where('activo', true)->first();
$transportista = \App\Models\User::find(1);
$vehiculo = \App\Models\Vehiculo::where('transportista_id', 1)->orWhere('disponible', true)->first();

echo "=== Creando envío para transportista ID 1 ===\n";

if (!$transportista) {
    echo "❌ Transportista ID 1 no encontrado\n";
    exit;
}

echo "Transportista: {$transportista->name}\n";
echo "Almacen destino: {$almacen->nombre}\n";
echo "Vehículo: {$vehiculo->placa}\n\n";

$envio = \App\Models\Envio::create([
    'codigo' => $codigo,
    'almacen_destino_id' => $almacen->id,
    'categoria' => 'Herramientas',
    'fecha_creacion' => now(),
    'fecha_estimada_entrega' => now()->addDays(1),
    'hora_estimada' => '09:00',
    'estado' => 'asignado',
    'total_cantidad' => 8,
    'total_peso' => 180.0,
    'total_precio' => 950.00,
    'observaciones' => 'Envío de prueba para transportista ID 1',
]);

echo "✅ Envío creado: {$codigo}\n";

// Crear producto
\App\Models\EnvioProducto::create([
    'envio_id' => $envio->id,
    'producto_nombre' => 'Herramientas varias',
    'cantidad' => 8,
    'peso_unitario' => 22.5,
    'precio_unitario' => 118.75,
    'total_peso' => 180.0,
    'total_precio' => 950.00,
]);

// Asignar a transportista
$asignacion = \App\Models\EnvioAsignacion::create([
    'envio_id' => $envio->id,
    'transportista_id' => $transportista->id,
    'vehiculo_id' => $vehiculo->id,
    'fecha_asignacion' => now(),
    'observaciones' => 'Asignación de prueba para transportista ID 1',
]);

echo "✅ Envío asignado a: {$transportista->name} (ID: {$transportista->id})\n";
echo "\n✅ ¡Listo! Ahora el transportista ID 1 tiene envíos asignados\n";

