// Crear vehículos para transportistas que no tienen

$transportistas = \App\Models\User::where('role', 'transportista')->where('disponible', true)->get();

echo "=== Creando vehículos para transportistas ===\n";

foreach ($transportistas as $transportista) {
    // Verificar si ya tiene vehículo
    $vehiculoExistente = \App\Models\Vehiculo::where('transportista_id', $transportista->id)->first();
    
    if ($vehiculoExistente) {
        echo "✅ {$transportista->name} ya tiene vehículo: {$vehiculoExistente->placa}\n";
        continue;
    }
    
    // Crear vehículo según su licencia
    $tipo = $transportista->licencia === 'A' ? 'Camión' : 'Camioneta';
    $placa = 'SCZ-' . rand(1000, 9999);
    
    $vehiculo = \App\Models\Vehiculo::create([
        'placa' => $placa,
        'marca' => $tipo === 'Camión' ? 'Volvo' : 'Toyota',
        'modelo' => $tipo === 'Camión' ? 'FH16' : 'Hilux',
        'anio' => 2020,
        'tipo_vehiculo' => $tipo,
        'licencia_requerida' => $transportista->licencia,
        'capacidad_carga' => $tipo === 'Camión' ? 18000 : 1000,
        'capacidad_volumen' => $tipo === 'Camión' ? 50 : 5,
        'transportista_id' => $transportista->id,
        'disponible' => true,
        'estado' => 'activo',
    ]);
    
    echo "✅ Vehículo creado para {$transportista->name}: {$placa} ({$tipo})\n";
}

echo "\n✅ Vehículos creados exitosamente!\n";

