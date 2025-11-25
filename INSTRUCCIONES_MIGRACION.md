# Instrucciones para Arreglar la Base de Datos

## ⚠️ IMPORTANTE: Errores Corregidos

Se han corregido los siguientes problemas en las migraciones y modelos:

1. ✅ Tabla `users`: Agregadas columnas `tipo`, `telefono`, `direccion`
2. ✅ Tabla `productos`: Creada correctamente con el orden adecuado
3. ✅ Tabla `subcategorias`: Corregida (estaba creando tabla productos por error)
4. ✅ Tabla `envios`: Agregada columna `subcategoria_id`
5. ✅ Tabla `envio_productos`: Agregada columna `producto_nombre`
6. ✅ Dashboard: Actualizado para manejar consultas con try-catch

## 🔧 Pasos para Ejecutar las Migraciones

### Opción 1: Base de Datos Nueva (RECOMENDADO)

Si puedes borrar todos los datos y empezar de nuevo:

```bash
# 1. Borrar todas las tablas y volver a crear
php artisan migrate:fresh

# 2. Si quieres datos de prueba (opcional)
php artisan db:seed
```

### Opción 2: Base de Datos Existente

Si necesitas mantener los datos existentes:

```bash
# 1. Primero hacer un backup de la base de datos
pg_dump nombre_base_datos > backup.sql

# 2. Ejecutar las migraciones pendientes
php artisan migrate

# Si hay errores, necesitarás agregar las columnas manualmente
```

### Opción 3: Agregar Columnas Manualmente (PostgreSQL)

Si prefieres agregar las columnas a las tablas existentes sin perder datos:

```sql
-- Conectarse a PostgreSQL
psql -U tu_usuario -d nombre_base_datos

-- Agregar columnas faltantes a users
ALTER TABLE users ADD COLUMN IF NOT EXISTS tipo VARCHAR(255) DEFAULT 'user';
ALTER TABLE users ADD COLUMN IF NOT EXISTS telefono VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS direccion TEXT;

-- Agregar comentarios
COMMENT ON COLUMN users.tipo IS 'tipos: admin, transportista, cliente, user';

-- Crear tabla productos si no existe
CREATE TABLE IF NOT EXISTS productos (
    id BIGSERIAL PRIMARY KEY,
    categoria_id BIGINT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio_base DECIMAL(10, 2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- Agregar subcategoria_id a envios si no existe
ALTER TABLE envios ADD COLUMN IF NOT EXISTS subcategoria_id BIGINT;
ALTER TABLE envios ADD CONSTRAINT fk_envios_subcategoria 
    FOREIGN KEY (subcategoria_id) REFERENCES subcategorias(id) ON DELETE SET NULL;

-- Agregar producto_nombre a envio_productos si no existe
ALTER TABLE envio_productos ADD COLUMN IF NOT EXISTS producto_nombre VARCHAR(255);

-- Salir
\q
```

## 🗂️ Orden Correcto de las Migraciones

Las migraciones se ejecutan en este orden:

1. `0001_01_01_000000_create_users_table.php` (actualizada ✅)
2. `0001_01_01_000001_create_cache_table.php`
3. `0001_01_01_000002_create_jobs_table.php`
4. `0001_01_01_000003_create_direcciones_table.php`
5. `0001_01_01_000004_create_tipos_empaque_table.php`
6. `0001_01_01_000005_create_unidades_medida_table.php`
7. `0001_01_01_000006_create_categorias_table.php`
8. `0001_01_01_000006_5_create_productos_table.php` (nueva ✅)
9. `0001_01_01_000007_create_subcategorias_table.php` (corregida ✅)
10. `0001_01_01_000008_create_almacenes_table.php`
11. `0001_01_01_000009_create_vehiculos_table.php`
12. `0001_01_01_000010_create_envios_table.php` (actualizada ✅)
13. `0001_01_01_000011_create_envio_productos_table.php` (actualizada ✅)
14. `0001_01_01_000012_create_inventario_almacen_table.php`
15. `2025_11_25_000001_create_tipos_vehiculo_table.php`
16. `2025_11_25_000002_create_estados_vehiculo_table.php`
17. `2025_11_25_000003_create_codigosqr_table.php`
18. `2025_11_25_000004_create_rutas_table.php`

## 🎯 Verificar que Todo Funciona

Después de ejecutar las migraciones:

```bash
# 1. Verificar conexión a la base de datos
php artisan migrate:status

# 2. Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Iniciar el servidor
php artisan serve
```

## 📊 Crear Usuario de Prueba

```bash
php artisan tinker
```

Luego ejecuta:

```php
\App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@admin.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'tipo' => 'admin'
]);

exit
```

## 🔍 Verificar Estructura de Tablas (PostgreSQL)

```sql
-- Ver estructura de la tabla users
\d users

-- Ver todas las tablas
\dt

-- Ver columnas de una tabla específica
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'users';
```

## ⚠️ Errores Comunes y Soluciones

### Error: "relation already exists"
```bash
# La tabla ya existe, usa migrate en lugar de migrate:fresh
php artisan migrate
```

### Error: "column already exists"
```bash
# La columna ya existe, omite ese ALTER TABLE o usa IF NOT EXISTS
```

### Error: "violates foreign key constraint"
```bash
# Hay datos que hacen referencia a registros inexistentes
# Necesitas limpiar los datos huérfanos primero
```

### Error: "permission denied"
```bash
# El usuario de PostgreSQL no tiene permisos
# Conéctate como superusuario y otorga permisos:
GRANT ALL PRIVILEGES ON DATABASE nombre_base_datos TO tu_usuario;
```

## 📝 Notas Adicionales

- **PostgreSQL vs MySQL**: Este proyecto usa PostgreSQL. Si usas MySQL, el símbolo `$1` será `?`
- **Backup**: Siempre haz backup antes de modificar la estructura de la base de datos
- **Testing**: Prueba primero en un ambiente de desarrollo
- **Datos**: Si tienes datos importantes, usa migraciones incrementales en lugar de `migrate:fresh`

## ✅ Checklist

- [ ] Backup de la base de datos realizado
- [ ] Migraciones ejecutadas correctamente
- [ ] Tabla `users` tiene columnas `tipo`, `telefono`, `direccion`
- [ ] Tabla `productos` existe y tiene datos
- [ ] Tabla `subcategorias` existe correctamente
- [ ] Dashboard carga sin errores
- [ ] Usuario de prueba creado
- [ ] Todos los módulos funcionan correctamente

---

**¡Listo!** Una vez completados estos pasos, el sistema debería funcionar correctamente sin errores de base de datos.

