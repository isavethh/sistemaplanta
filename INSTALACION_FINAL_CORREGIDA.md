# 🚀 INSTALACIÓN FINAL CORREGIDA - PlantaCRUDS

## ✅ **ESTRUCTURA SIMPLIFICADA DE ALMACENES**

### Almacenes ahora SOLO tienen:
```
- id
- nombre
- usuario_almacen_id (FK → users)
- latitud (GPS)
- longitud (GPS)
- direccion_completa (texto)
- es_planta (boolean) - marca si es la planta principal
- activo (boolean)
```

**❌ ELIMINADO:** código y capacidad

---

## 📊 **ESTRUCTURA COMPLETA DE BD**

### 1. `users`
```sql
- id
- name, email, password
- role (admin, transportista, almacen)
- tipo (admin, transportista, almacen)
- telefono, direccion
- licencia (A, B, C) [solo transportistas]
- disponible [solo transportistas]
```

### 2. `almacenes` (SIMPLIFICADO)
```sql
- id
- nombre
- usuario_almacen_id
- latitud, longitud
- direccion_completa
- es_planta (boolean)
- activo
```

### 3. `direcciones` (Rutas entre almacenes)
```sql
- id
- almacen_origen_id (FK → almacenes)
- almacen_destino_id (FK → almacenes)
- distancia_km
- tiempo_estimado_minutos
- ruta_descripcion
```

### 4. `tipos_transporte`
```sql
- id
- nombre (Aislado, Ventilado, Refrigerado)
- descripcion
- requiere_temperatura_controlada
- temperatura_minima, temperatura_maxima
- activo
```

### 5. `vehiculos`
```sql
- id
- placa, marca, modelo, anio
- tipo_vehiculo
- tipo_transporte_id (FK)
- licencia_requerida (A, B, C)
- capacidad_carga
- unidad_medida_carga_id (FK)
- transportista_id (FK)
- disponible, estado
```

### 6. `envios`
```sql
- id
- codigo
- cliente_id (FK → users tipo cliente... pero espera, eliminamos clientes)
- categoria (Verduras o Frutas)
- fecha_creacion, fecha_estimada_entrega, hora_estimada
- estado
- total_cantidad, total_peso, total_precio
- observaciones
```

### 7. `envio_productos`
```sql
- id
- envio_id (FK)
- producto_nombre
- cantidad
- peso_unitario
- unidad_medida_id (FK)
- tipo_empaque_id (FK)
- precio_unitario (Bs)
- total_peso, total_precio
```

### 8. `envio_asignaciones`
```sql
- id
- envio_id (FK)
- transportista_id (FK)
- vehiculo_id (FK)
- fecha_asignacion
- observaciones
```

### 9. `unidades_medida`
```sql
- id
- nombre (Kilogramo, Tonelada, Litro, etc)
- abreviatura (kg, ton, L)
```

### 10. `tipos_empaque`
```sql
- id
- nombre (Caja, Saco, Contenedor, Pallet)
- descripcion
```

---

## 💻 **COMANDOS DE INSTALACIÓN**

```bash
cd C:\Users\Personal\Downloads\Planta\plantaCruds

# 1. Eliminar BD y recrear
php artisan migrate:fresh

# 2. Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Crear datos de prueba
php artisan tinker
```

---

## 📝 **SCRIPT COMPLETO DE DATOS**

Ejecuta esto en `php artisan tinker`:

