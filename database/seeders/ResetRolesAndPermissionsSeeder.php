<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ResetRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Eliminar roles anteriores y crear los correctos según el flujo real
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🗑️  Eliminando roles y permisos antiguos...');
        
        // Eliminar TODOS los roles
        \DB::table('model_has_roles')->delete();
        \DB::table('model_has_permissions')->delete();
        \DB::table('role_has_permissions')->delete();
        Role::query()->delete();
        Permission::query()->delete();

        $this->command->info('✅ Roles anteriores eliminados');
        $this->command->info('');
        $this->command->info('📋 Creando nuevos roles según el flujo real...');
        $this->command->info('');

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
        Permission::create(['name' => 'envios.tracking']);
        Permission::create(['name' => 'envios.aceptar']);
        Permission::create(['name' => 'envios.rechazar']);
        Permission::create(['name' => 'envios.actualizar-estado']);
        Permission::create(['name' => 'envios.entregar']);
        Permission::create(['name' => 'envios.firmar']);

        // Módulo: Asignaciones
        Permission::create(['name' => 'asignaciones.ver']);
        Permission::create(['name' => 'asignaciones.asignar']);
        Permission::create(['name' => 'asignaciones.remover']);
        Permission::create(['name' => 'asignaciones.multiple']);

        // Módulo: Rutas Multi-Entrega
        Permission::create(['name' => 'rutas-multi.ver']);
        Permission::create(['name' => 'rutas-multi.crear']);
        Permission::create(['name' => 'rutas-multi.editar']);
        Permission::create(['name' => 'rutas-multi.monitorear']);

        // Módulo: Documentos
        Permission::create(['name' => 'documentos.ver']);
        Permission::create(['name' => 'documentos.nota-entrega']);

        // Módulo: Incidentes
        Permission::create(['name' => 'incidentes.ver']);
        Permission::create(['name' => 'incidentes.crear']);
        Permission::create(['name' => 'incidentes.reportar']);

        // Módulo: Monitoreo
        Permission::create(['name' => 'monitoreo.ver-propio']);
        Permission::create(['name' => 'monitoreo.ver-todos']);
        Permission::create(['name' => 'monitoreo.simular']);

        // Módulo: Vehículos
        Permission::create(['name' => 'vehiculos.ver']);
        Permission::create(['name' => 'vehiculos.gestionar']);

        // Módulo: Transportistas
        Permission::create(['name' => 'transportistas.ver']);
        Permission::create(['name' => 'transportistas.gestionar']);

        // ==========================================
        // CREAR ROLES SEGÚN EL FLUJO REAL
        // ==========================================

        // 1. PLANTA (Cliente que crea pedidos)
        $planta = Role::create(['name' => 'planta']);
        $planta->givePermissionTo([
            'dashboard.ver',
            // Envíos (crear y ver sus propios envíos)
            'envios.ver',
            'envios.crear',
            'envios.tracking',
            // Documentos (ver sus documentos)
            'documentos.ver',
            'documentos.nota-entrega',
            'documentos.nota-entrega',
            // Monitoreo (ver sus envíos en tiempo real)
            'monitoreo.ver-propio',
        ]);

        // 2. ADMINISTRADOR (Asigna envíos a transportistas)
        $administrador = Role::create(['name' => 'administrador']);
        $administrador->givePermissionTo([
            'dashboard.ver',
            // Envíos (ver, editar, eliminar)
            'envios.ver',
            'envios.editar',
            'envios.eliminar',
            'envios.tracking',
            // Asignaciones (completas - individual y múltiple)
            'asignaciones.ver',
            'asignaciones.asignar',
            'asignaciones.remover',
            'asignaciones.multiple',
            // Rutas Multi-Entrega
            'rutas-multi.ver',
            'rutas-multi.crear',
            'rutas-multi.editar',
            'rutas-multi.monitorear',
            // Documentos
            'documentos.ver',
            'documentos.nota-entrega',
            'documentos.nota-entrega',
            // Monitoreo (ver todos)
            'monitoreo.ver-todos',
            // Vehículos y transportistas
            'vehiculos.ver',
            'vehiculos.gestionar',
            'transportistas.ver',
            'transportistas.gestionar',
            // Incidentes
            'incidentes.ver',
        ]);

        // 3. TRANSPORTISTA (Acepta/rechaza, monitorea sus envíos)
        $transportista = Role::create(['name' => 'transportista']);
        $transportista->givePermissionTo([
            'dashboard.ver',
            // Envíos (solo asignados)
            'envios.ver',
            'envios.tracking',
            'envios.aceptar',
            'envios.rechazar',
            'envios.actualizar-estado',
            'envios.entregar',
            // Rutas (solo asignadas)
            'rutas-multi.ver',
            // Documentos (de sus envíos)
            'documentos.ver',
            'documentos.nota-entrega',
            'documentos.nota-entrega',
            // Monitoreo (simular movimiento de sus envíos)
            'monitoreo.ver-propio',
            'monitoreo.simular',
            // Incidentes (crear/reportar)
            'incidentes.ver',
            'incidentes.crear',
        ]);

        // 4. ALMACEN (Recibe envíos, firma, reporta incidentes)
        $almacen = Role::create(['name' => 'almacen']);
        $almacen->givePermissionTo([
            'dashboard.ver',
            // Envíos (solo los que le llegan)
            'envios.ver',
            'envios.tracking',
            'envios.firmar',
            // Documentos (nota de entrega/venta)
            'documentos.ver',
            'documentos.nota-entrega',
            'documentos.nota-entrega',
            // Monitoreo (ver envíos que vienen hacia su almacén)
            'monitoreo.ver-propio',
            // Incidentes (reportar problemas con pedidos)
            'incidentes.ver',
            'incidentes.crear',
            'incidentes.reportar',
        ]);

        $this->command->info('✅ Roles creados exitosamente!');
        $this->command->info('');
        $this->command->info('📋 Roles creados según el flujo real:');
        $this->command->info('  1. Planta (crea envíos desde planta)');
        $this->command->info('  2. Administrador (asigna envíos a transportistas)');
        $this->command->info('  3. Transportista (acepta/rechaza, monitoreo, entrega)');
        $this->command->info('  4. Almacén (recibe envíos, firma, reporta incidentes)');
        $this->command->info('');
        $this->command->info('📝 Total de permisos: ' . Permission::count());
    }
}
