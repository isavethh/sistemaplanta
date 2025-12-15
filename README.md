# 🏭 PlantaCRUDS - Sistema de Gestión Integral de Planta

## 📖 ¿Qué es PlantaCRUDS?

**PlantaCRUDS** es un sistema de gestión empresarial desarrollado con **Laravel 11** que controla toda la operación logística de una planta de distribución. Este sistema gestiona inventarios, envíos, vehículos, transportistas y proporciona seguimiento en tiempo real mediante integraciones con otros microservicios.

### 🎯 Propósito del Sistema

Imagina que tienes una empresa que:
- Recibe pedidos de diferentes almacenes
- Tiene una flota de vehículos y transportistas
- Necesita asignar envíos a transportistas
- Requiere seguimiento GPS en tiempo real
- Debe generar documentos automáticos (propuestas, notas de entrega, etc.)
- Necesita integrarse con otros sistemas (almacenes, trazabilidad)

**PlantaCRUDS** es el "cerebro" que coordina todo esto.

---

## 🏗️ Arquitectura del Sistema: Microservicios

Este proyecto forma parte de un **ecosistema de microservicios** que trabajan juntos. Es importante entender cómo se integran:

```
┌─────────────────────────────────────────────────────────────────┐
│                    ECOSISTEMA DE MICROSERVICIOS                  │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────┐      ┌──────────────────┐      ┌──────────────────┐
│  Sistema de      │      │   PlantaCRUDS    │      │   Trazabilidad   │
│  Almacenes       │◄────►│   (Este Sistema) │◄────►│   (Node.js)      │
│  (Laravel)       │      │   (Laravel)      │      │                  │
│  Puerto: 8002    │      │   Puerto: 8001   │      │   Puerto: 8000   │
└──────────────────┘      └──────────────────┘      └──────────────────┘
         │                        │                           │
         │                        │                           │
         └────────────────────────┴───────────────────────────┘
                                  │
                                  │
                    ┌─────────────▼─────────────┐
                    │    App Móvil (React)      │
                    │    (Transportistas)      │
                    └──────────────────────────┘
```

### 🔄 Flujo de Integración entre Microservicios

#### 1. **Sistema de Almacenes (sistema-almacen-PSIII)**
- **Puerto**: `8002`
- **Rol**: Gestiona pedidos de clientes, inventario de almacenes
- **Comunicación con PlantaCRUDS**:
  - ✅ Envía pedidos a PlantaCRUDS para crear envíos
  - ✅ Recibe notificaciones cuando un envío es asignado
  - ✅ Recibe documentos PDF cuando un envío es entregado
  - ✅ Consulta estado de envíos

#### 2. **Sistema de Trazabilidad**
- **Puerto**: `8000`
- **Rol**: Gestiona el seguimiento GPS en tiempo real, rutas, ubicaciones
- **Comunicación con PlantaCRUDS**:
  - ✅ Envía pedidos desde almacenes a PlantaCRUDS
  - ✅ Recibe actualizaciones de estado de envíos
  - ✅ Proporciona datos de ubicación GPS para el seguimiento

#### 3. **PlantaCRUDS (Este Sistema)**
- **Puerto**: `8001`
- **Rol**: **Coordinador central** - Gestiona envíos, transportistas, vehículos, documentos
- **Comunicación**:
  - ✅ Recibe pedidos desde Almacenes y Trazabilidad
  - ✅ Asigna envíos a transportistas
  - ✅ Genera documentos PDF automáticamente
  - ✅ Envía notificaciones a Almacenes cuando hay cambios
  - ✅ Proporciona API para la App Móvil

#### 4. **App Móvil (React Native/Flutter)**
- **Rol**: Interfaz para transportistas
- **Comunicación con PlantaCRUDS**:
  - ✅ Login de transportistas
  - ✅ Ver envíos asignados
  - ✅ Aceptar/rechazar envíos
  - ✅ Iniciar envío (comienza tracking GPS)
  - ✅ Marcar como entregado
  - ✅ Reportar incidentes

---

## 📋 Tabla de Contenidos

- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación sin Docker](#-instalación-sin-docker-paso-a-paso)
- [Instalación con Docker](#-instalación-con-docker-paso-a-paso)
- [Configuración de Variables de Entorno](#-configuración-de-variables-de-entorno)
- [Integraciones Detalladas](#-integraciones-detalladas-con-otros-sistemas)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Comandos Útiles](#-comandos-útiles)
- [Solución de Problemas](#-solución-de-problemas)
- [Preguntas Frecuentes](#-preguntas-frecuentes)

---

## 📦 Requisitos del Sistema

### Para Instalación sin Docker

| Requisito | Versión Mínima | Versión Recomendada |
|-----------|---------------|---------------------|
| **PHP** | 8.2 | 8.4 |
| **Composer** | 2.0 | Última |
| **PostgreSQL** | 12.0 | 16.0 |
| **Node.js** | 18.0 | 20.0 (para assets) |
| **NPM** | 9.0 | Última |

**Extensiones PHP requeridas:**
- `pdo_pgsql` - Para conectar con PostgreSQL
- `zip` - Para manejar archivos comprimidos
- `bcmath` - Para cálculos matemáticos
- `gd` - Para manipulación de imágenes
- `mbstring` - Para manejo de strings multibyte
- `xml` - Para procesamiento XML
- `curl` - Para peticiones HTTP

**Verificar extensiones PHP:**
```bash
php -m | grep -E "pdo_pgsql|zip|bcmath|gd|mbstring|xml|curl"
```

### Para Instalación con Docker

| Requisito | Versión Mínima |
|-----------|---------------|
| **Docker** | 20.10 |
| **Docker Compose** | 2.0 |

**Verificar instalación:**
```bash
docker --version
docker compose version
```

---

## 🚀 Instalación sin Docker (Paso a Paso)

### Paso 1: Clonar o Descomprimir el Proyecto

```bash
# Navegar a la carpeta del proyecto
cd /ruta/del/proyecto/plantaCruds
```

### Paso 2: Instalar Dependencias de PHP

```bash
# Instalar todas las dependencias definidas en composer.json
composer install
```

**¿Qué hace este comando?**
- Lee `composer.json` que lista todas las librerías necesarias
- Descarga e instala paquetes como Laravel, AdminLTE, DomPDF, etc.
- Crea el archivo `vendor/autoload.php` que permite usar las clases

**Si tienes problemas:**
```bash
# Limpiar caché de Composer
composer clear-cache
# Reinstalar
composer install --no-cache
```

### Paso 3: Configurar Variables de Entorno

```bash
# Copiar el archivo de ejemplo
cp .env.example .env
```

**¿Qué es el archivo `.env`?**
- Contiene todas las configuraciones del sistema (base de datos, URLs, claves, etc.)
- **NUNCA** subas este archivo a Git (contiene información sensible)
- Cada desarrollador/entorno tiene su propio `.env`

### Paso 4: Generar Clave de Aplicación

```bash
php artisan key:generate
```

**¿Por qué es necesario?**
- Laravel usa esta clave para encriptar datos sensibles (sesiones, cookies, etc.)
- Cada instalación debe tener una clave única
- Se guarda automáticamente en `.env` como `APP_KEY`

### Paso 5: Configurar Base de Datos

**5.1. Crear la base de datos en PostgreSQL:**

```sql
-- Conectarse a PostgreSQL
psql -U postgres

-- Crear base de datos
CREATE DATABASE planta_cruds;

-- Crear usuario (opcional, puedes usar postgres)
CREATE USER planta_user WITH PASSWORD 'tu_contraseña_segura';
GRANT ALL PRIVILEGES ON DATABASE planta_cruds TO planta_user;
```

**5.2. Configurar en `.env`:**

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=planta_cruds
DB_USERNAME=planta_user
DB_PASSWORD=tu_contraseña_segura
```

**5.3. Probar la conexión:**

```bash
php artisan tinker
# En tinker, ejecutar:
DB::connection()->getPdo();
# Si no hay error, la conexión funciona ✅
```

### Paso 6: Ejecutar Migraciones

```bash
php artisan migrate
```

**¿Qué son las migraciones?**
- Son archivos que definen la estructura de las tablas de la base de datos
- Se encuentran en `database/migrations/`
- Cada migración crea/modifica tablas específicas
- Ejemplos: `create_envios_table.php`, `create_productos_table.php`

**¿Qué hace este comando?**
- Lee todas las migraciones en orden
- Crea las tablas en PostgreSQL
- Registra qué migraciones ya se ejecutaron (tabla `migrations`)

**Si hay errores:**
```bash
# Ver el error específico
php artisan migrate --verbose

# Si necesitas empezar de cero (¡CUIDADO! BORRA TODOS LOS DATOS)
php artisan migrate:fresh
```

### Paso 7: Ejecutar Seeders (Datos de Ejemplo)

```bash
php artisan db:seed
```

**¿Qué son los seeders?**
- Son archivos que insertan datos iniciales en la base de datos
- Se encuentran en `database/seeders/`
- Útiles para tener datos de prueba (usuarios, roles, categorías, etc.)

**Seeders disponibles:**
- `RolesAndPermissionsSeeder`: Crea roles (admin, transportista, etc.) y permisos
- `InitialSeeder`: Crea datos básicos (categorías, tipos de empaque, unidades de medida)
- `CrearUsuariosPorRolSeeder`: Crea usuarios de ejemplo por cada rol
- `TamanoVehiculoSeeder`: Crea tamaños de vehículos (pequeño, mediano, grande)
- `TiposEmpaqueSeeder`: Crea tipos de empaque (caja, bolsa, pallet, etc.)

**Credenciales por defecto** (si ejecutaste los seeders):
- **Email**: `admin@admin.com`
- **Password**: `password`

### Paso 8: Configurar Permisos de Storage

**Linux/Mac:**
```bash
chmod -R 775 storage bootstrap/cache
```

**Windows:**
- Asegúrate de que el usuario tenga permisos de escritura en:
  - `storage/` (para logs, archivos subidos, PDFs generados)
  - `bootstrap/cache/` (para caché de configuración)

**¿Por qué es necesario?**
- Laravel necesita escribir archivos (logs, PDFs, imágenes)
- Sin permisos, verás errores como "Permission denied"

### Paso 9: Crear Enlace Simbólico de Storage

```bash
php artisan storage:link
```

**¿Qué hace esto?**
- Crea un enlace simbólico de `storage/app/public` a `public/storage`
- Permite acceder a archivos públicos (imágenes, PDFs) vía URL
- Ejemplo: `http://localhost:8001/storage/incidentes/1/foto.jpg`

### Paso 10: Iniciar el Servidor de Desarrollo

```bash
php artisan serve
```

**O en un puerto específico:**
```bash
php artisan serve --port=8001
```

El sistema estará disponible en: `http://localhost:8001`

### Paso 11: Acceder al Sistema

1. Abre tu navegador
2. Navega a `http://localhost:8001`
3. Inicia sesión con las credenciales por defecto:
   - **Email**: `admin@admin.com`
   - **Password**: `password`

---

## 🐳 Instalación con Docker (Paso a Paso)

### ¿Por qué usar Docker?

- ✅ **Aislamiento**: No contamina tu sistema con dependencias
- ✅ **Consistencia**: Funciona igual en cualquier máquina
- ✅ **Facilidad**: Un solo comando instala todo
- ✅ **Producción**: Similar al entorno de producción

### Arquitectura Docker del Proyecto

```
┌─────────────────────────────────────────────────────────┐
│              Docker Compose Network                      │
│                                                           │
│  ┌──────────────┐         ┌──────────────┐              │
│  │   Nginx      │────────▶│   Laravel    │              │
│  │  (Puerto 80) │         │  (PHP-FPM)   │              │
│  │              │         │              │              │
│  │  orgtrack2   │         │ org2-laravel │              │
│  └──────────────┘         └──────┬───────┘              │
│                                   │                      │
│                            ┌──────▼───────┐              │
│                            │  PostgreSQL  │              │
│                            │  (Puerto 5432)│              │
│                            │   org2-db    │              │
│                            └──────────────┘              │
└─────────────────────────────────────────────────────────┘
```

**Contenedores:**
1. **org2-laravel**: Contenedor con PHP 8.4-FPM que ejecuta Laravel
2. **orgtrack2**: Contenedor con Nginx que sirve la aplicación
3. **org2-db**: Contenedor con PostgreSQL que almacena los datos

### Paso 1: Construir y Levantar los Contenedores

```bash
docker compose up --build -d
```

**¿Qué hace este comando?**
- `--build`: Construye las imágenes Docker desde cero
- `-d`: Ejecuta en modo "detached" (en segundo plano)

**¿Qué sucede automáticamente?**

El script `entrypoint.sh` se ejecuta cuando el contenedor inicia y hace TODO automáticamente:

1. ✅ **Crea `.env`** si no existe (desde `.env.example`)
2. ✅ **Instala dependencias** (`composer install`)
3. ✅ **Genera APP_KEY** (`php artisan key:generate`)
4. ✅ **Configura permisos** (`chmod -R 777 storage bootstrap/cache`)
5. ✅ **Ejecuta migraciones** (`php artisan migrate`)
6. ✅ **Ejecuta seeders** (`php artisan db:seed`)
7. ✅ **Inicia PHP-FPM** para servir la aplicación

**No necesitas ejecutar comandos manuales con `docker exec`** - todo se hace automáticamente.

### Paso 2: Verificar que los Contenedores Estén Corriendo

```bash
docker ps
```

Deberías ver tres contenedores:
```
CONTAINER ID   IMAGE              STATUS         PORTS     NAMES
abc123def456   nginx:latest       Up 2 minutes   80/tcp    orgtrack2
def456ghi789   planta-cruds       Up 2 minutes   9000/tcp  org2-laravel
ghi789jkl012   postgres:latest    Up 2 minutes   5432/tcp  org2-db
```

### Paso 3: Configurar Acceso Local (Desarrollo)

**Por defecto, Docker no expone puertos localmente** (configurado para producción).

**Para desarrollo local**, edita `docker-compose.yml`:

```yaml
nginx:
  ports:
    - "8080:80"  # Descomenta esta línea
```

Luego reinicia:
```bash
docker compose down
docker compose up -d
```

Accede al sistema en: `http://localhost:8080`

### Paso 4: Ver Logs (Opcional)

```bash
# Logs del contenedor Laravel
docker logs org2-laravel -f

# Logs de Nginx
docker logs orgtrack2 -f

# Logs de PostgreSQL
docker logs org2-db -f
```

### Estructura de Redes Docker

El `docker-compose.yml` define tres redes:

1. **org2-net**: Red interna para comunicación entre contenedores
2. **internal-network**: Red externa (debe existir, para integración con otros servicios)
3. **proxy-network**: Red externa para proxy reverso (debe existir)

**Si estas redes no existen**, créalas:
```bash
docker network create internal-network
docker network create proxy-network
```

---

## ⚙️ Configuración de Variables de Entorno

### Variables Principales del Sistema

Edita el archivo `.env` con tus configuraciones:

```env
# ============================================
# CONFIGURACIÓN DE LA APLICACIÓN
# ============================================
APP_NAME="PlantaCRUDS"
APP_ENV=local                    # local, staging, production
APP_KEY=base64:...               # Generado automáticamente
APP_DEBUG=true                   # false en producción
APP_URL=http://localhost:8001    # URL base de la aplicación

# ============================================
# BASE DE DATOS
# ============================================
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1               # En Docker usar: db
DB_PORT=5432
DB_DATABASE=planta_cruds         # En Docker usar: org2_db
DB_USERNAME=tu_usuario           # En Docker usar: admin
DB_PASSWORD=tu_contraseña        # En Docker usar: admin123

# ============================================
# INTEGRACIONES CON OTROS SISTEMAS
# ============================================
# URL del sistema de almacenes (sistema-almacen-PSIII)
ALMACEN_API_URL=http://localhost:8002/api

# URL del sistema de trazabilidad
TRAZABILIDAD_API_URL=http://localhost:8000/api

# URL de este sistema (usado por otros sistemas y app móvil)
PLANTA_CRUDS_API_URL=http://localhost:8001

# IMPORTANTE para App Móvil: Usa la IP de tu red local
# Encuentra tu IP con: ipconfig (Windows) o ifconfig (Linux/Mac)
# Ejemplo: http://10.26.10.192:8001
APP_MOBILE_API_URL=http://10.26.10.192:8001/api

# ============================================
# CACHE Y SESIONES
# ============================================
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# ============================================
# CORREO ELECTRÓNICO (Opcional)
# ============================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Explicación de Variables de Integración

#### `ALMACEN_API_URL`
- **Qué es**: URL base del sistema de almacenes (sistema-almacen-PSIII)
- **Cuándo se usa**: Cuando PlantaCRUDS necesita notificar a almacenes sobre cambios en envíos
- **Ejemplo**: `http://localhost:8002/api`
- **Endpoints usados**:
  - `POST /pedidos/{pedido_id}/asignacion-envio` - Notificar asignación
  - `POST /pedidos/{pedido_id}/documentos-entrega` - Enviar documentos PDF

#### `TRAZABILIDAD_API_URL`
- **Qué es**: URL base del sistema de trazabilidad
- **Cuándo se usa**: Cuando se envían documentos de entrega al sistema de trazabilidad
- **Ejemplo**: `http://localhost:8000/api`

#### `PLANTA_CRUDS_API_URL`
- **Qué es**: URL base de este sistema
- **Cuándo se usa**: Otros sistemas y la app móvil usan esta URL para conectarse
- **Ejemplo**: `http://localhost:8001`
- **Importante**: Si la app móvil se conecta desde otro dispositivo, usa la IP de tu red local:
  ```env
  PLANTA_CRUDS_API_URL=http://10.26.10.192:8001
  ```

#### `APP_MOBILE_API_URL`
- **Qué es**: URL completa de la API para la app móvil
- **Cuándo se usa**: La app móvil consulta `/api/config` para obtener esta URL
- **Ejemplo**: `http://10.26.10.192:8001/api`

---

## 🔗 Integraciones Detalladas con Otros Sistemas

### 1. Integración con Sistema de Almacenes

#### Flujo: Recibir Pedido desde Almacenes

```
Sistema Almacenes          PlantaCRUDS
     │                          │
     │  POST /api/pedido-almacen│
     │  {pedido_data}           │
     ├─────────────────────────▶│
     │                          │ Crea Envio
     │                          │ Crea EnvioProductos
     │                          │
     │  {success: true,         │
     │   envio_id: 123}         │
     │◀─────────────────────────┤
```

**Endpoint en PlantaCRUDS**: `POST /api/pedido-almacen`

**Datos que recibe**:
```json
{
  "codigo": "P1000001",
  "almacen_destino": "Almacén Centro",
  "almacen_destino_lat": -17.7833,
  "almacen_destino_lng": -63.1821,
  "fecha_requerida": "2025-01-15",
  "productos": [
    {
      "producto_nombre": "Producto A",
      "cantidad": 10,
      "peso_unitario": 2.5,
      "precio_unitario": 100.00
    }
  ],
  "webhook_url": "http://localhost:8002/api/pedidos/1/webhook"
}
```

**Qué hace PlantaCRUDS**:
1. Busca o crea el almacén destino
2. Crea el envío con estado `pendiente`
3. Crea los productos del envío
4. Retorna el `envio_id` y `codigo` del envío

#### Flujo: Notificar Asignación a Almacenes

```
PlantaCRUDS              Sistema Almacenes
     │                          │
     │ Usuario asigna envío     │
     │ a transportista           │
     │                          │
     │ Genera Propuesta PDF      │
     │                          │
     │ POST /api/pedidos/{id}/   │
     │     asignacion-envio     │
     │ {asignacion_data + PDF}  │
     ├─────────────────────────▶│
     │                          │ Guarda asignación
     │                          │ Guarda PDF
     │  {success: true}         │
     │◀─────────────────────────┤
```

**Cuándo se ejecuta**: Cuando un administrador asigna un envío a un transportista

**Servicio usado**: `AlmacenIntegrationService::notifyAsignacion()`

**Datos que se envían**:
```json
{
  "pedido_id": 1,
  "envio_id": 123,
  "envio_codigo": "ENV-250115-ABC12",
  "estado": "asignado",
  "transportista": {
    "id": 5,
    "nombre": "Juan Pérez",
    "email": "juan@example.com"
  },
  "vehiculo": {
    "id": 10,
    "placa": "ABC-123",
    "marca": "Toyota",
    "modelo": "Hiace"
  },
  "documentos": {
    "propuesta_vehiculos": "base64_encoded_pdf..."
  }
}
```

#### Flujo: Enviar Documentos de Entrega

```
PlantaCRUDS              Sistema Almacenes
     │                          │
     │ Transportista marca       │
     │ envío como entregado      │
     │                          │
     │ Genera 3 PDFs:            │
     │ - Propuesta Vehículos     │
     │ - Nota de Entrega         │
     │ - Trazabilidad Completa   │
     │                          │
     │ POST /api/pedidos/{id}/   │
     │     documentos-entrega    │
     │ {documentos: {...}}      │
     ├─────────────────────────▶│
     │                          │ Guarda documentos
     │                          │ Marca pedido entregado
     │  {success: true}         │
     │◀─────────────────────────┤
```

**Cuándo se ejecuta**: Cuando un transportista marca un envío como entregado

**Servicio usado**: `AlmacenIntegrationService::notifyEntrega()`

**Documentos generados automáticamente**:
1. **Propuesta de Vehículos**: PDF con información del vehículo asignado
2. **Nota de Entrega**: PDF con detalles de la entrega
3. **Trazabilidad Completa**: PDF con historial completo del envío

### 2. Integración con Sistema de Trazabilidad

#### Flujo: Recibir Pedido desde Trazabilidad

Similar al flujo con Almacenes, pero con estado especial:

```
Trazabilidad            PlantaCRUDS
     │                      │
     │ POST /api/pedido-    │
     │     almacen          │
     │ {pedido_data,        │
     │  origen: "trazabilidad"}│
     ├─────────────────────▶│
     │                      │ Crea Envio con estado
     │                      │ "pendiente_aprobacion_trazabilidad"
     │                      │
     │  {success: true,     │
     │   envio_id: 123}     │
     │◀─────────────────────┤
```

**Diferencia clave**: Los envíos desde Trazabilidad tienen estado `pendiente_aprobacion_trazabilidad` y requieren aprobación antes de asignarse.

#### Flujo: Enviar Documentos a Trazabilidad

Cuando un envío es entregado, también se envían documentos a Trazabilidad:

**Servicio usado**: `DocumentoEntregaService::enviarATrazabilidad()`

**Endpoint en Trazabilidad**: `POST /api/documentos-entrega`

### 3. Integración con App Móvil

#### Endpoints Principales para App Móvil

**1. Obtener Configuración**
```
GET /api/config
```
Retorna la URL base de la API y lista de endpoints disponibles.

**2. Login de Transportista**
```
POST /api/public/login-transportista
Body: { "email": "transportista@example.com", "password": "password" }
```

**3. Obtener Envíos Asignados**
```
GET /api/transportista/{id}/envios
```

**4. Aceptar Envío**
```
POST /api/envios/{id}/aceptar
```

**5. Rechazar Envío**
```
POST /api/envios/{id}/rechazar
```

**6. Iniciar Envío (Comienza Tracking GPS)**
```
POST /api/envios/{id}/iniciar
```

**7. Marcar como Entregado**
```
POST /api/envios/{id}/entregado
Body: {
  "foto_entrega": "base64_image...",
  "firma_cliente": "base64_image...",
  "observaciones": "Entrega exitosa"
}
```

**8. Reportar Incidente**
```
POST /api/envios/{envioId}/incidentes
Body: {
  "tipo_incidente": "Accidente",
  "descripcion": "Descripción del incidente",
  "accion": "cancelar", // o "continuar"
  "foto_base64": "base64_image...",
  "ubicacion_lat": -17.7833,
  "ubicacion_lng": -63.1821
}
```

---

## 📁 Estructura del Proyecto

```
plantaCruds/
├── app/                          # Código fuente de la aplicación
│   ├── Console/                   # Comandos Artisan personalizados
│   ├── Http/
│   │   ├── Controllers/          # Controladores (lógica de negocio)
│   │   │   ├── Api/              # Controladores de API
│   │   │   │   ├── EnvioController.php
│   │   │   │   ├── IncidenteController.php
│   │   │   │   └── TransportistaController.php
│   │   │   └── EnvioController.php
│   │   └── Middleware/           # Middleware (autenticación, CORS, etc.)
│   ├── Models/                   # Modelos Eloquent (representan tablas)
│   │   ├── Envio.php
│   │   ├── Producto.php
│   │   ├── Vehiculo.php
│   │   └── Incidente.php
│   └── Services/                 # Servicios (lógica reutilizable)
│       ├── AlmacenIntegrationService.php
│       ├── DocumentoEntregaService.php
│       └── PropuestaVehiculosService.php
├── config/                       # Archivos de configuración
│   ├── app.php
│   ├── database.php
│   ├── services.php              # URLs de integración
│   └── adminlte.php              # Configuración de AdminLTE
├── database/
│   ├── migrations/               # Migraciones (estructura de BD)
│   └── seeders/                  # Seeders (datos iniciales)
├── public/                       # Archivos públicos (accesibles vía web)
│   ├── index.php                 # Punto de entrada
│   ├── css/
│   └── js/
├── resources/
│   ├── views/                    # Vistas Blade (HTML)
│   │   ├── envios/
│   │   ├── incidentes/
│   │   └── layouts/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php                   # Rutas web (interfaz)
│   └── api.php                   # Rutas API (para integraciones)
├── storage/                      # Archivos generados
│   ├── app/
│   │   ├── public/               # Archivos públicos (PDFs, imágenes)
│   │   └── private/              # Archivos privados
│   └── logs/                     # Logs de la aplicación
├── docker-compose.yml            # Configuración Docker Compose
├── Dockerfile                    # Imagen Docker de Laravel
├── entrypoint.sh                 # Script de inicio automático
├── nginx.conf                    # Configuración de Nginx
├── composer.json                 # Dependencias PHP
└── .env                          # Variables de entorno (NO subir a Git)
```

---

## 🛠️ Comandos Útiles

### Comandos de Laravel (Sin Docker)

```bash
# ============================================
# LIMPIAR CACHÉ
# ============================================
php artisan cache:clear          # Limpiar caché de aplicación
php artisan config:clear         # Limpiar caché de configuración
php artisan route:clear          # Limpiar caché de rutas
php artisan view:clear           # Limpiar caché de vistas

# Limpiar todo
php artisan optimize:clear

# ============================================
# OPTIMIZAR APLICACIÓN (Producción)
# ============================================
php artisan optimize              # Optimizar todo
php artisan config:cache          # Cachear configuración
php artisan route:cache           # Cachear rutas
php artisan view:cache            # Cachear vistas

# ============================================
# BASE DE DATOS
# ============================================
php artisan migrate               # Ejecutar migraciones pendientes
php artisan migrate:fresh         # Refrescar BD (¡BORRA DATOS!)
php artisan migrate:rollback      # Revertir última migración
php artisan db:seed               # Ejecutar seeders
php artisan migrate:fresh --seed  # Refrescar y sembrar

# ============================================
# INFORMACIÓN
# ============================================
php artisan route:list            # Ver todas las rutas
php artisan tinker                # Consola interactiva de Laravel
```

### Comandos de Docker

```bash
# ============================================
# GESTIÓN DE CONTENEDORES
# ============================================
docker compose up -d              # Levantar contenedores
docker compose down               # Detener contenedores
docker compose restart            # Reiniciar contenedores
docker compose ps                 # Ver estado de contenedores

# ============================================
# LOGS
# ============================================
docker logs org2-laravel -f       # Logs de Laravel (seguimiento)
docker logs orgtrack2 -f          # Logs de Nginx
docker logs org2-db -f            # Logs de PostgreSQL

# ============================================
# EJECUTAR COMANDOS DENTRO DEL CONTENEDOR
# ============================================
docker exec -it org2-laravel bash              # Acceder al shell
docker exec -it org2-laravel php artisan migrate    # Ejecutar migraciones
docker exec -it org2-laravel composer install       # Instalar dependencias

# ============================================
# RECONSTRUIR DESDE CERO
# ============================================
docker compose down -v            # Eliminar contenedores y volúmenes
docker compose up --build -d      # Reconstruir y levantar
```

### Comandos de Desarrollo

```bash
# ============================================
# GENERAR CÓDIGO
# ============================================
php artisan make:controller NombreController
php artisan make:model NombreModel
php artisan make:migration create_nombre_table
php artisan make:seeder NombreSeeder

# ============================================
# AUTOLOAD
# ============================================
composer dump-autoload            # Regenerar autoload después de cambios
```

---

## 🔧 Solución de Problemas

### Error: "Class not found"

**Causa**: El autoload de Composer no está actualizado.

**Solución:**
```bash
# Sin Docker
composer dump-autoload
php artisan optimize:clear

# Con Docker
docker exec -it org2-laravel composer dump-autoload
docker exec -it org2-laravel php artisan optimize:clear
```

### Error de Permisos en Storage

**Síntomas**: Errores como "Permission denied" al generar PDFs o subir imágenes.

**Solución Linux/Mac:**
```bash
chmod -R 775 storage bootstrap/cache
```

**Solución Windows:**
- Click derecho en `storage` → Propiedades → Seguridad
- Asegúrate de que el usuario tenga permisos de escritura

**Solución Docker:**
El `entrypoint.sh` ya configura permisos automáticamente. Si persiste:
```bash
docker exec -it org2-laravel chmod -R 777 storage bootstrap/cache
```

### Error de Conexión a Base de Datos

**Síntomas**: "SQLSTATE[HY000] [2002] Connection refused"

**Solución Sin Docker:**
1. Verifica que PostgreSQL esté corriendo:
   ```bash
   # Linux/Mac
   sudo systemctl status postgresql
   
   # Windows
   # Abre "Servicios" y verifica que PostgreSQL esté "En ejecución"
   ```

2. Verifica las credenciales en `.env`:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=planta_cruds
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_contraseña
   ```

3. Prueba la conexión:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

**Solución Con Docker:**
1. Verifica que el contenedor de BD esté corriendo:
   ```bash
   docker ps | grep org2-db
   ```

2. Verifica que el `.env` use el nombre del servicio:
   ```env
   DB_HOST=db          # Nombre del servicio en docker-compose.yml
   DB_DATABASE=org2_db
   DB_USERNAME=admin
   DB_PASSWORD=admin123
   ```

### Error: "No application encryption key has been specified"

**Solución Sin Docker:**
```bash
php artisan key:generate
```

**Solución Con Docker:**
El `entrypoint.sh` ya genera la clave automáticamente. Si persiste:
```bash
docker exec -it org2-laravel php artisan key:generate
```

### Error en Docker: Contenedor no inicia

**Pasos de diagnóstico:**

1. **Ver los logs:**
   ```bash
   docker logs org2-laravel -f
   ```

2. **Verificar que los contenedores estén corriendo:**
   ```bash
   docker ps -a
   ```

3. **Reconstruir desde cero:**
   ```bash
   docker compose down -v
   docker compose up --build -d
   ```

4. **Verificar redes Docker:**
   ```bash
   docker network ls
   # Si faltan internal-network o proxy-network:
   docker network create internal-network
   docker network create proxy-network
   ```

### Error: "Port already in use"

**Solución Sin Docker:**
```bash
# Usar otro puerto
php artisan serve --port=8002
```

**Solución Con Docker:**
Edita `docker-compose.yml` y cambia el puerto:
```yaml
nginx:
  ports:
    - "8081:80"  # Cambia 8080 por otro puerto disponible
```

### Error: "Vendor folder affecting container"

**Síntomas**: El contenedor se queda en "Instalando dependencias"

**Causa**: La carpeta `vendor` local puede causar conflictos.

**Solución:**
```bash
# Eliminar vendor local (se reinstalará en el contenedor)
rm -rf vendor
docker compose up --build -d
```

### Error: Imágenes no se muestran

**Síntomas**: Las imágenes de incidentes no aparecen.

**Solución:**
```bash
# Crear enlace simbólico de storage
php artisan storage:link

# Con Docker
docker exec -it org2-laravel php artisan storage:link
```

### Error: "CORS policy" en App Móvil

**Síntomas**: La app móvil no puede conectarse a la API.

**Solución:**
1. Verifica que `APP_MOBILE_API_URL` use la IP de tu red local (no `localhost`):
   ```env
   APP_MOBILE_API_URL=http://10.26.10.192:8001/api
   ```

2. Verifica que el middleware CORS esté configurado en `config/cors.php`

---

## ❓ Preguntas Frecuentes

### ¿Cómo sé qué versión de PHP tengo?

```bash
php -v
```

### ¿Cómo encuentro mi IP local para la app móvil?

**Windows:**
```bash
ipconfig
# Busca "IPv4 Address" en la sección de tu adaptador de red
```

**Linux/Mac:**
```bash
ifconfig
# O
ip addr show
# Busca la IP en la red local (generalmente 192.168.x.x)
```

### ¿Puedo usar MySQL en lugar de PostgreSQL?

Sí, pero necesitarás:
1. Cambiar `DB_CONNECTION=mysql` en `.env`
2. Instalar la extensión `pdo_mysql` de PHP
3. Ajustar las migraciones si hay sintaxis específica de PostgreSQL

### ¿Cómo cambio el puerto del servidor?

**Sin Docker:**
```bash
php artisan serve --port=8002
```

**Con Docker:**
Edita `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8002:80"
```

### ¿Cómo veo los logs de la aplicación?

**Sin Docker:**
```bash
tail -f storage/logs/laravel.log
```

**Con Docker:**
```bash
docker logs org2-laravel -f
```

### ¿Cómo reseteo la base de datos?

**⚠️ CUIDADO: Esto borra TODOS los datos**

```bash
# Sin Docker
php artisan migrate:fresh --seed

# Con Docker
docker exec -it org2-laravel php artisan migrate:fresh --seed
```

### ¿Cómo actualizo las dependencias?

```bash
# Sin Docker
composer update

# Con Docker
docker exec -it org2-laravel composer update
```

---

## 📝 Notas Importantes

### Generación Automática de Documentos

El sistema genera automáticamente documentos PDF en estos momentos:

1. **Al Asignar un Envío**:
   - Genera **Propuesta de Vehículos** (PDF)
   - La envía automáticamente al sistema de almacenes
   - Se guarda en `storage/app/pedidos/{pedido_id}/documentos-entrega/`

2. **Al Marcar como Entregado**:
   - Genera **Propuesta de Vehículos**, **Nota de Entrega** y **Trazabilidad Completa** (PDFs)
   - Los envía automáticamente a:
     - Sistema de Almacenes (sistema-almacen-PSIII)
     - Sistema de Trazabilidad

### Scripts de Utilidad

- `enviar_propuestas_existentes.php`: Procesa envíos existentes y envía propuestas de vehículos faltantes

**Ejecutar:**
```bash
php enviar_propuestas_existentes.php
```

### Archivo entrypoint.sh

Este script se ejecuta automáticamente cuando el contenedor Docker inicia. Realiza:
- Creación de `.env` si no existe
- Instalación de dependencias
- Generación de `APP_KEY`
- Configuración de permisos
- Ejecución de migraciones
- Ejecución de seeders
- Inicio de PHP-FPM

**No necesitas ejecutar estos comandos manualmente** - todo se hace automáticamente.

### Configuración de Nginx

El archivo `nginx.conf` está configurado para:
- Servir archivos estáticos desde `/var/www/public`
- Procesar PHP a través de PHP-FPM en el contenedor `org2-laravel:9000`
- Manejar rutas de Laravel correctamente

El nombre del contenedor Laravel (`org2-laravel`) debe coincidir en:
- `docker-compose.yml` → `container_name: org2-laravel`
- `nginx.conf` → `fastcgi_pass org2-laravel:9000;`

---

## 📄 Licencia

Este proyecto es privado y de uso interno de la organización.

---

## 👨‍💻 Soporte y Contacto

Para soporte técnico, reportar problemas o solicitar nuevas funcionalidades, contactar al equipo de desarrollo.

---

**Versión**: 2.0.0  
**Última actualización**: Enero 2025  
**Framework**: Laravel 12  
**PHP**: 8.4  
**Base de Datos**: PostgreSQL  

---

**Desarrollado con ❤️ para la gestión eficiente de operaciones logísticas**
