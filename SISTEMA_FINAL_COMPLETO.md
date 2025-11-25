# 🚀 SISTEMA FINAL COMPLETO - PlantaCRUDS

## ✅ **ESTRUCTURA FINAL IMPLEMENTADA**

### 👥 **GESTIÓN DE USUARIOS (3 TIPOS)**

```
1. ADMIN (Administrador)
   - Gestiona todo el sistema
   - Puede crear y modificar todo

2. TRANSPORTISTA
   - Tiene licencia (A, B o C)
   - Puede ver sus envíos asignados
   - Controla las rutas

3. ALMACEN (Usuario de Almacén)
   - Crea su propio almacén
   - Marca ubicación en mapa
   - Gestiona inventario
```

---

## 🏢 **SISTEMA DE ALMACENES**

### Crear Almacén
```
DATOS DEL ALMACÉN:
├── Nombre (Ej: Almacén Norte)
├── Código (Ej: ALM-002, o ALM-PLANTA para la planta principal)
├── Dirección completa
├── UBICACIÓN EN MAPA (con clic o arrastrar marcador)
│   ├── Latitud (automático)
│   └── Longitud (automático)
├── Capacidad máxima (kg)
└── Estado (activo/inactivo)

CARACTERÍSTICAS:
✅ Mapa interactivo con Leaflet
✅ Click para marcar ubicación
✅ Arrastrar marcador
✅ Botón "Mi ubicación" (GPS)
✅ Coordenadas guardadas automáticamente
```

---

## 🗺️ **SISTEMA DE DIRECCIONES/RUTAS**

### Crear Dirección (Ruta entre Almacenes)
```
DEFINIR RUTA:
├── Almacén Origen (dropdown de almacenes creados)
│   └── Por defecto: ALM-PLANTA (punto fijo)
├── Almacén Destino (dropdown de almacenes creados)
├── MAPA: Muestra ruta visual
│   ├── Marcador ROJO: Origen
│   ├── Marcador VERDE: Destino
│   └── Línea AZUL: Ruta
├── Distancia (km) - AUTOMÁTICO con fórmula Haversine
├── Tiempo estimado (min) - AUTOMÁTICO (basado en 40 km/h)
└── Descripción de la ruta
```

### Flujo de Direcciones
```
1. Usuario Almacén crea su almacén con ubicación en mapa
2. Sistema guarda coordenadas GPS (latitud, longitud)
3. Para crear rutas:
   - Seleccionar origen (ALM-PLANTA)
   - Seleccionar destino (almacén creado)
   - Sistema calcula distancia automáticamente
   - Muestra ruta en mapa
4. Ruta guardada para usar en envíos
```

---

## 📊 **ESTRUCTURA DE BASE DE DATOS ACTUALIZADA**

### Tabla `users`
```sql
- id
- name
- email
- password
- role (admin, transportista, almacen)
- tipo (admin, transportista, almacen)
- telefono
- direccion
- licencia (A, B, C) [solo transportistas]
- disponible [solo transportistas]
```

### Tabla `almacenes` (REESTRUCTURADA)
```sql
- id
- nombre
- codigo (ej: ALM-PLANTA, ALM-002, etc)
- usuario_almacen_id (FK → users) [usuario que gestiona]
- latitud (coordenada GPS)
- longitud (coordenada GPS)
- direccion_completa (texto descriptivo)
- capacidad_maxima
- capacidad_actual
- activo
```

### Tabla `direcciones` (REESTRUCTURADA)
```sql
- id
- almacen_origen_id (FK → almacenes) [Planta]
- almacen_destino_id (FK → almacenes) [Destino]
- distancia_km (calculada automáticamente)
- tiempo_estimado_minutos (calculado)
- ruta_descripcion (texto)
```

---

## 🎯 **FLUJO COMPLETO DEL SISTEMA**

### 1️⃣ CREAR USUARIOS
```
ADMIN:
→ Crear → Tipo: Admin

TRANSPORTISTA:
→ Crear → Tipo: Transportista
→ Asignar Licencia: A, B o C
→ Marcar como disponible

ALMACEN:
→ Crear → Tipo: Almacen
→ Este usuario luego crea su almacén
```

