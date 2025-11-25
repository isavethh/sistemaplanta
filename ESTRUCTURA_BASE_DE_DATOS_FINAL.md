# 🗄️ Estructura Final de Base de Datos - PlantaCRUDS

## 📊 Estructura Lógica y Completa

Esta es la estructura definitiva de la base de datos con todos los campos necesarios para el funcionamiento completo del sistema.

---

## 👤 Tabla: `users`

### Propósito
Gestiona todos los usuarios del sistema: administradores, clientes y transportistas.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| `name` | VARCHAR(255) | Nombre completo |
| `email` | VARCHAR(255) | Email (único) |
| `password` | VARCHAR(255) | Contraseña hasheada |
| `role` | VARCHAR(255) | Rol: admin, cliente, transportista, user |
| `tipo` | VARCHAR(255) | Tipo (redundante con role para flexibilidad) |
| `telefono` | VARCHAR(255) | Teléfono de contacto |
| `direccion` | TEXT | Dirección física |
| **`licencia`** | VARCHAR(255) | **Tipo de licencia: A, B o C** |
| **`disponible`** | BOOLEAN | **Si el transportista está disponible** |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Fecha de actualización |

### Lógica de Licencias

- **Licencia A**: Puede conducir cualquier vehículo (mayor jerarquía)
- **Licencia B**: Puede conducir vehículos medianos y pequeños
- **Licencia C**: Solo vehículos pequeños (menor jerarquía)

### Ejemplo de Datos

```sql
-- Administrador
INSERT INTO users (name, email, password, role, tipo) VALUES
('Admin Principal', 'admin@planta.com', bcrypt('password'), 'admin', 'admin');

-- Cliente
INSERT INTO users (name, email, password, role, tipo, telefono, direccion) VALUES
('Empresa ABC', 'contacto@abc.com', bcrypt('password'), 'cliente', 'cliente', '77123456', 'Av. Banzer 123');

-- Transportista con Licencia A
INSERT INTO users (name, email, password, role, tipo, telefono, licencia, disponible) VALUES
('Juan Pérez', 'juan@transporte.com', bcrypt('password'), 'transportista', 'transportista', '77888999', 'A', true);
```

---

## 📍 Tabla: `direcciones`

### Propósito
Almacena todos los puntos geográficos: la planta (origen fijo) y puntos de entrega.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| **`nombre`** | VARCHAR(255) | **Nombre del punto** |
| `descripcion` | TEXT | Descripción de la dirección |
| **`latitud`** | DECIMAL(10,7) | **Coordenada GPS** |
| **`longitud`** | DECIMAL(10,7) | **Coordenada GPS** |
| **`es_planta`** | BOOLEAN | **TRUE si es el punto de origen** |
| **`es_punto_entrega`** | BOOLEAN | **TRUE si es punto de entrega** |

### Ejemplo

```sql
-- Planta (Punto Fijo)
INSERT INTO direcciones (nombre, descripcion, latitud, longitud, es_planta, es_punto_entrega) VALUES
('Planta Principal', 'Av. Cristo Redentor, Santa Cruz de la Sierra', -17.783333, -63.182778, true, false);

-- Puntos de Entrega
INSERT INTO direcciones (nombre, descripcion, latitud, longitud, es_planta, es_punto_entrega) VALUES
('Almacén Centro', 'Av. Banzer 500, Santa Cruz', -17.783, -63.182, false, true),
('Almacén Norte', 'Barrio Norte, Santa Cruz', -17.770, -63.190, false, true);
```

---

## 🚚 Tabla: `vehiculos`

### Propósito
Registra todos los vehículos de la flota con sus capacidades y requisitos.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| `placa` | VARCHAR(255) | Placa única del vehículo |
| **`marca`** | VARCHAR(255) | **Marca del vehículo** |
| **`modelo`** | VARCHAR(255) | **Modelo** |
| **`anio`** | INTEGER | **Año de fabricación** |
| **`tipo_vehiculo`** | VARCHAR(255) | **Camión, Camioneta, etc** |
| **`licencia_requerida`** | VARCHAR(255) | **A, B o C** |
| **`capacidad_carga`** | DECIMAL(10,2) | **Capacidad en kg** |
| **`capacidad_volumen`** | DECIMAL(10,2) | **Capacidad en m³** |
| `transportista_id` | BIGINT | ID del transportista asignado |
| **`disponible`** | BOOLEAN | **Si está disponible** |
| **`estado`** | VARCHAR(255) | **activo, mantenimiento, inactivo** |

