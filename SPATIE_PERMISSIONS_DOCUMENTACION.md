# Spatie Laravel-Permission - Sistema Implementado

## ✅ Implementación Completada

Se ha integrado exitosamente **Spatie Laravel-Permission** al sistema sin interrumpir las funcionalidades existentes.

---

## 📋 Roles Creados

### 1. **Super Admin** (`super-admin`)
- **Descripción**: Control total del sistema
- **Acceso**: Todas las funcionalidades
- **Usuarios**: admin@orgtrack.com

### 2. **Admin** (`admin`)
- **Descripción**: Gestión completa excepto usuarios/roles
- **Acceso**:
  - Dashboard
  - Envíos (CRUD completo, asignación, aprobación, tracking)
  - Asignaciones múltiples
  - Rutas multi-entrega (crear, editar, monitorear)
  - Vehículos, almacenes, productos
  - Reportes y estadísticas
  - Incidentes (gestión completa)

### 3. **Gestor de Almacén** (`gestor-almacen`)
- **Descripción**: Gestión de inventario y envíos
- **Acceso**:
  - Dashboard
  - Crear y ver envíos
  - Inventario (CRUD completo)
  - Ver almacenes
  - Ver productos y categorías
  - Reportes de su almacén

### 4. **Transportista** (`transportista`)
- **Descripción**: Ver y actualizar envíos asignados
- **Acceso**:
  - Dashboard
  - Ver envíos asignados
  - Tracking de envíos
  - Aceptar/rechazar envíos
  - Actualizar estado de envíos (en tránsito, entregado)
  - Ver rutas asignadas
  - Crear y ver incidentes

### 5. **Cliente** (`cliente`)
- **Descripción**: Ver sus propios envíos
- **Acceso**:
  - Dashboard
  - Ver sus envíos
  - Tracking de sus envíos

### 6. **Despachador** (`despachador`)
- **Descripción**: Asignación de transportistas y monitoreo
- **Acceso**:
  - Dashboard
  - Crear envíos
  - Asignaciones (completas)
  - Rutas multi-entrega (CRUD completo)
  - Monitoreo en tiempo real
  - Ver transportistas y vehículos
  - Incidentes (ver y actualizar)
  - Reportes

---

## 🔑 Permisos Implementados (67 total)

### Módulo: Dashboard
- `dashboard.ver`

### Módulo: Envíos (13 permisos)
- `envios.ver`
- `envios.crear`
- `envios.editar`
- `envios.eliminar`
- `envios.asignar`
- `envios.aprobar`
- `envios.tracking`
- `envios.actualizar-estado`
- `envios.aceptar`
- `envios.rechazar`
- `envios.iniciar`
- `envios.entregar`

### Módulo: Asignaciones (4 permisos)
- `asignaciones.ver`
- `asignaciones.asignar`
- `asignaciones.remover`
- `asignaciones.multiple`

### Módulo: Rutas Multi-Entrega (7 permisos)
- `rutas-multi.ver`
- `rutas-multi.crear`
- `rutas-multi.editar`
- `rutas-multi.eliminar`
- `rutas-multi.monitorear`
- `rutas-multi.reordenar`
- `rutas-multi.documentos`

### Módulo: Usuarios (5 permisos)
- `usuarios.ver`
- `usuarios.crear`
- `usuarios.editar`
- `usuarios.eliminar`
- `usuarios.asignar-roles`

### Módulo: Transportistas, Clientes, Vehículos, etc.
- Ver documentación completa en `RolesAndPermissionsSeeder.php`

---

## 🔄 Migración de Usuarios Existentes

Todos los usuarios existentes fueron migrados automáticamente:

- **admin@orgtrack.com** → Super Admin
- **trans@orgtrack.com** → Transportista
- Usuarios con `role='admin'` → Admin
- Usuarios con `role='transportista'` → Transportista
- Usuarios con `role='almacen'` → Gestor de Almacén
- Usuarios con `role='cliente'` → Cliente

---

## 🛡️ Rutas Protegidas

### Rutas Web (routes/web.php)
Todas las rutas están protegidas con middleware `auth` y permisos específicos:

```php
// Ejemplo: Solo usuarios con permiso pueden ver envíos
Route::middleware(['auth', 'permission:envios.ver'])->group(function () {
    Route::resource('envios', EnvioController::class);
});

// Ejemplo: Solo admin y super-admin pueden gestionar usuarios
Route::middleware(['auth', 'role:super-admin|admin'])->group(function () {
    Route::resource('users', UserController::class);
});
```

### Rutas API (routes/api.php)
Las rutas API mantienen acceso público para la app móvil (sin cambios).

---

## 🔧 Uso en Controladores

### Verificar permisos en controladores:

```php
public function index()
{
    // Opción 1: Usando authorize
    $this->authorize('envios.ver');
    
    // Opción 2: Verificar manualmente
    if (!auth()->user()->can('envios.ver')) {
        abort(403, 'No tienes permiso para ver envíos');
    }
    
    // Tu código...
}
```

### Verificar roles en controladores:

