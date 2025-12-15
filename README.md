# 🏭 PlantaCRUDS - Sistema de Gestión Integral de Planta

Sistema completo de gestión empresarial para control de inventarios, envíos, vehículos, transportistas y logística en tiempo real. Desarrollado con Laravel 11, integrado con sistemas de almacenes y trazabilidad mediante APIs REST.

---

## 📋 Tabla de Contenidos

- [Características Principales](#-características-principales)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación sin Docker](#-instalación-sin-docker)
- [Instalación con Docker](#-instalación-con-docker)
- [Configuración de Variables de Entorno](#-configuración-de-variables-de-entorno)
- [Integraciones con Otros Sistemas](#-integraciones-con-otros-sistemas)
- [Comandos Útiles](#-comandos-útiles)
- [Solución de Problemas](#-solución-de-problemas)

---

## ✨ Características Principales

### 🎯 Gestión de Inventario
- **Almacenes**: Administración completa con geolocalización (latitud/longitud)
- **Productos**: Catálogo con categorías, subcategorías, tipos de empaque y unidades de medida
- **Inventario**: Control de stock por almacén con valoración y reportes
- **Movimientos**: Historial de entradas y salidas con trazabilidad completa

### 🚚 Gestión de Envíos
- **Creación de Envíos**: Asignación de productos, almacén destino y transportista
- **Tracking en Tiempo Real**: Seguimiento GPS con WebSocket (Socket.IO) y visualización en mapa
- **Propuesta de Vehículos**: Cálculo automático según peso y volumen, generación de PDF
- **Estados de Envío**: `pendiente` → `asignado` → `aceptado` → `en_transito` → `entregado`
- **Documentos Automáticos**: 
  - **Al asignar**: Propuesta de Vehículos (se envía automáticamente al sistema de almacenes)
  - **Al entregar**: Nota de Entrega, Trazabilidad Completa, Propuesta de Vehículos (se envían a almacenes y trazabilidad)

### 🚛 Gestión de Vehículos y Transportistas
- **Flota Vehicular**: Control de vehículos con tipos, tamaños, estados y transportistas asignados
- **Transportistas**: Gestión de conductores con asignación de vehículos
- **Rutas**: Planificación y seguimiento de rutas de entrega
- **Checklists**: Formularios de verificación pre-entrega

### 📊 Dashboard y Reportes
- **Dashboard Interactivo**: Estadísticas en tiempo real con gráficos
- **DataTables Avanzadas**: Búsqueda, filtrado, ordenamiento y exportación (Excel, PDF, CSV)
- **Monitoreo de Almacenes**: Vista en tiempo real de envíos por almacén con mapa

### 🔗 Integraciones
- **Sistema de Almacenes (sistema-almacen-PSIII)**: 
  - Sincronización de pedidos y documentos
  - Envío automático de propuesta de vehículos al asignar envío
  - Envío automático de documentos al marcar como entregado
- **Sistema de Trazabilidad**: 
  - Envío automático de documentos de entrega
- **APIs REST**: Endpoints para comunicación con sistemas externos y app móvil

---

## 📦 Requisitos del Sistema

### Para Instalación sin Docker
- **PHP**: >= 8.1 (recomendado 8.4)
- **Composer**: >= 2.0
- **PostgreSQL**: >= 12.0
- **Extensiones PHP**: `pdo_pgsql`, `zip`, `bcmath`, `gd`, `mbstring`, `xml`, `curl`

### Para Instalación con Docker
- **Docker**: >= 20.10
- **Docker Compose**: >= 2.0

---

## 🚀 Instalación sin Docker

### Paso 1: Clonar o Descomprimir el Proyecto

```bash
cd /ruta/del/proyecto
```

### Paso 2: Instalar Dependencias de PHP

```bash
composer install
```

### Paso 3: Configurar Variables de Entorno

```bash
cp .env.example .env
```

Edita el archivo `.env` con tus configuraciones (ver sección [Configuración de Variables de Entorno](#-configuración-de-variables-de-entorno)).

### Paso 4: Generar Clave de Aplicación

```bash
php artisan key:generate
```

### Paso 5: Configurar Base de Datos

Asegúrate de que tu base de datos PostgreSQL esté creada y configurada en el `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=planta_cruds
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### Paso 6: Ejecutar Migraciones

```bash
php artisan migrate
```

### Paso 7: (Opcional) Ejecutar Seeders

Para cargar datos de ejemplo (roles, permisos, usuarios, etc.):

```bash
php artisan db:seed
```

**Seeders disponibles:**
- `RolesAndPermissionsSeeder`: Crea roles y permisos del sistema
- `InitialSeeder`: Crea datos básicos (categorías, tipos de empaque, etc.)
- `CrearUsuariosPorRolSeeder`: Crea usuarios de ejemplo por rol
- `TamanoVehiculoSeeder`: Crea tamaños de vehículos
- `TiposEmpaqueSeeder`: Crea tipos de empaque

### Paso 8: Configurar Permisos de Storage

**Linux/Mac:**
```bash
chmod -R 775 storage bootstrap/cache
```

**Windows:** Asegúrate de que el usuario tenga permisos de escritura en las carpetas `storage` y `bootstrap/cache`.

### Paso 9: Iniciar el Servidor de Desarrollo

```bash
php artisan serve
```

El sistema estará disponible en: `http://localhost:8000`

### Paso 10: Acceder al Sistema

Abre tu navegador y navega a `http://localhost:8000`

**Credenciales por defecto** (si ejecutaste los seeders):
- **Email**: `admin@admin.com`
- **Password**: `password`

---

## 🐳 Instalación con Docker

### ⚡ Instalación Automática (Recomendada)

El sistema incluye un script `entrypoint.sh` que **automatiza completamente** la instalación. Solo necesitas ejecutar un comando:

### Paso 1: Construir y Levantar los Contenedores

```bash
docker compose up --build -d
```

**¿Qué hace este comando automáticamente?**

El script `entrypoint.sh` ejecuta en orden:

1. ✅ **Crea el archivo `.env`** si no existe (desde `.env.example`)
2. ✅ **Instala dependencias de Composer** (`composer install`)
3. ✅ **Genera la clave de aplicación** (`php artisan key:generate`)
4. ✅ **Configura permisos** en `storage` y `bootstrap/cache`
5. ✅ **Ejecuta migraciones** (`php artisan migrate`)
6. ✅ **Ejecuta seeders** (`php artisan db:seed`)
7. ✅ **Inicia PHP-FPM** para servir la aplicación

**No necesitas ejecutar comandos manuales con `docker exec`** - todo se hace automáticamente.

### Paso 2: Verificar que los Contenedores Estén Corriendo

```bash
docker ps
```

Deberías ver tres contenedores:
- `org2-laravel` (aplicación Laravel con PHP-FPM)
- `orgtrack2` (servidor Nginx)
- `org2-db` (base de datos PostgreSQL)

### Paso 3: Acceder al Sistema

**Nota importante**: El `docker-compose.yml` actual está configurado para producción y no expone puertos localmente. 

**Para desarrollo local**, descomenta la línea de puertos en `docker-compose.yml`:

```yaml
nginx:
  ports:
    - "8080:80"  # Descomenta esta línea para acceso local
```

Luego reinicia los contenedores:

```bash
docker compose down
docker compose up -d
```

Accede al sistema en: `http://localhost:8080`

### Estructura de Contenedores Docker

```
┌─────────────────────────────────────────┐
│         Docker Compose Network          │
│                                         │
│  ┌──────────────┐    ┌──────────────┐  │
│  │   Nginx      │───▶│   Laravel    │  │
│  │  (Puerto 80) │    │  (PHP-FPM)   │  │
│  │              │    │              │  │
│  │  orgtrack2   │    │ org2-laravel │  │
│  └──────────────┘    └──────┬───────┘  │
│                             │          │
│                      ┌──────▼───────┐  │
│                      │  PostgreSQL  │  │
│                      │  (Puerto 5432)│  │
│                      │   org2-db    │  │
│                      └──────────────┘  │
└─────────────────────────────────────────┘
```

**Configuración de Redes:**
- `org2-net`: Red interna para comunicación entre contenedores
- `internal-network`: Red externa (debe existir)
- `proxy-network`: Red externa para proxy reverso (debe existir)

**Volúmenes:**
- `db-data`: Volumen persistente para la base de datos PostgreSQL
- `.` (directorio actual): Montado en `/var/www` para desarrollo

---

## ⚙️ Configuración de Variables de Entorno

### Variables Principales del Sistema

Edita el archivo `.env` con tus configuraciones:

```env
# Aplicación
APP_NAME="PlantaCRUDS"
APP_ENV=local
APP_KEY=base64:...  # Generado automáticamente
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de Datos
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1          # En Docker usar: db
DB_PORT=5432
DB_DATABASE=planta_cruds   # En Docker usar: org2_db
DB_USERNAME=tu_usuario     # En Docker usar: admin
DB_PASSWORD=tu_contraseña  # En Docker usar: admin123

# Integraciones con Otros Sistemas
ALMACEN_API_URL=http://localhost:8002/api
TRAZABILIDAD_API_URL=http://localhost:8000/api
PLANTA_CRUDS_API_URL=http://localhost:8001

# Cache y Sesiones
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail (Opcional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Variables de Integración

- **ALMACEN_API_URL**: URL base del sistema de almacenes (sistema-almacen-PSIII)
- **TRAZABILIDAD_API_URL**: URL base del sistema de trazabilidad
- **PLANTA_CRUDS_API_URL**: URL base de este sistema (usado por otros sistemas y app móvil)

**Importante para App Móvil**: Si la app móvil se conecta desde otro dispositivo, usa la IP de tu red local en lugar de `localhost`:
```env
PLANTA_CRUDS_API_URL=http://192.168.1.100:8001
```

---

## 🔗 Integraciones con Otros Sistemas

### Integración con Sistema de Almacenes (sistema-almacen-PSIII)

El sistema se comunica automáticamente con el sistema de almacenes para:

1. **Al Asignar un Envío**:
   - Genera automáticamente la **Propuesta de Vehículos** (PDF)
   - Envía la información de asignación y el documento al sistema de almacenes
   - Endpoint: `POST /api/pedidos/{pedido}/asignacion-envio`

2. **Al Marcar un Envío como Entregado**:
   - Genera automáticamente tres documentos PDF:
     - Propuesta de Vehículos
     - Nota de Entrega
     - Trazabilidad Completa
   - Envía todos los documentos al sistema de almacenes
   - Endpoint: `POST /api/pedidos/{pedido}/documentos-entrega`

**Flujo Automático:**
```
Envío Asignado → Genera Propuesta PDF → Envía a Almacenes
Envío Entregado → Genera 3 PDFs → Envía a Almacenes y Trazabilidad
```

### Integración con Sistema de Trazabilidad

Al marcar un envío como entregado, también se envían los documentos al sistema de trazabilidad:
- Endpoint: `POST /api/pedidos/{pedido}/documentos-entrega`

### Búsqueda de Pedidos

El sistema puede buscar pedidos en el sistema de almacenes mediante:
- `GET /api/pedidos/buscar-por-envio` - Buscar por código de envío o envio_id
- `GET /api/pedidos/buscar-por-envio-id` - Buscar directamente en pedido_entregas

---

## 🛠️ Comandos Útiles

### Comandos de Laravel (Sin Docker)

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar aplicación
php artisan optimize
php artisan config:cache
php artisan route:cache

# Base de datos
php artisan migrate                    # Ejecutar migraciones
php artisan migrate:fresh              # Refrescar BD (¡BORRA DATOS!)
php artisan db:seed                    # Ejecutar seeders
php artisan migrate:fresh --seed       # Refrescar y sembrar

# Ver rutas
php artisan route:list
```

### Comandos de Docker

```bash
# Construir y levantar contenedores (hace todo automáticamente)
docker compose up --build -d

# Detener contenedores
docker compose down

# Ver logs del contenedor Laravel
docker logs org2-laravel -f

# Ver logs de Nginx
docker logs orgtrack2 -f

# Ver logs de PostgreSQL
docker logs org2-db -f

# Reiniciar contenedores
docker compose restart

# Reconstruir desde cero (elimina volúmenes)
docker compose down -v
docker compose up --build -d
```

### Comandos Adicionales (Solo si necesitas ejecutar algo manualmente)

**Nota**: Normalmente NO necesitas estos comandos porque el `entrypoint.sh` ya hace todo. Solo úsalos si necesitas ejecutar algo específico después de que el contenedor esté corriendo:

```bash
# Ejecutar migraciones manualmente (si es necesario)
docker exec -it org2-laravel php artisan migrate

# Ejecutar seeders manualmente (si es necesario)
docker exec -it org2-laravel php artisan db:seed

# Acceder al shell del contenedor Laravel
docker exec -it org2-laravel bash

# Ver logs en tiempo real
docker logs org2-laravel -f
```

---

## 🔧 Solución de Problemas

### Error: "Class not found"

```bash
# Sin Docker
composer dump-autoload
php artisan optimize:clear

# Con Docker
docker exec -it org2-laravel composer dump-autoload
docker exec -it org2-laravel php artisan optimize:clear
```

### Error de Permisos en Storage

**Linux/Mac:**
```bash
chmod -R 775 storage bootstrap/cache
```

**Windows:** Verifica permisos de escritura en las carpetas.

**Docker:** El `entrypoint.sh` ya configura los permisos automáticamente.

### Error de Conexión a Base de Datos

1. **Sin Docker**: Verifica que PostgreSQL esté corriendo y las credenciales en `.env`
2. **Con Docker**: Verifica que el contenedor `org2-db` esté corriendo:
   ```bash
   docker ps | grep org2-db
   ```

### Error: "No application encryption key has been specified"

**Sin Docker:**
```bash
php artisan key:generate
```

**Con Docker:** El `entrypoint.sh` ya genera la clave automáticamente. Si persiste:
```bash
docker exec -it org2-laravel php artisan key:generate
```

### Error en Docker: Contenedor no inicia

1. **Verifica los logs:**
   ```bash
   docker logs org2-laravel -f
   ```

2. **Reconstruye los contenedores:**
   ```bash
   docker compose down
   docker compose up --build -d
   ```

3. **Si el problema persiste, elimina los volúmenes:**
   ```bash
   docker compose down -v
   docker compose up --build -d
   ```

### Error: "Vendor folder affecting container"

Si el contenedor se queda en "Instalando dependencias", elimina la carpeta `vendor` local:

```bash
rm -rf vendor
docker compose up --build -d
```

### Error: "Port already in use"

**Sin Docker:** Cambia el puerto:
```bash
php artisan serve --port=8001
```

**Con Docker:** Cambia el puerto en `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "8081:80"  # Cambia 8080 por otro puerto
```

### El contenedor se reinicia constantemente

Verifica los logs para ver el error:
```bash
docker logs org2-laravel -f
```

Comúnmente es por:
- Error en la conexión a la base de datos
- Error en las migraciones
- Permisos incorrectos

---

## 📝 Notas Importantes

### Generación Automática de Documentos

El sistema genera automáticamente documentos PDF cuando:

1. **Al Asignar un Envío**: 
   - Genera la **Propuesta de Vehículos** (PDF)
   - La envía automáticamente al sistema de almacenes
   - Se guarda en `storage/app/pedidos/{pedido_id}/documentos-entrega/`

2. **Al Marcar como Entregado**:
   - Genera **Propuesta de Vehículos**, **Nota de Entrega** y **Trazabilidad Completa** (PDFs)
   - Los envía automáticamente a:
     - Sistema de Almacenes (sistema-almacen-PSIII)
     - Sistema de Trazabilidad

### Scripts de Utilidad

El proyecto incluye scripts útiles:

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
**Última actualización**: Diciembre 2025  
**Framework**: Laravel 11  
**PHP**: 8.4  
**Base de Datos**: PostgreSQL  

---

**Desarrollado con ❤️ para la gestión eficiente de operaciones logísticas**