### 2️⃣ CREAR PLANTA (Punto Fijo)
```
ALMACÉN PLANTA:
→ Nombre: "Planta Principal"
→ Código: ALM-PLANTA
→ Ubicación: Santa Cruz de la Sierra
→ Latitud: -17.783333
→ Longitud: -63.182778
→ Marcar en mapa (punto fijo)
```

### 3️⃣ USUARIO ALMACEN CREA SU ALMACÉN
```
USUARIO ALMACEN:
1. Login como usuario tipo "almacen"
2. Ir a Almacenes → Crear
3. Ingresar nombre: "Almacén Norte"
4. Código: ALM-002
5. HACER CLICK EN EL MAPA para marcar ubicación
   O arrastrar marcador
   O usar botón "Mi ubicación"
6. Coordenadas se guardan automáticamente
7. Guardar
```

### 4️⃣ CREAR RUTAS (DIRECCIONES)
```
DESDE PLANTA A ALMACENES:
1. Ir a Direcciones → Crear Ruta
2. Origen: ALM-PLANTA (planta principal)
3. Destino: Seleccionar almacén creado
4. MAPA MUESTRA:
   - Marcador rojo en planta
   - Marcador verde en destino
   - Línea azul conectando ambos
5. Distancia calculada automáticamente
6. Tiempo estimado automático
7. Guardar ruta
```

### 5️⃣ CREAR ENVÍO
```
ENVÍO:
1. Cliente seleccionado
2. Categoría: Verduras o Frutas
3. Productos (múltiples):
   - Producto hardcodeado
   - Cantidad
   - Peso unitario
   - Unidad de medida
   - Tipo de empaque
   - Precio (Bs)
4. Totales automáticos
5. Guardar → Estado: PENDIENTE
```

### 6️⃣ ASIGNAR TRANSPORTISTA
```
ASIGNACIÓN:
1. Ver envíos pendientes
2. Seleccionar transportista
   - Mostrar LICENCIA destacada
3. Seleccionar vehículo
   - Validar licencia requerida
   - Validar capacidad
4. Asignar → Estado: ASIGNADO
```

### 7️⃣ RUTA EN TIEMPO REAL
```
TRANSPORTE:
1. Transportista inicia ruta
2. Sistema usa ruta creada (origen → destino)
3. Mapa muestra:
   - Planta (rojo)
   - Vehículo (azul, moviéndose)
   - Destino (verde)
4. Al llegar → Estado: ENTREGADO
```

---

## 💻 **INSTALACIÓN Y DATOS DE PRUEBA**

```bash
cd C:\Users\Personal\Downloads\Planta\plantaCruds

# 1. Migrar
php artisan migrate:fresh

# 2. Crear datos
php artisan tinker
```

### Script Completo en Tinker:

