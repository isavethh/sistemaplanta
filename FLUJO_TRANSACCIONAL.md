# 📦 Flujo Transaccional del Sistema - PlantaCRUDS

## 🎯 Visión General

Este documento describe el flujo transaccional completo del sistema de gestión de planta y envíos, desde la creación de clientes hasta la entrega de productos.

---

## 🔄 Flujo Completo del Sistema

### 1️⃣ **CONFIGURACIÓN INICIAL**

#### A. Crear Direcciones (Puntos de Entrega)
1. Ir a **Direcciones** en el menú
2. Crear dirección de la **PLANTA** (punto fijo):
   - **Ubicación**: Santa Cruz de la Sierra, Bolivia
   - **Coordenadas fijas**: -17.783333, -63.182778
   - Esta es la dirección de origen
3. Crear puntos de entrega adicionales (se convierten en almacenes)

#### B. Crear Almacenes
1. Ir a **Gestión de Inventario > Almacenes**
2. Crear almacén seleccionando:
   - Nombre del almacén
   - Dirección (del dropdown de direcciones creadas)
3. **IMPORTANTE**: Los almacenes creados se convierten automáticamente en puntos de entrega disponibles

#### C. Crear Clientes
1. Ir a **Gestión de Usuarios > Clientes**
2. Registrar clientes con:
   - Nombre
   - Email
   - Teléfono
   - **Tipo**: debe ser "cliente"

#### D. Crear Productos y Categorías
1. Ir a **Gestión de Inventario > Categorías**
2. Crear categorías de productos
3. Ir a **Gestión de Inventario > Productos**
4. Crear productos asignándolos a categorías

---

### 2️⃣ **FLUJO DE ENVÍOS (TRANSACCIONAL)**

#### Paso 1: Crear un Envío
1. Ir a **Gestión de Envíos > Envíos**
2. Click en **"Nuevo Envío"**
3. Completar el formulario:
   - **Cliente** *(requerido)*: Seleccionar del dropdown de clientes creados
   - **Origen (Almacén/Planta)** *(requerido)*: Punto de origen del envío
   - **Punto de Entrega** *(requerido)*: Seleccionar solo de los almacenes/direcciones creados
   - **Categoría**: Opcional, para clasificar el envío
   - **Fecha y Hora Estimada**: Cuándo se espera entregar
   - **Productos**:
     - Nombre del producto
     - Cantidad
     - Peso por unidad (kg)
     - Precio por unidad ($)
     - Puede agregar múltiples productos con el botón "Agregar Otro Producto"

4. Click en **"Crear Envío"**
5. El sistema genera automáticamente:
   - ✅ Código único del envío (ENV-XXXXX)
   - ✅ Estado inicial: "pendiente"
   - ✅ Cálculo automático de totales

#### Paso 2: Ver Rutas en Tiempo Real
1. Ir a **Gestión de Envíos > Rutas en Tiempo Real**
2. Se cargan automáticamente los envíos pendientes en el panel izquierdo
3. Seleccionar un envío de la lista
4. Click en **"Iniciar Ruta"**
5. El sistema:
   - ✅ Cambia el estado a "en_transito"
   - ✅ Muestra el mapa con:
     - 🔴 Marcador rojo = Planta (origen fijo en Santa Cruz)
     - 🔵 Marcador azul = Vehículo en movimiento
     - 🟢 Marcador verde = Punto de entrega (destino)
   - ✅ Simula el movimiento del vehículo en tiempo real
   - ✅ Muestra barra de progreso
6. Al llegar al destino:
   - ✅ Estado cambia automáticamente a "entregado"
   - ✅ El envío desaparece de la lista de pendientes

#### Paso 3: Generar Código QR y Documento
1. Ir a **Gestión de Envíos > Códigos QR y Documentos**
2. Filtrar por:
   - **Cliente**: Ver envíos de un cliente específico
   - **Estado**: pendiente, en_transito, entregado
3. Para cada envío:
   - **Ver QR**: Genera código QR con el código del envío
     - Se puede descargar como imagen PNG
   - **Ver Documento**: Genera documento PDF completo con:
     - ✅ Código QR
     - ✅ Información del cliente
     - ✅ Origen y destino
     - ✅ Lista de productos
     - ✅ Totales (cantidad, peso, precio)
     - ✅ Espacio para firma de recepción
     - ✅ Sello del transportista
   - **Tracking**: Ver ubicación en tiempo real

---

### 3️⃣ **GESTIÓN DE INVENTARIO**

#### Ver Inventario por Almacén
1. Ir a **Gestión de Inventario > Almacenes**
2. Click en **"Ver Inventario"** de cualquier almacén
3. Se muestra:
   - ✅ Estadísticas del almacén (total unidades, peso, valor)
   - ✅ Lista de productos en ese almacén
   - ✅ Información detallada de cada producto