### Lógica de Asignación

1. El vehículo tiene una `licencia_requerida` (A, B o C)
2. Solo transportistas con licencia igual o superior pueden conducirlo
3. Ejemplo: Un camión grande requiere licencia A
4. Un transportista con licencia B NO puede conducir ese camión

### Ejemplo

```sql
-- Camión grande (requiere licencia A)
INSERT INTO vehiculos (placa, marca, modelo, anio, tipo_vehiculo, licencia_requerida, capacidad_carga, capacidad_volumen, disponible, estado) VALUES
('ABC-1234', 'Volvo', 'FH16', 2020, 'Camión', 'A', 18000.00, 50.00, true, 'activo');

-- Camioneta (requiere licencia B)
INSERT INTO vehiculos (placa, marca, modelo, anio, tipo_vehiculo, licencia_requerida, capacidad_carga, capacidad_volumen, disponible, estado) VALUES
('XYZ-5678', 'Toyota', 'Hilux', 2021, 'Camioneta', 'B', 1000.00, 5.00, true, 'activo');
```

---

## 🏢 Tabla: `almacenes`

### Propósito
Puntos de almacenamiento que también funcionan como puntos de entrega.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| `nombre` | VARCHAR(255) | Nombre del almacén |
| **`codigo`** | VARCHAR(255) | **Código único** |
| `direccion_id` | BIGINT | Referencia a direcciones |
| **`encargado_id`** | BIGINT | **Usuario encargado** |
| **`capacidad_maxima`** | DECIMAL(12,2) | **Capacidad máxima en kg** |
| **`capacidad_actual`** | DECIMAL(12,2) | **Peso actual almacenado** |
| **`activo`** | BOOLEAN | **Si está operativo** |

---

## 📦 Tabla: `envios`

### Propósito
Registro de todos los pedidos/envíos del sistema.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| **`codigo`** | VARCHAR(255) | **Código único del envío** |
| **`cliente_id`** | BIGINT | **Cliente que hace el pedido** |
| **`almacen_origen_id`** | BIGINT | **Almacén de origen (planta)** |
| **`direccion_destino_id`** | BIGINT | **Punto de entrega** |
| `categoria_id` | BIGINT | Categoría del envío |
| `fecha_creacion` | DATE | Fecha de creación |
| `fecha_estimada_entrega` | DATE | Fecha estimada |
| `hora_estimada` | TIME | Hora estimada |
| **`estado`** | VARCHAR(255) | **pendiente, asignado, en_transito, entregado, cancelado** |
| **`transportista_id`** | BIGINT | **Transportista asignado** |
| **`vehiculo_id`** | BIGINT | **Vehículo asignado** |
| `total_cantidad` | INTEGER | Total de unidades |
| `total_peso` | DECIMAL(12,3) | Peso total en kg |
| **`total_volumen`** | DECIMAL(12,3) | **Volumen total en m³** |
| `total_precio` | DECIMAL(12,2) | Precio total |
| `observaciones` | TEXT | Notas adicionales |
| **`fecha_asignacion`** | TIMESTAMP | **Cuándo se asignó** |
| **`fecha_inicio_transito`** | TIMESTAMP | **Cuándo inició el viaje** |
| **`fecha_entrega`** | TIMESTAMP | **Cuándo se entregó** |

### Estados del Envío

1. **pendiente**: Recién creado, esperando asignación
2. **asignado**: Transportista y vehículo asignados
3. **en_transito**: Vehículo en camino al destino
4. **entregado**: Completado exitosamente
5. **cancelado**: Cancelado por algún motivo

---

## 📦 Tabla: `envio_productos`

### Propósito
Detalle de productos en cada envío (un envío puede tener múltiples productos).

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| `envio_id` | BIGINT | Referencia al envío |
| `producto_nombre` | VARCHAR(255) | Nombre del producto |
| `descripcion` | TEXT | Descripción |
| `cantidad` | INTEGER | Cantidad de unidades |
| **`peso_unitario`** | DECIMAL(12,3) | **Peso por unidad en kg** |
| **`volumen_unitario`** | DECIMAL(12,3) | **Volumen por unidad en m³** |
| `precio_unitario` | DECIMAL(12,2) | Precio por unidad |
| **`total_peso`** | DECIMAL(12,3) | **Peso total (cantidad * peso_unitario)** |
| **`total_volumen`** | DECIMAL(12,3) | **Volumen total** |
| `total_precio` | DECIMAL(12,2) | Precio total |