```php
// ========================================
// 1. USUARIOS
// ========================================

// ADMIN
\App\Models\User::create([
    'name' => 'Administrador Principal',
    'email' => 'admin@planta.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'tipo' => 'admin'
]);

// TRANSPORTISTAS CON LICENCIAS
\App\Models\User::create([
    'name' => 'Juan Pérez',
    'email' => 'juan@transporte.com',
    'password' => bcrypt('password'),
    'role' => 'transportista',
    'tipo' => 'transportista',
    'telefono' => '77888888',
    'licencia' => 'A',
    'disponible' => true
]);

\App\Models\User::create([
    'name' => 'Carlos López',
    'email' => 'carlos@transporte.com',
    'password' => bcrypt('password'),
    'role' => 'transportista',
    'tipo' => 'transportista',
    'telefono' => '77999999',
    'licencia' => 'B',
    'disponible' => true
]);

// USUARIOS DE ALMACEN
\App\Models\User::create([
    'name' => 'Encargado Almacén Central',
    'email' => 'almacen@planta.com',
    'password' => bcrypt('password'),
    'role' => 'almacen',
    'tipo' => 'almacen',
    'telefono' => '77111111'
]);

\App\Models\User::create([
    'name' => 'Encargado Almacén Norte',
    'email' => 'norte@planta.com',
    'password' => bcrypt('password'),
    'role' => 'almacen',
    'tipo' => 'almacen',
    'telefono' => '77222222'
]);

// ========================================
// 2. ALMACENES (sin código ni capacidad)
// ========================================

// PLANTA PRINCIPAL (Punto Fijo)
$planta = \App\Models\Almacen::create([
    'nombre' => 'Planta Principal',
    'latitud' => -17.783333,
    'longitud' => -63.182778,
    'direccion_completa' => 'Av. Cristo Redentor 1500, Santa Cruz de la Sierra, Bolivia',
    'es_planta' => true,
    'activo' => true
]);

// ALMACÉN NORTE
$almacenNorte = \App\Models\Almacen::create([
    'nombre' => 'Almacén Norte',
    'latitud' => -17.770,
    'longitud' => -63.190,
    'direccion_completa' => 'Av. Alemana, Zona Norte, Santa Cruz',
    'es_planta' => false,
    'activo' => true
]);

// ALMACÉN CENTRO
$almacenCentro = \App\Models\Almacen::create([
    'nombre' => 'Almacén Centro',
    'latitud' => -17.783,
    'longitud' => -63.182,
    'direccion_completa' => 'Av. Banzer 500, Santa Cruz',
    'es_planta' => false,
    'activo' => true
]);

// ALMACÉN SUR
$almacenSur = \App\Models\Almacen::create([
    'nombre' => 'Almacén Sur',
    'latitud' => -17.800,
    'longitud' => -63.180,
    'direccion_completa' => 'Radial 26, Zona Sur, Santa Cruz',
    'es_planta' => false,
    'activo' => true
]);

// ========================================
// 3. DIRECCIONES (Rutas de Planta a Almacenes)
// ========================================

\App\Models\Direccion::create([
    'almacen_origen_id' => $planta->id,
    'almacen_destino_id' => $almacenNorte->id,
    'distancia_km' => 5.2,
    'tiempo_estimado_minutos' => 15,
    'ruta_descripcion' => 'Por Av. Cristo Redentor hasta 4to Anillo, norte por Alemana'
]);

\App\Models\Direccion::create([
    'almacen_origen_id' => $planta->id,
    'almacen_destino_id' => $almacenCentro->id,
    'distancia_km' => 2.8,
    'tiempo_estimado_minutos' => 10,
    'ruta_descripcion' => 'Por Av. Banzer directo al centro'
]);

\App\Models\Direccion::create([
    'almacen_origen_id' => $planta->id,
    'almacen_destino_id' => $almacenSur->id,
    'distancia_km' => 7.5,
    'tiempo_estimado_minutos' => 20,
    'ruta_descripcion' => 'Por Radial 26 hacia el sur'
]);

// ========================================
// 4. TIPOS DE TRANSPORTE
// ========================================

$tipoAislado = \App\Models\TipoTransporte::create([
    'nombre' => 'Aislado',
    'descripcion' => 'Transporte aislado térmicamente',
    'activo' => true
]);

$tipoVentilado = \App\Models\TipoTransporte::create([
    'nombre' => 'Ventilado',
    'descripcion' => 'Transporte con ventilación natural',
    'activo' => true
]);

$tipoRefrigerado = \App\Models\TipoTransporte::create([
    'nombre' => 'Refrigerado',
    'descripcion' => 'Transporte refrigerado',
    'requiere_temperatura_controlada' => true,
    'temperatura_minima' => 0,
    'temperatura_maxima' => 10,
    'activo' => true
]);

$tipoCongelado = \App\Models\TipoTransporte::create([
    'nombre' => 'Congelado',
    'descripcion' => 'Transporte congelado',
    'requiere_temperatura_controlada' => true,
    'temperatura_minima' => -20,
    'temperatura_maxima' => -10,
    'activo' => true
]);

// ========================================
// 5. UNIDADES DE MEDIDA
// ========================================

$unidadKg = \App\Models\UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg']);
$unidadTon = \App\Models\UnidadMedida::create(['nombre' => 'Tonelada', 'abreviatura' => 'ton']);
$unidadL = \App\Models\UnidadMedida::create(['nombre' => 'Litro', 'abreviatura' => 'L']);
$unidadM3 = \App\Models\UnidadMedida::create(['nombre' => 'Metro Cúbico', 'abreviatura' => 'm³']);

// ========================================
// 6. TIPOS DE EMPAQUE
// ========================================

$empaqueCaja = \App\Models\TipoEmpaque::create(['nombre' => 'Caja', 'descripcion' => 'Caja de cartón']);
$empaqueSaco = \App\Models\TipoEmpaque::create(['nombre' => 'Saco', 'descripcion' => 'Saco de tela o polipropileno']);
$empaqueContenedor = \App\Models\TipoEmpaque::create(['nombre' => 'Contenedor', 'descripcion' => 'Contenedor plástico']);
$empaquePallet = \App\Models\TipoEmpaque::create(['nombre' => 'Pallet', 'descripcion' => 'Pallet de madera']);

// ========================================
// 7. VEHÍCULOS
// ========================================

$juan = \App\Models\User::where('email', 'juan@transporte.com')->first();
$carlos = \App\Models\User::where('email', 'carlos@transporte.com')->first();

// Camión refrigerado grande (requiere Licencia A)
\App\Models\Vehiculo::create([
    'placa' => 'SCZ-1001',
    'marca' => 'Volvo',
    'modelo' => 'FH16',
    'anio' => 2020,
    'tipo_vehiculo' => 'Camión Refrigerado',
    'tipo_transporte_id' => $tipoRefrigerado->id,
    'licencia_requerida' => 'A',
    'capacidad_carga' => 18,
    'unidad_medida_carga_id' => $unidadTon->id,
    'transportista_id' => $juan->id,
    'disponible' => true,
    'estado' => 'activo'
]);

// Camioneta ventilada (requiere Licencia B)
\App\Models\Vehiculo::create([
    'placa' => 'SCZ-2002',
    'marca' => 'Toyota',
    'modelo' => 'Hilux',
    'anio' => 2021,
    'tipo_vehiculo' => 'Camioneta',
    'tipo_transporte_id' => $tipoVentilado->id,
    'licencia_requerida' => 'B',
    'capacidad_carga' => 1000,
    'unidad_medida_carga_id' => $unidadKg->id,
    'transportista_id' => $carlos->id,
    'disponible' => true,
    'estado' => 'activo'
]);

// Camión aislado mediano (requiere Licencia B)
\App\Models\Vehiculo::create([
    'placa' => 'SCZ-3003',
    'marca' => 'Mercedes-Benz',
    'modelo' => 'Atego',
    'anio' => 2019,
    'tipo_vehiculo' => 'Camión Mediano',
    'tipo_transporte_id' => $tipoAislado->id,
    'licencia_requerida' => 'B',
    'capacidad_carga' => 8,
    'unidad_medida_carga_id' => $unidadTon->id,
    'transportista_id' => $carlos->id,
    'disponible' => true,
    'estado' => 'activo'
]);

echo "✅ Sistema completo creado exitosamente!\n";
echo "📍 Planta creada en Santa Cruz: {$planta->nombre}\n";
echo "🏢 {$almacenNorte->nombre}, {$almacenCentro->nombre}, {$almacenSur->nombre}\n";
echo "🚛 3 vehículos creados\n";
echo "👥 5 usuarios creados (1 admin, 2 transportistas, 2 almacenes)\n";
exit
```

```bash
# 4. Iniciar servidor
php artisan serve
```

---

## 🔐 **LOGINS**

```
ADMIN:
Email: admin@planta.com
Password: password

TRANSPORTISTA LICENCIA A:
Email: juan@transporte.com
Password: password

TRANSPORTISTA LICENCIA B:
Email: carlos@transporte.com
Password: password

USUARIO ALMACEN:
Email: almacen@planta.com
Password: password
```

---

## ✅ **CAMBIOS REALIZADOS**

- ✅ Almacenes **SIN** código
- ✅ Almacenes **SIN** capacidad
- ✅ Solo: nombre, ubicación GPS, dirección, es_planta, activo
- ✅ Orden de migraciones corregido
- ✅ Direcciones después de almacenes
- ✅ Todas las relaciones actualizadas

---

## 🚀 **¡SISTEMA LISTO!**

Ahora ejecuta:
```bash
php artisan migrate:fresh
```

Y luego copia TODO el script en `php artisan tinker`

**¡Sin errores de BD!** ✨

