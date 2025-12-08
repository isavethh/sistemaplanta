<?php

use Spatie\Permission\Models\Role;
use App\Models\User;

echo "\n=== ROLES CREADOS EN EL SISTEMA ===\n\n";

$roles = Role::with('permissions', 'users')->get();

foreach ($roles as $role) {
    echo "📌 ROL: " . strtoupper($role->name) . "\n";
    echo "   Total de permisos: " . $role->permissions->count() . "\n";
    echo "   Usuarios con este rol: " . $role->users->count() . "\n";
    
    if ($role->users->count() > 0) {
        echo "   Usuarios:\n";
        foreach ($role->users as $user) {
            echo "      - " . $user->name . " (" . $user->email . ")\n";
        }
    }
    
    echo "\n";
}

echo "\n=== RESUMEN DE ROLES ===\n\n";
echo "ANTES NO TENÍAS SISTEMA DE ROLES FORMAL.\n";
echo "Solo tenías un campo 'role' en la tabla users con valores como: admin, transportista, almacen\n\n";
echo "AHORA TIENES 6 ROLES CREADOS CON SPATIE:\n\n";

echo "1. SUPER-ADMIN\n";
echo "   - Control total del sistema\n";
echo "   - Acceso a TODO (67 permisos)\n";
echo "   - Puede gestionar usuarios y roles\n\n";

echo "2. ADMIN\n";
echo "   - Gestión completa del sistema\n";
echo "   - No puede gestionar usuarios/roles\n";
echo "   - Puede: envíos, asignaciones, rutas, vehículos, almacenes, productos\n\n";

echo "3. GESTOR-ALMACEN\n";
echo "   - Gestión de inventario y envíos\n";
echo "   - Puede crear envíos, ver inventario\n";
echo "   - Acceso limitado a su almacén\n\n";

echo "4. TRANSPORTISTA\n";
echo "   - Ver y actualizar envíos asignados\n";
echo "   - Puede aceptar/rechazar/entregar envíos\n";
echo "   - Ver rutas asignadas\n\n";

echo "5. CLIENTE\n";
echo "   - Ver sus propios envíos\n";
echo "   - Tracking de envíos\n";
echo "   - Acceso limitado\n\n";

echo "6. DESPACHADOR\n";
echo "   - Asignación de transportistas\n";
echo "   - Crear rutas multi-entrega\n";
echo "   - Monitoreo en tiempo real\n\n";

echo "Total de permisos en el sistema: " . \Spatie\Permission\Models\Permission::count() . "\n";
echo "Total de usuarios con roles asignados: " . User::role(['super-admin', 'admin', 'gestor-almacen', 'transportista', 'cliente', 'despachador'])->count() . "\n";