```php
public function store(Request $request)
{
    // Opción 1: Verificar rol
    if (!auth()->user()->hasRole('admin')) {
        abort(403);
    }
    
    // Opción 2: Verificar múltiples roles
    if (!auth()->user()->hasAnyRole(['admin', 'super-admin'])) {
        abort(403);
    }
    
    // Tu código...
}
```

---

## 🎨 Uso en Vistas Blade

### Mostrar contenido según permisos:

```blade
@can('envios.crear')
    <a href="{{ route('envios.create') }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Nuevo Envío
    </a>
@endcan

@cannot('envios.eliminar')
    <p class="text-muted">No tienes permiso para eliminar envíos</p>
@endcannot
```

### Mostrar contenido según roles:

```blade
@role('super-admin')
    <div class="alert alert-info">
        Eres Super Administrador
    </div>
@endrole

@hasrole('admin|super-admin')
    <a href="{{ route('users.index') }}">Gestionar Usuarios</a>
@endhasrole
```

---

## 👤 Métodos Helper en User Model

### Métodos actualizados (mantienen compatibilidad):

```php
// Verificar si es cliente (usa Spatie + fallback a campos legacy)
$user->esCliente(); // true/false

// Verificar si es transportista
$user->esTransportista(); // true/false

// Verificar si es admin
$user->esAdmin(); // true/false (NUEVO)

// Verificar si es gestor de almacén
$user->esGestorAlmacen(); // true/false (NUEVO)

// Verificar si es despachador
$user->esDespachador(); // true/false (NUEVO)
```

### Métodos de Spatie disponibles:

```php
// Asignar rol
$user->assignRole('admin');

// Verificar rol
$user->hasRole('admin');
$user->hasAnyRole(['admin', 'super-admin']);

// Asignar permiso
$user->givePermissionTo('envios.crear');

// Verificar permiso
$user->can('envios.crear');
$user->hasPermissionTo('envios.crear');

// Quitar rol
$user->removeRole('admin');

// Quitar permiso
$user->revokePermissionTo('envios.crear');
```

---

## 📝 Comandos Útiles

### Limpiar caché de permisos:
```bash
php artisan permission:cache-reset
```

### Ver todos los permisos:
```bash
php artisan tinker
>>> \Spatie\Permission\Models\Permission::all()->pluck('name');
```

### Ver todos los roles:
```bash
php artisan tinker
>>> \Spatie\Permission\Models\Role::with('permissions')->get();
```

### Asignar rol a usuario manualmente:
```bash
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> $user->assignRole('super-admin');
```

---

## ⚠️ Notas Importantes

### 1. Compatibilidad con código existente
- Los campos `role` y `tipo` en la tabla `users` **NO fueron eliminados**
- Los métodos `esCliente()` y `esTransportista()` **siguen funcionando**
- El sistema primero verifica roles de Spatie, luego hace fallback a campos legacy

### 2. App Móvil
- Las rutas API mantienen acceso público (sin cambios)
- La app móvil seguirá funcionando sin modificaciones

### 3. Usuarios sin rol
- Los roles se asignan únicamente a través de los seeders principales:
  - `RolesAndPermissionsSeeder.php` - Crea roles y permisos
  - `ResetRolesAndPermissionsSeeder.php` - Reinicia roles y permisos
- Los usuarios se crean desde el dashboard del admin con roles asignados directamente

### 4. Performance
- Spatie cachea roles y permisos automáticamente
- Si haces cambios manuales en roles/permisos, ejecuta: `php artisan permission:cache-reset`

---

## 🚀 Próximos Pasos Recomendados

### 1. Agregar validación de permisos en controladores
Actualmente las rutas están protegidas, pero es recomendable agregar validación en los métodos de los controladores:

```php
public function destroy($id)
{
    $this->authorize('envios.eliminar');
    // ... código de eliminación
}
```

### 2. Actualizar vistas para ocultar botones
Agregar directivas `@can` en las vistas para ocultar botones de acciones no permitidas:

```blade
@can('envios.eliminar')
    <form action="{{ route('envios.destroy', $envio) }}" method="POST">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">Eliminar</button>
    </form>
@endcan
```

### 3. Crear panel de gestión de roles y permisos
Agregar una interfaz en el panel admin para:
- Crear/editar roles
- Asignar permisos a roles
- Asignar roles a usuarios

---

## 📚 Documentación Oficial

- **Spatie Laravel-Permission**: https://spatie.be/docs/laravel-permission/v6
- **GitHub**: https://github.com/spatie/laravel-permission

---

## ✅ Verificación del Sistema

Para verificar que todo funciona correctamente:

1. **Login como admin@orgtrack.com**
   - Debería tener acceso a todas las funcionalidades

2. **Login como transportista (trans@orgtrack.com)**
   - Debería ver solo envíos asignados
   - Puede actualizar estados de envíos

3. **Probar creación de envíos**
   - Admin y Gestor Almacén pueden crear
   - Transportista y Cliente NO pueden crear

4. **Verificar middleware en rutas**
   - Intentar acceder a `/users` sin ser admin debería redirigir

---

**Implementado por**: GitHub Copilot  
**Fecha**: Diciembre 7, 2025  
**Versión Spatie**: 6.23.0  
**Laravel**: 12.0