---

## 🗃️ Tabla: `inventario_almacen`

### Propósito
Control de stock en cada almacén.

### Campos

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | BIGINT | ID único |
| `almacen_id` | BIGINT | Almacén donde está el producto |
| `envio_producto_id` | BIGINT | Referencia al envío |
| `producto_nombre` | VARCHAR(255) | Nombre del producto |
| `descripcion` | TEXT | Descripción |
| `cantidad` | INTEGER | Unidades en stock |
| **`peso_total`** | DECIMAL(12,3) | **Peso total** |
| **`volumen_total`** | DECIMAL(12,3) | **Volumen total** |
| `precio_unitario` | DECIMAL(12,2) | Precio por unidad |
| `fecha_ingreso` | DATE | Cuándo ingresó al almacén |
| **`lote`** | VARCHAR(255) | **Número de lote** |

---

## 🔄 Flujo Transaccional

### 1. Cliente Crea Pedido
```
Cliente → Selecciona productos → Crea envío
Estado: PENDIENTE
```

### 2. Sistema Asigna Transportista
```
Verificar:
- ¿Transportista disponible?
- ¿Tiene licencia adecuada?
- ¿Vehículo disponible?
- ¿Vehículo tiene capacidad suficiente?

Si TODO OK → Asignar
Estado: ASIGNADO
```

### 3. Transportista Inicia Viaje
```
Transportista → Iniciar ruta
Estado: EN_TRANSITO
fecha_inicio_transito: NOW()
```

### 4. Llega a Destino
```
Vehículo llega → Marcar como entregado
Estado: ENTREGADO
fecha_entrega: NOW()
Productos → Se registran en inventario del almacén destino
```

---

## 🎯 Validaciones Importantes

### Al Asignar Transportista

```php
// 1. Verificar licencia
$vehiculo = Vehiculo::find($vehiculoId);
$transportista = User::find($transportistaId);

if (!$transportista->puedeConducir($vehiculo->licencia_requerida)) {
    return "Transportista no tiene licencia adecuada";
}

// 2. Verificar disponibilidad
if (!$transportista->disponible || !$vehiculo->disponible) {
    return "Transportista o vehículo no disponible";
}

// 3. Verificar capacidad
if (!$vehiculo->puedeTransportar($envio->total_peso, $envio->total_volumen)) {
    return "Vehículo no tiene capacidad suficiente";
}
```

---

## 📊 Comandos para Crear Estructura

```bash
# 1. Ejecutar migraciones
php artisan migrate:fresh

# 2. Crear datos de ejemplo
php artisan tinker
```

```php
// Admin
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@planta.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'tipo' => 'admin'
]);

// Cliente
\App\Models\User::create([
    'name' => 'Empresa ABC',
    'email' => 'cliente@abc.com',
    'password' => bcrypt('password'),
    'role' => 'cliente',
    'tipo' => 'cliente',
    'telefono' => '77123456'
]);

// Transportista Licencia A
\App\Models\User::create([
    'name' => 'Juan Pérez',
    'email' => 'juan@trans.com',
    'password' => bcrypt('password'),
    'role' => 'transportista',
    'tipo' => 'transportista',
    'telefono' => '77888999',
    'licencia' => 'A',
    'disponible' => true
]);

// Dirección Planta
\App\Models\Direccion::create([
    'nombre' => 'Planta Principal',
    'descripcion' => 'Av. Cristo Redentor, Santa Cruz',
    'latitud' => -17.783333,
    'longitud' => -63.182778,
    'es_planta' => true,
    'es_punto_entrega' => false
]);
```

---

## ✅ Estructura Completa y Lista

- ✅ Usuarios con tipos y licencias
- ✅ Direcciones con coordenadas GPS
- ✅ Vehículos con capacidades y requisitos
- ✅ Almacenes con control de capacidad
- ✅ Envíos con seguimiento completo
- ✅ Productos con peso y volumen
- ✅ Inventario por almacén
- ✅ Flujo transaccional lógico

**Sistema**: PlantaCRUDS  
**Versión**: 2.0.0 FINAL  
**Estado**: ✅ OPTIMIZADO Y COMPLETO