4. El inventario se actualiza automáticamente cuando:
   - Se completa un envío a ese almacén
   - Se registran productos manualmente

#### Gestión Manual de Inventario
1. Ir a **Gestión de Inventario > Gestión de Inventario**
2. Ver todos los registros de inventario del sistema
3. Crear nuevos registros:
   - Almacén
   - Producto
   - Cantidad
   - Peso y precio
   - Fecha de llegada

---

## 📊 Flujo de Datos

```
CLIENTE hace PEDIDO
    ↓
Se crea ENVÍO con PRODUCTOS
    ↓
ENVÍO tiene estado "pendiente"
    ↓
Se inicia RUTA en tiempo real
    ↓
Estado cambia a "en_transito"
    ↓
Vehículo se simula moviéndose en MAPA
    ↓
Llega al PUNTO DE ENTREGA
    ↓
Estado cambia a "entregado"
    ↓
Se genera DOCUMENTO con QR
    ↓
Cliente puede ver su HISTORIAL de envíos
    ↓
Productos se registran en INVENTARIO del almacén destino
```

---

## 🔑 Conceptos Clave

### Direcciones
- **Dirección de la Planta**: Punto fijo en Santa Cruz de la Sierra (origen)
- **Puntos de Entrega**: Se crean al crear almacenes
- **Solo direcciones creadas** aparecen en el dropdown al crear envíos

### Estados de Envío
1. **Pendiente**: Envío creado pero no iniciado
2. **En Tránsito**: Vehículo en camino al destino
3. **Entregado**: Envío completado

### Inventario
- Se actualiza automáticamente con envíos completados
- Muestra productos que llegaron a cada almacén
- Se puede gestionar manualmente

### Códigos QR
- Cada envío tiene su código único
- El QR contiene el código del envío
- Permite tracking rápido del pedido
- Genera documento PDF imprimible

---

## 💡 Buenas Prácticas

### ✅ Hacer:
1. Crear primero todas las direcciones y almacenes
2. Registrar clientes antes de crear envíos
3. Asignar transportistas y vehículos a los envíos
4. Revisar el documento antes de enviar al cliente
5. Usar el sistema de tracking para seguimiento
6. Mantener actualizado el inventario

### ❌ No Hacer:
1. No eliminar direcciones con almacenes asociados
2. No eliminar clientes con envíos activos
3. No modificar envíos en tránsito
4. No crear envíos sin productos

---

## 🎨 Interfaz de Usuario

### Menú Principal (Solo Funcionales)
```
├── Dashboard
├── GESTIÓN DE USUARIOS
│   ├── Usuarios
│   └── Clientes
├── GESTIÓN DE INVENTARIO
│   ├── Almacenes
│   ├── Gestión de Inventario
│   ├── Productos
│   ├── Categorías
│   └── Subcategorías
├── GESTIÓN DE ENVÍOS
│   ├── Envíos
│   ├── Rutas en Tiempo Real
│   ├── Códigos QR y Documentos
│   └── Direcciones
└── VEHÍCULOS Y TRANSPORTE
    ├── Vehículos
    └── Transportistas
```

---

## 📱 Características Principales

### Rutas en Tiempo Real
- ✅ Mapa interactivo con Leaflet
- ✅ Marcadores de origen (rojo), vehículo (azul) y destino (verde)
- ✅ Simulación de movimiento del vehículo
- ✅ Barra de progreso
- ✅ Actualización automática de estados

### Códigos QR
- ✅ Generación automática de QR para cada envío
- ✅ Descarga de imagen PNG
- ✅ Documentos PDF profesionales
- ✅ Filtrado por cliente y estado
- ✅ Tracking directo desde el documento

### Inventario
- ✅ Vista por almacén
- ✅ Estadísticas en tiempo real
- ✅ Gestión manual o automática
- ✅ Control de stock
- ✅ Valoración de inventario

---

## 🔧 Comandos para Iniciar

```bash
# 1. Migrar base de datos
php artisan migrate:fresh

# 2. Crear usuario de prueba
php artisan tinker
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@admin.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'tipo' => 'admin'
]);
exit

# 3. Crear cliente de prueba
php artisan tinker
\App\Models\User::create([
    'name' => 'Cliente Test',
    'email' => 'cliente@test.com',
    'password' => bcrypt('password'),
    'role' => 'cliente',
    'tipo' => 'cliente',
    'telefono' => '12345678'
]);
exit

# 4. Iniciar servidor
php artisan serve
```

---

## 📞 Soporte

Para cualquier duda sobre el flujo transaccional:
1. Revisar este documento
2. Consultar el README.md
3. Ver INSTRUCCIONES_MIGRACION.md para problemas de BD

---

**Sistema**: PlantaCRUDS - Sistema de Gestión Transaccional  
**Versión**: 1.0.0  
**Ubicación**: Santa Cruz de la Sierra, Bolivia  
**Estado**: ✅ OPERATIVO

