# Sistema de Gestión de Planta - PlantaCRUDS

Sistema completo de gestión integral para control de inventarios, envíos, vehículos y logística.

## 🚀 Características

- ✅ **Gestión de Inventario**: Control completo de productos, categorías y almacenes
- ✅ **Gestión de Envíos**: Seguimiento de envíos con tracking en tiempo real
- ✅ **Gestión de Vehículos**: Control de flota vehicular y transportistas
- ✅ **Gestión de Usuarios**: Administración de usuarios, clientes y administradores
- ✅ **Dashboard Moderno**: Panel con estadísticas y accesos rápidos
- ✅ **DataTables**: Todas las tablas con búsqueda, ordenamiento y exportación
- ✅ **Diseño Responsivo**: Interfaz adaptable a cualquier dispositivo

## 📋 Requisitos

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Node.js y NPM (opcional, para compilar assets)

## 🔧 Instalación

1. **Clonar el repositorio o descomprimir el proyecto**

```bash
cd plantaCruds
```

2. **Instalar dependencias de PHP**

```bash
composer install
```

3. **Configurar el archivo de entorno**

```bash
cp .env.example .env
```

Edita el archivo `.env` y configura tu base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=planta_cruds
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

4. **Generar la clave de la aplicación**

```bash
php artisan key:generate
```

5. **Ejecutar las migraciones**

```bash
php artisan migrate
```

6. **Crear un usuario administrador (opcional)**

```bash
php artisan tinker
```

Luego ejecuta:

```php
\App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@admin.com',
    'password' => bcrypt('password'),
    'role' => 'admin'
]);
exit
```

7. **Iniciar el servidor de desarrollo**

```bash
php artisan serve
```

8. **Acceder al sistema**

Abre tu navegador en: `http://localhost:8000`

Login:
- Email: `admin@admin.com`
- Password: `password`

## 📁 Estructura del Proyecto

```
plantaCruds/
├── app/
│   ├── Http/Controllers/      # Controladores del sistema
│   └── Models/                 # Modelos Eloquent
├── config/
│   └── adminlte.php           # Configuración del AdminLTE
├── database/
│   └── migrations/            # Migraciones de base de datos
├── public/
│   ├── css/custom.css         # Estilos personalizados
│   └── js/custom.js           # Scripts personalizados
├── resources/
│   └── views/                 # Vistas Blade
│       ├── almacenes/
│       ├── categorias/
│       ├── clientes/
│       ├── envios/
│       ├── inventarios/
│       ├── productos/
│       ├── subcategorias/
│       ├── users/
│       ├── vehiculos/
│       └── dashboard.blade.php
└── routes/
    └── web.php                # Rutas del sistema
```

## 🎯 Módulos del Sistema

### Gestión de Inventario
- **Almacenes**: Administrar ubicaciones de almacenamiento
- **Productos**: Catálogo completo de productos
- **Categorías**: Organización de productos por categorías
- **Subcategorías**: Clasificación detallada
- **Inventario**: Control de stock por almacén

### Gestión de Envíos
- **Envíos**: Crear y gestionar envíos
- **Rutas**: Tracking en tiempo real
- **Códigos QR**: Generación de códigos para seguimiento
- **Direcciones**: Gestión de ubicaciones

### Gestión de Vehículos
- **Vehículos**: Control de flota
- **Tipos de Vehículo**: Clasificación de vehículos
- **Estados de Vehículo**: Control de disponibilidad

### Gestión de Personal
- **Usuarios**: Administración de usuarios del sistema
- **Clientes**: Base de datos de clientes
- **Transportistas**: Gestión de conductores
- **Administradores**: Control de accesos

## 🎨 Características de la Interfaz

- **Design System**: AdminLTE 3 con Bootstrap 4
- **DataTables**: 
  - Búsqueda y filtrado avanzado
  - Ordenamiento por columnas
  - Paginación
  - Exportación a Excel, PDF, CSV
  - Impresión de reportes
- **Dashboard Interactivo**: Estadísticas en tiempo real
- **Formularios con Validación**: Validación del lado del servidor y cliente
- **Alertas y Notificaciones**: Mensajes de éxito y error
- **Responsive Design**: Adaptable a móviles y tablets

## 📊 Funcionalidades Destacadas

### Dashboard
- Resumen de estadísticas principales
- Accesos rápidos a módulos
- Tarjetas informativas con contadores
- Navegación intuitiva

### Inventario
- Gestión completa de stock
- Control de entrada y salida de productos
- Valoración de inventario
- Reportes de inventario por almacén

### Envíos
- Creación de envíos con múltiples productos
- Asignación de transportistas
- Estados de envío (pendiente, en tránsito, entregado)
- Tracking en tiempo real

## 🛠️ Tecnologías Utilizadas

- **Backend**: Laravel 11
- **Frontend**: 
  - AdminLTE 3
  - Bootstrap 4
  - jQuery
  - DataTables
  - Font Awesome
- **Base de Datos**: MySQL/MariaDB

## 📝 Comandos Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Refrescar base de datos (¡CUIDADO! Borra todos los datos)
php artisan migrate:fresh

# Crear un nuevo controlador
php artisan make:controller NombreController --resource

# Crear un nuevo modelo
php artisan make:model NombreModelo -m
```

## 🔐 Seguridad

- Validación de datos en todos los formularios
- Protección CSRF en formularios
- Autenticación de usuarios
- Control de acceso (middleware)

## 📱 Responsive Design

El sistema está completamente optimizado para:
- 💻 Desktop (1920x1080 y superiores)
- 💻 Laptop (1366x768)
- 📱 Tablet (768x1024)
- 📱 Mobile (320x568 y superiores)

## 🆘 Solución de Problemas

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error de permisos en storage
```bash
chmod -R 775 storage bootstrap/cache
```

### Error de migraciones
```bash
php artisan migrate:fresh
```

## 📄 Licencia

Este proyecto es privado y de uso interno.

## 👨‍💻 Soporte

Para soporte técnico o reportar problemas, contactar al equipo de desarrollo.

---

**Versión**: 1.0.0  
**Última actualización**: Noviembre 2025  
**Sistema**: PlantaCRUDS - Sistema de Gestión Integral
