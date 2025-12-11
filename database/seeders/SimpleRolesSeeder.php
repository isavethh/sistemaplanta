<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SimpleRolesSeeder extends Seeder
{
    /**
     * Crear solo 3 roles: admin, transportista, almacen
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🗑️  Eliminando roles antiguos...');
        
        // Eliminar TODOS los roles y permisos existentes
        \DB::table('model_has_roles')->delete();
        \DB::table('model_has_permissions')->delete();
        \DB::table('role_has_permissions')->delete();
        Role::query()->delete();
        Permission::query()->delete();

        $this->command->info('✅ Roles anteriores eliminados');
        $this->command->info('');
        $this->command->info('📋 Creando 3 roles simplificados...');
        $this->command->info('');

        // ==========================================
        // CREAR PERMISOS ESENCIALES
        // ==========================================

        // Dashboard
        Permission::create(['name' => 'dashboard.ver']);

        // Envíos
        Permission::create(['name' => 'envios.ver']);
        Permission::create(['name' => 'envios.crear']);
        Permission::create(['name' => 'envios.editar']);
        Permission::create(['name' => 'envios.eliminar']);
        Permission::create(['name' => 'envios.asignar']);
        Permission::create(['name' => 'envios.tracking']);
        Permission::create(['name' => 'envios.aceptar']);
        Permission::create(['name' => 'envios.rechazar']);
        Permission::create(['name' => 'envios.actualizar-estado']);
        Permission::create(['name' => 'envios.entregar']);
        Permission::create(['name' => 'envios.firmar']);

        // Asignaciones
        Permission::create(['name' => 'asignaciones.ver']);
        Permission::create(['name' => 'asignaciones.asignar']);
        Permission::create(['name' => 'asignaciones.multiple']);

        // Rutas Multi-Entrega
        Permission::create(['name' => 'rutas-multi.ver']);
        Permission::create(['name' => 'rutas-multi.crear']);
        Permission::create(['name' => 'rutas-multi.monitorear']);

        // Documentos
        Permission::create(['name' => 'documentos.ver']);
        Permission::create(['name' => 'documentos.nota-entrega']);

        // Incidentes
        Permission::create(['name' => 'incidentes.ver']);
        Permission::create(['name' => 'incidentes.crear']);
        Permission::create(['name' => 'incidentes.resolver']);

        // Monitoreo
        Permission::create(['name' => 'monitoreo.ver-todos']);
        Permission::create(['name' => 'monitoreo.ver-propio']);
        Permission::create(['name' => 'monitoreo.simular']);

        // Transportistas y Vehículos
        Permission::create(['name' => 'transportistas.ver']);
        Permission::create(['name' => 'transportistas.gestionar']);
        Permission::create(['name' => 'vehiculos.ver']);
        Permission::create(['name' => 'vehiculos.gestionar']);

        // Almacenes
        Permission::create(['name' => 'almacenes.ver']);
        Permission::create(['name' => 'almacenes.inventario']);

        // Reportes
        Permission::create(['name' => 'reportes.ver']);
        Permission::create(['name' => 'reportes.exportar']);

        // Productos y Categorías
        Permission::create(['name' => 'productos.ver']);
        Permission::create(['name' => 'productos.gestionar']);
        Permission::create(['name' => 'categorias.ver']);
        Permission::create(['name' => 'categorias.gestionar']);

        // ==========================================
        // CREAR SOLO 3 ROLES
        // ==========================================

        // 1. ADMIN - Control total del sistema
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'dashboard.ver',
            // Envíos (completo)
            'envios.ver', 'envios.crear', 'envios.editar', 'envios.eliminar',
            'envios.asignar', 'envios.tracking', 'envios.actualizar-estado',
            // Asignaciones (completo)
            'asignaciones.ver', 'asignaciones.asignar', 'asignaciones.multiple',
            // Rutas Multi-Entrega (completo)
            'rutas-multi.ver', 'rutas-multi.crear', 'rutas-multi.monitorear',
            // Documentos (completo)
            'documentos.ver', 'documentos.nota-entrega',
            // Monitoreo (ver todos)
            'monitoreo.ver-todos',
            // Transportistas y Vehículos (completo)
            'transportistas.ver', 'transportistas.gestionar',
            'vehiculos.ver', 'vehiculos.gestionar',
            // Almacenes
            'almacenes.ver',
            // Incidentes (completo)
            'incidentes.ver', 'incidentes.resolver',
            // Reportes (completo)
            'reportes.ver', 'reportes.exportar',
            // Productos y Categorías
            'productos.ver', 'productos.gestionar',
            'categorias.ver', 'categorias.gestionar',
        ]);

        // 2. TRANSPORTISTA - Gestiona sus envíos asignados
        $transportista = Role::create(['name' => 'transportista']);
        $transportista->givePermissionTo([
            'dashboard.ver',
            // Envíos (solo asignados)
            'envios.ver', 'envios.tracking',
            'envios.aceptar', 'envios.rechazar',
            'envios.actualizar-estado', 'envios.entregar',
            // Rutas (solo asignadas)
            'rutas-multi.ver',
            // Documentos (de sus envíos)
            'documentos.ver', 'documentos.nota-venta', 'documentos.nota-entrega',
            // Monitoreo (simular movimiento)
            'monitoreo.ver-propio', 'monitoreo.simular',
            // Incidentes (reportar)
            'incidentes.ver', 'incidentes.crear',
        ]);

        // 3. ALMACEN - Recibe envíos y gestiona inventario
        $almacen = Role::create(['name' => 'almacen']);
        $almacen->givePermissionTo([
            'dashboard.ver',
            // Envíos (solo los que recibe)
            'envios.ver', 'envios.tracking', 'envios.firmar',
            // Documentos (nota de entrega/venta)
            'documentos.ver', 'documentos.nota-venta', 'documentos.nota-entrega',
            // Monitoreo (ver envíos hacia su almacén)
            'monitoreo.ver-propio',
            // Almacenes (inventario)
            'almacenes.ver', 'almacenes.inventario',
            // Incidentes (reportar problemas)
            'incidentes.ver', 'incidentes.crear',
        ]);

        $this->command->info('✅ Roles creados exitosamente!');
        $this->command->info('');
        $this->command->info('📋 Roles creados (3):');
        $this->command->info('  1. admin (control total)');
        $this->command->info('  2. transportista (envíos asignados)');
        $this->command->info('  3. almacen (recibe envíos)');
        $this->command->info('');
        $this->command->info('📝 Total de permisos: ' . Permission::count());
    }
}

