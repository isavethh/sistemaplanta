<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==========================================
        // CREAR PERMISOS POR MÓDULO
        // ==========================================

        // Módulo: Dashboard
        Permission::create(['name' => 'dashboard.ver']);

        // Módulo: Envíos
        Permission::create(['name' => 'envios.ver']);
        Permission::create(['name' => 'envios.crear']);
        Permission::create(['name' => 'envios.editar']);
        Permission::create(['name' => 'envios.eliminar']);
        Permission::create(['name' => 'envios.asignar']);
        Permission::create(['name' => 'envios.aprobar']);
        Permission::create(['name' => 'envios.tracking']);
        Permission::create(['name' => 'envios.actualizar-estado']);
        Permission::create(['name' => 'envios.aceptar']);
        Permission::create(['name' => 'envios.rechazar']);
        Permission::create(['name' => 'envios.iniciar']);
        Permission::create(['name' => 'envios.entregar']);

        // Módulo: Asignaciones
        Permission::create(['name' => 'asignaciones.ver']);
        Permission::create(['name' => 'asignaciones.asignar']);
        Permission::create(['name' => 'asignaciones.remover']);
        Permission::create(['name' => 'asignaciones.multiple']);

        // Módulo: Rutas Multi-Entrega
        Permission::create(['name' => 'rutas-multi.ver']);
        Permission::create(['name' => 'rutas-multi.crear']);
        Permission::create(['name' => 'rutas-multi.editar']);
        Permission::create(['name' => 'rutas-multi.eliminar']);
        Permission::create(['name' => 'rutas-multi.monitorear']);
        Permission::create(['name' => 'rutas-multi.reordenar']);
        Permission::create(['name' => 'rutas-multi.documentos']);

        // Módulo: Usuarios
        Permission::create(['name' => 'usuarios.ver']);
        Permission::create(['name' => 'usuarios.crear']);
        Permission::create(['name' => 'usuarios.editar']);
        Permission::create(['name' => 'usuarios.eliminar']);
        Permission::create(['name' => 'usuarios.asignar-roles']);

        // Módulo: Transportistas
        Permission::create(['name' => 'transportistas.ver']);
        Permission::create(['name' => 'transportistas.crear']);
        Permission::create(['name' => 'transportistas.editar']);
        Permission::create(['name' => 'transportistas.eliminar']);
        Permission::create(['name' => 'transportistas.asignar-vehiculo']);

        // Módulo: Clientes
        Permission::create(['name' => 'clientes.ver']);
        Permission::create(['name' => 'clientes.crear']);
        Permission::create(['name' => 'clientes.editar']);
        Permission::create(['name' => 'clientes.eliminar']);

        // Módulo: Vehículos
        Permission::create(['name' => 'vehiculos.ver']);
        Permission::create(['name' => 'vehiculos.crear']);
        Permission::create(['name' => 'vehiculos.editar']);
        Permission::create(['name' => 'vehiculos.eliminar']);

        // Módulo: Almacenes
        Permission::create(['name' => 'almacenes.ver']);
        Permission::create(['name' => 'almacenes.crear']);
        Permission::create(['name' => 'almacenes.editar']);
        Permission::create(['name' => 'almacenes.eliminar']);
        Permission::create(['name' => 'almacenes.inventario']);

        // Módulo: Productos
        Permission::create(['name' => 'productos.ver']);
        Permission::create(['name' => 'productos.crear']);
        Permission::create(['name' => 'productos.editar']);
        Permission::create(['name' => 'productos.eliminar']);

        // Módulo: Categorías
        Permission::create(['name' => 'categorias.ver']);
        Permission::create(['name' => 'categorias.crear']);
        Permission::create(['name' => 'categorias.editar']);
        Permission::create(['name' => 'categorias.eliminar']);

        // Módulo: Inventario
        Permission::create(['name' => 'inventario.ver']);
        Permission::create(['name' => 'inventario.crear']);
        Permission::create(['name' => 'inventario.editar']);
        Permission::create(['name' => 'inventario.eliminar']);

        // Módulo: Incidentes
        Permission::create(['name' => 'incidentes.ver']);
        Permission::create(['name' => 'incidentes.crear']);
        Permission::create(['name' => 'incidentes.actualizar']);
        Permission::create(['name' => 'incidentes.resolver']);

        // Módulo: Reportes
        Permission::create(['name' => 'reportes.ver']);
        Permission::create(['name' => 'reportes.exportar']);

        // Módulo: Configuración (Catálogos)
        Permission::create(['name' => 'configuracion.ver']);
        Permission::create(['name' => 'configuracion.editar']);

        // ==========================================
        // CREAR ROLES Y ASIGNAR PERMISOS
        // Solo 3 roles: admin, almacen, transportista
        // ==========================================

        // 1. ADMIN - Control total del sistema
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // 2. ALMACEN - Gestión de inventario y recepción de envíos
        $almacen = Role::create(['name' => 'almacen']);
        $almacen->givePermissionTo([
            'dashboard.ver',
            // Envíos (ver y firmar)
            'envios.ver', 'envios.tracking', 'envios.firmar',
            // Almacenes
            'almacenes.ver', 'almacenes.inventario',
            // Productos
            'productos.ver',
            // Categorías
            'categorias.ver',
            // Inventario (completo)
            'inventario.ver', 'inventario.crear', 'inventario.editar',
            // Documentos
            'documentos.ver', 'documentos.nota-entrega',
            // Incidentes (reportar)
            'incidentes.ver', 'incidentes.crear', 'incidentes.reportar',
            // Reportes (solo de su almacén)
            'reportes.ver',
        ]);

        // 3. TRANSPORTISTA - Ver y actualizar sus envíos asignados
        $transportista = Role::create(['name' => 'transportista']);
        $transportista->givePermissionTo([
            'dashboard.ver',
            // Envíos (solo asignados)
            'envios.ver', 'envios.tracking', 'envios.actualizar-estado',
            'envios.aceptar', 'envios.rechazar', 'envios.iniciar', 'envios.entregar',
            // Rutas (solo asignadas)
            'rutas-multi.ver', 'rutas-multi.documentos',
            // Documentos
            'documentos.ver', 'documentos.nota-entrega',
            // Incidentes (crear y ver)
            'incidentes.ver', 'incidentes.crear',
            // Monitoreo
            'monitoreo.ver-propio', 'monitoreo.simular',
        ]);

        $this->command->info('✅ Roles y permisos creados exitosamente!');
        $this->command->info('');
        $this->command->info('📋 Roles creados (3 roles):');
        $this->command->info('  1. Admin (control total)');
        $this->command->info('  2. Almacen (inventario y recepción)');
        $this->command->info('  3. Transportista (envíos asignados)');
        $this->command->info('');
        $this->command->info('📝 Total de permisos: ' . Permission::count());
    }
}