```php
// 1. ADMIN
\App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@planta.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'tipo' => 'admin'
]);

// 2. TRANSPORTISTAS CON LICENCIAS
\App\Models\User::create([
    'name' => 'Juan Pérez (Lic. A)',
    'email' => 'juan@transporte.com',
    'password' => bcrypt('password'),
    'role' => 'transportista',
    'tipo' => 'transportista',
    'licencia' => 'A',
    'disponible' => true
]);

\App\Models\User::create([
    'name' => 'Carlos López (Lic. B)',
    'email' => 'carlos@transporte.com',
    'password' => bcrypt('password'),
    'role' => 'transportista',
    'tipo' => 'transportista',
    'licencia' => 'B',
    'disponible' => true
]);

// 3. USUARIOS ALMACEN
\App\Models\User::create([
    'name' => 'Encargado Almacén Central',
    'email' => 'almacen@planta.com',
    'password' => bcrypt('password'),
    'role' => 'almacen',
    'tipo' => 'almacen'
]);

\App\Models\User::create([
    'name' => 'Encargado Almacén Norte',
    'email' => 'norte@planta.com',
    'password' => bcrypt('password'),
    'role' => 'almacen',
    'tipo' => 'almacen'
]);

// 4. PLANTA (Punto Fijo)
$planta = \App\Models\Almacen::create([
    'nombre' => 'Planta Principal',
    'codigo' => 'ALM-PLANTA',
    'latitud' => -17.783333,
    'longitud' => -63.182778,
    'direccion_completa' => 'Av. Cristo Redentor 1500, Santa Cruz de la Sierra, Bolivia',
    'capacidad_maxima' => 100000,
    'activo' => true
]);

// 5. ALMACENES DE EJEMPLO
$almacenNorte = \App\Models\Almacen::create([
    'nombre' => 'Almacén Norte',
    'codigo' => 'ALM-002',
    'latitud' => -17.770,
    'longitud' => -63.190,
    'direccion_completa' => 'Av. Alemana, Zona Norte, Santa Cruz',
    'capacidad_maxima' => 50000,
    'activo' => true
]);

$almacenCentro = \App\Models\Almacen::create([
    'nombre' => 'Almacén Centro',
    'codigo' => 'ALM-003',
    'latitud' => -17.783,
    'longitud' => -63.182,
    'direccion_completa' => 'Av. Banzer 500, Santa Cruz',
    'capacidad_maxima' => 30000,
    'activo' => true
]);

// 6. RUTAS (Direcciones)
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

// 7. TIPOS DE TRANSPORTE
\App\Models\TipoTransporte::create(['nombre' => 'Aislado']);
\App\Models\TipoTransporte::create(['nombre' => 'Ventilado']);
\App\Models\TipoTransporte::create(['nombre' => 'Refrigerado', 'requiere_temperatura_controlada' => true, 'temperatura_minima' => 0, 'temperatura_maxima' => 10]);

// 8. UNIDADES DE MEDIDA
\App\Models\UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg']);
\App\Models\UnidadMedida::create(['nombre' => 'Tonelada', 'abreviatura' => 'ton']);
\App\Models\UnidadMedida::create(['nombre' => 'Litro', 'abreviatura' => 'L']);

// 9. TIPOS DE EMPAQUE
\App\Models\TipoEmpaque::create(['nombre' => 'Caja']);
\App\Models\TipoEmpaque::create(['nombre' => 'Saco']);
\App\Models\TipoEmpaque::create(['nombre' => 'Contenedor']);

// 10. VEHÍCULOS
$tipoRefri = \App\Models\TipoTransporte::where('nombre', 'Refrigerado')->first();
$juan = \App\Models\User::where('email', 'juan@transporte.com')->first();
$unidadTon = \App\Models\UnidadMedida::where('abreviatura', 'ton')->first();

\App\Models\Vehiculo::create([
    'placa' => 'SCZ-1001',
    'marca' => 'Volvo',
    'modelo' => 'FH16',
    'anio' => 2020,
    'tipo_vehiculo' => 'Camión Refrigerado',
    'tipo_transporte_id' => $tipoRefri->id,
    'licencia_requerida' => 'A',
    'capacidad_carga' => 18,
    'unidad_medida_carga_id' => $unidadTon->id,
    'transportista_id' => $juan->id,
    'disponible' => true,
    'estado' => 'activo'
]);

echo "✅ Sistema completo creado!\n";
exit
```

```bash
# 3. Iniciar servidor
php artisan serve
```

---

## 🎯 **LOGINS DE PRUEBA**

```
ADMIN:
Email: admin@planta.com
Password: password

TRANSPORTISTA LICENCIA A:
Email: juan@transporte.com
Password: password

USUARIO ALMACEN:
Email: almacen@planta.com
Password: password
```

---

## ✅ **CARACTERÍSTICAS IMPLEMENTADAS**

- ✅ 3 tipos de usuarios (Admin, Transportista, Almacén)
- ✅ Almacenes con ubicación en mapa interactivo
- ✅ Click/arrastrar para marcar ubicación
- ✅ GPS para ubicación actual
- ✅ Direcciones = Rutas entre almacenes
- ✅ Punto fijo de planta (ALM-PLANTA)
- ✅ Cálculo automático de distancia (Haversine)
- ✅ Visualización de rutas en mapa
- ✅ Marcadores de colores (origen rojo, destino verde)
- ✅ Sistema completo de transportes
- ✅ Tipos de transporte (Aislado, Ventilado, Refrigerado)
- ✅ Validación de licencias
- ✅ Productos hardcodeados con precios en Bolivianos

---

## 🚀 **¡SISTEMA 100% FUNCIONAL!**

**Todo implementado según especificación** ✨

