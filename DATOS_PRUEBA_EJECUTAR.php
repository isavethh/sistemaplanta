<?php
// ========================================
// COPIAR TODO ESTO EN: php artisan tinker
// ========================================

// 1. USUARIOS
\App\Models\User::create(['name' => 'Administrador', 'email' => 'admin@planta.com', 'password' => bcrypt('password'), 'role' => 'admin', 'tipo' => 'admin']);

\App\Models\User::create(['name' => 'Juan Pérez (Lic. A)', 'email' => 'juan@transporte.com', 'password' => bcrypt('password'), 'role' => 'transportista', 'tipo' => 'transportista', 'telefono' => '77888888', 'licencia' => 'A', 'disponible' => true]);

\App\Models\User::create(['name' => 'Carlos López (Lic. B)', 'email' => 'carlos@transporte.com', 'password' => bcrypt('password'), 'role' => 'transportista', 'tipo' => 'transportista', 'telefono' => '77999999', 'licencia' => 'B', 'disponible' => true]);

\App\Models\User::create(['name' => 'Encargado Almacén', 'email' => 'almacen@planta.com', 'password' => bcrypt('password'), 'role' => 'almacen', 'tipo' => 'almacen', 'telefono' => '77111111']);

// 2. ALMACENES
$planta = \App\Models\Almacen::create(['nombre' => 'Planta Principal', 'latitud' => -17.783333, 'longitud' => -63.182778, 'direccion_completa' => 'Av. Cristo Redentor 1500, Santa Cruz', 'es_planta' => true, 'activo' => true]);

$almacenNorte = \App\Models\Almacen::create(['nombre' => 'Almacén Norte', 'latitud' => -17.770, 'longitud' => -63.190, 'direccion_completa' => 'Av. Alemana, Zona Norte', 'es_planta' => false, 'activo' => true]);

$almacenCentro = \App\Models\Almacen::create(['nombre' => 'Almacén Centro', 'latitud' => -17.783, 'longitud' => -63.182, 'direccion_completa' => 'Av. Banzer 500', 'es_planta' => false, 'activo' => true]);

// 3. DIRECCIONES (Rutas)
\App\Models\Direccion::create(['almacen_origen_id' => $planta->id, 'almacen_destino_id' => $almacenNorte->id, 'distancia_km' => 5.2, 'tiempo_estimado_minutos' => 15, 'ruta_descripcion' => 'Por Cristo Redentor hasta 4to Anillo']);

\App\Models\Direccion::create(['almacen_origen_id' => $planta->id, 'almacen_destino_id' => $almacenCentro->id, 'distancia_km' => 2.8, 'tiempo_estimado_minutos' => 10, 'ruta_descripcion' => 'Por Av. Banzer directo']);

// 4. TIPOS DE TRANSPORTE
$tipoRefri = \App\Models\TipoTransporte::create(['nombre' => 'Refrigerado', 'descripcion' => 'Transporte refrigerado', 'requiere_temperatura_controlada' => true, 'temperatura_minima' => 0, 'temperatura_maxima' => 10, 'activo' => true]);

$tipoVent = \App\Models\TipoTransporte::create(['nombre' => 'Ventilado', 'descripcion' => 'Transporte ventilado', 'activo' => true]);

// 5. UNIDADES DE MEDIDA
$kg = \App\Models\UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg']);
$ton = \App\Models\UnidadMedida::create(['nombre' => 'Tonelada', 'abreviatura' => 'ton']);

// 6. TIPOS DE EMPAQUE
\App\Models\TipoEmpaque::create(['nombre' => 'Caja', 'descripcion' => 'Caja de cartón']);
\App\Models\TipoEmpaque::create(['nombre' => 'Saco', 'descripcion' => 'Saco']);

// 7. VEHÍCULOS
$juan = \App\Models\User::where('email', 'juan@transporte.com')->first();
$carlos = \App\Models\User::where('email', 'carlos@transporte.com')->first();

\App\Models\Vehiculo::create(['placa' => 'SCZ-1001', 'marca' => 'Volvo', 'modelo' => 'FH16', 'anio' => 2020, 'tipo_vehiculo' => 'Camión Refrigerado', 'tipo_transporte_id' => $tipoRefri->id, 'licencia_requerida' => 'A', 'capacidad_carga' => 18, 'unidad_medida_carga_id' => $ton->id, 'transportista_id' => $juan->id, 'disponible' => true, 'estado' => 'activo']);

\App\Models\Vehiculo::create(['placa' => 'SCZ-2002', 'marca' => 'Toyota', 'modelo' => 'Hilux', 'anio' => 2021, 'tipo_vehiculo' => 'Camioneta', 'tipo_transporte_id' => $tipoVent->id, 'licencia_requerida' => 'B', 'capacidad_carga' => 1000, 'unidad_medida_carga_id' => $kg->id, 'transportista_id' => $carlos->id, 'disponible' => true, 'estado' => 'activo']);

echo "✅ Sistema creado!\n";
echo "Planta: {$planta->nombre}\n";
echo "Almacén Norte y Centro creados\n";
echo "2 Vehículos, 2 Transportistas\n";
echo "\nLOGIN: admin@planta.com / password\n";
exit

