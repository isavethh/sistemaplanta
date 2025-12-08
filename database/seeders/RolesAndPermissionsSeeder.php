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
        // ==========================================

        // 1. SUPER ADMIN - Control total
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. ADMIN - Gestión completa excepto usuarios/roles
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'dashboard.ver',
            // Envíos
            'envios.ver', 'envios.crear', 'envios.editar', 'envios.eliminar',
            'envios.asignar', 'envios.aprobar', 'envios.tracking', 'envios.actualizar-estado',
            // Asignaciones
            'asignaciones.ver', 'asignaciones.asignar', 'asignaciones.remover', 'asignaciones.multiple',
            // Rutas Multi-Entrega
            'rutas-multi.ver', 'rutas-multi.crear', 'rutas-multi.editar', 'rutas-multi.monitorear',
            'rutas-multi.reordenar', 'rutas-multi.documentos',
            // Usuarios (solo ver)
            'usuarios.ver', 'transportistas.ver', 'clientes.ver',
            // Vehículos
            'vehiculos.ver', 'vehiculos.crear', 'vehiculos.editar',
            // Almacenes
            'almacenes.ver', 'almacenes.crear', 'almacenes.editar', 'almacenes.inventario',
            // Productos
            'productos.ver', 'productos.crear', 'productos.editar',
            // Categorías
            'categorias.ver', 'categorias.crear', 'categorias.editar',
            // Inventario
            'inventario.ver', 'inventario.crear', 'inventario.editar',
            // Incidentes
            'incidentes.ver', 'incidentes.actualizar', 'incidentes.resolver',
            // Reportes
            'reportes.ver', 'reportes.exportar',
        ]);

        // 3. GESTOR DE ALMACÉN - Gestión de inventario y creación de envíos
        $gestorAlmacen = Role::create(['name' => 'gestor-almacen']);
        $gestorAlmacen->givePermissionTo([
            'dashboard.ver',
            // Envíos (crear y ver)
            'envios.ver', 'envios.crear', 'envios.tracking',
            // Almacenes
            'almacenes.ver', 'almacenes.inventario',
            // Productos
            'productos.ver',
            // Categorías
            'categorias.ver',
            // Inventario (completo)
            'inventario.ver', 'inventario.crear', 'inventario.editar',
            // Reportes (solo de su almacén)
            'reportes.ver',
        ]);

        // 4. TRANSPORTISTA - Ver y actualizar sus envíos asignados
        $transportista = Role::create(['name' => 'transportista']);
        $transportista->givePermissionTo([
            'dashboard.ver',
            // Envíos (solo asignados)
            'envios.ver', 'envios.tracking', 'envios.actualizar-estado',
            'envios.aceptar', 'envios.rechazar', 'envios.iniciar', 'envios.entregar',
            // Rutas (solo asignadas)
            'rutas-multi.ver', 'rutas-multi.documentos',
            // Incidentes (crear y ver)
            'incidentes.ver', 'incidentes.crear',
        ]);

        // 5. CLIENTE - Ver sus propios envíos
        $cliente = Role::create(['name' => 'cliente']);
        $cliente->givePermissionTo([
            'dashboard.ver',
            // Envíos (solo propios)
            'envios.ver', 'envios.tracking',
        ]);

        // 6. DESPACHADOR - Asignación de transportistas y monitoreo
        $despachador = Role::create(['name' => 'despachador']);
        $despachador->givePermissionTo([
            'dashboard.ver',
            // Envíos
            'envios.ver', 'envios.crear', 'envios.asignar', 'envios.tracking',
            'envios.actualizar-estado',
            // Asignaciones (completo)
            'asignaciones.ver', 'asignaciones.asignar', 'asignaciones.remover', 'asignaciones.multiple',
            // Rutas Multi-Entrega (completo)
            'rutas-multi.ver', 'rutas-multi.crear', 'rutas-multi.editar',
            'rutas-multi.monitorear', 'rutas-multi.reordenar', 'rutas-multi.documentos',
            // Ver transportistas y vehículos
            'transportistas.ver', 'vehiculos.ver',
            // Incidentes
            'incidentes.ver', 'incidentes.actualizar',
            // Reportes
            'reportes.ver',
        ]);

        $this->command->info('✅ Roles y permisos creados exitosamente!');
        $this->command->info('');
        $this->command->info('📋 Roles creados:');
        $this->command->info('  1. Super Admin (acceso total)');
        $this->command->info('  2. Admin (gestión completa)');
        $this->command->info('  3. Gestor de Almacén (inventario y envíos)');
        $this->command->info('  4. Transportista (envíos asignados)');
        $this->command->info('  5. Cliente (ver propios envíos)');
        $this->command->info('  6. Despachador (asignaciones y rutas)');
        $this->command->info('');
        $this->command->info('📝 Total de permisos: ' . Permission::count());
    }
}
