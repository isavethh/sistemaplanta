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
        Permission::firstOrCreate(['name' => 'dashboard.ver', 'guard_name' => 'web']);

        // Módulo: Envíos
        Permission::firstOrCreate(['name' => 'envios.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.eliminar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.asignar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.aprobar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.tracking', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.actualizar-estado', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.aceptar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.rechazar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.iniciar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'envios.entregar', 'guard_name' => 'web']);

        // Módulo: Asignaciones
        Permission::firstOrCreate(['name' => 'asignaciones.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'asignaciones.asignar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'asignaciones.remover', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'asignaciones.multiple', 'guard_name' => 'web']);

        // Módulo: Rutas Multi-Entrega
        Permission::firstOrCreate(['name' => 'rutas-multi.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'rutas-multi.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'rutas-multi.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'rutas-multi.eliminar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'rutas-multi.monitorear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'rutas-multi.reordenar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'rutas-multi.documentos', 'guard_name' => 'web']);

        // Módulo: Usuarios
        Permission::firstOrCreate(['name' => 'usuarios.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'usuarios.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'usuarios.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'usuarios.eliminar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'usuarios.asignar-roles', 'guard_name' => 'web']);

        // Módulo: Transportistas
        Permission::firstOrCreate(['name' => 'transportistas.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'transportistas.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'transportistas.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'transportistas.eliminar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'transportistas.asignar-vehiculo', 'guard_name' => 'web']);

        // Módulo: Clientes
        Permission::firstOrCreate(['name' => 'clientes.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'clientes.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'clientes.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'clientes.eliminar', 'guard_name' => 'web']);

        // Módulo: Vehículos
        Permission::firstOrCreate(['name' => 'vehiculos.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'vehiculos.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'vehiculos.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'vehiculos.eliminar', 'guard_name' => 'web']);

        // Módulo: Almacenes
        Permission::firstOrCreate(['name' => 'almacenes.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'almacenes.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'almacenes.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'almacenes.eliminar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'almacenes.inventario', 'guard_name' => 'web']);

        // Módulo: Productos
        Permission::firstOrCreate(['name' => 'productos.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'productos.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'productos.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'productos.eliminar', 'guard_name' => 'web']);

        // Módulo: Categorías
        Permission::firstOrCreate(['name' => 'categorias.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'categorias.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'categorias.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'categorias.eliminar', 'guard_name' => 'web']);

        // Módulo: Inventario
        Permission::firstOrCreate(['name' => 'inventario.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'inventario.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'inventario.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'inventario.eliminar', 'guard_name' => 'web']);

        // Módulo: Incidentes
        Permission::firstOrCreate(['name' => 'incidentes.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'incidentes.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'incidentes.actualizar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'incidentes.resolver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'incidentes.reportar', 'guard_name' => 'web']);
        
        // Módulo: Documentos
        Permission::firstOrCreate(['name' => 'documentos.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'documentos.nota-entrega', 'guard_name' => 'web']);
        
        // Módulo: Envíos (permisos adicionales)
        Permission::firstOrCreate(['name' => 'envios.firmar', 'guard_name' => 'web']);
        
        // Módulo: Monitoreo
        Permission::firstOrCreate(['name' => 'monitoreo.ver-propio', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'monitoreo.simular', 'guard_name' => 'web']);

        // Módulo: Reportes
        Permission::firstOrCreate(['name' => 'reportes.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'reportes.exportar', 'guard_name' => 'web']);

        // Módulo: Configuración (Catálogos)
        Permission::firstOrCreate(['name' => 'configuracion.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'configuracion.editar', 'guard_name' => 'web']);

        // Módulo: Pedidos Almacén (Propietario)
        Permission::firstOrCreate(['name' => 'pedidos-almacen.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'pedidos-almacen.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'pedidos-almacen.editar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'pedidos-almacen.eliminar', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'pedidos-almacen.seguimiento', 'guard_name' => 'web']);

        // Módulo: Trazabilidad (Operador)
        Permission::firstOrCreate(['name' => 'trazabilidad.pedidos-pendientes', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'trazabilidad.aceptar-pedido', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'trazabilidad.rechazar-pedido', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'trazabilidad.propuestas-envios', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'trazabilidad.aprobar-propuesta', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'trazabilidad.rechazar-propuesta', 'guard_name' => 'web']);

        // ==========================================
        // CREAR ROLES Y ASIGNAR PERMISOS
        // Roles: admin, almacen, transportista, propietario, operador
        // ==========================================

        // 1. ADMIN - Control total del sistema
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all());

        // 2. ALMACEN - Gestión de inventario y recepción de envíos
        $almacen = Role::firstOrCreate(['name' => 'almacen', 'guard_name' => 'web']);
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
        $transportista = Role::firstOrCreate(['name' => 'transportista', 'guard_name' => 'web']);
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

        // 4. PROPIETARIO - Gestión de almacenes y pedidos
        $propietario = Role::firstOrCreate(['name' => 'propietario', 'guard_name' => 'web']);
        $propietario->givePermissionTo([
            'dashboard.ver',
            // Almacenes (sus propios almacenes)
            'almacenes.ver', 'almacenes.crear', 'almacenes.editar',
            // Pedidos Almacén (completo)
            'pedidos-almacen.ver', 'pedidos-almacen.crear', 'pedidos-almacen.editar',
            'pedidos-almacen.eliminar', 'pedidos-almacen.seguimiento',
            // Envíos (ver seguimiento de sus pedidos)
            'envios.ver', 'envios.tracking',
            // Productos (ver para seleccionar)
            'productos.ver',
            // Incidentes (ver y reportar)
            'incidentes.ver', 'incidentes.crear', 'incidentes.reportar',
            // Documentos
            'documentos.ver',
        ]);

        // 5. OPERADOR - Gestión de trazabilidad y propuestas
        $operador = Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);
        $operador->givePermissionTo([
            'dashboard.ver',
            // Trazabilidad (completo)
            'trazabilidad.pedidos-pendientes', 'trazabilidad.aceptar-pedido',
            'trazabilidad.rechazar-pedido', 'trazabilidad.propuestas-envios',
            'trazabilidad.aprobar-propuesta', 'trazabilidad.rechazar-propuesta',
            // Envíos (ver y aprobar)
            'envios.ver', 'envios.tracking', 'envios.aprobar',
            // Productos (ver)
            'productos.ver',
            // Documentos
            'documentos.ver',
            // Reportes
            'reportes.ver',
        ]);

        $this->command->info('✅ Roles y permisos creados exitosamente!');
        $this->command->info('');
        $this->command->info('📋 Roles creados (5 roles):');
        $this->command->info('  1. Admin (control total)');
        $this->command->info('  2. Almacen (inventario y recepción)');
        $this->command->info('  3. Transportista (envíos asignados)');
        $this->command->info('  4. Propietario (almacenes y pedidos)');
        $this->command->info('  5. Operador (trazabilidad y propuestas)');
        $this->command->info('');
        $this->command->info('📝 Total de permisos: ' . Permission::count());
    }
}
