<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class MostrarRoles extends Command
{
    protected $signature = 'roles:mostrar';
    protected $description = 'Mostrar todos los roles y permisos del sistema';

    public function handle()
    {
        $this->info('');
        $this->info('=== ROLES CREADOS EN EL SISTEMA ===');
        $this->info('');

        $roles = Role::with('permissions', 'users')->get();

        foreach ($roles as $role) {
            $this->line('📌 ROL: <fg=cyan>' . strtoupper($role->name) . '</>');
            $this->line('   Total de permisos: ' . $role->permissions->count());
            $this->line('   Usuarios con este rol: ' . $role->users->count());
            
            if ($role->users->count() > 0) {
                $this->line('   <fg=yellow>Usuarios:</>');
                foreach ($role->users as $user) {
                    $this->line('      - ' . $user->name . ' (' . $user->email . ')');
                }
            }
            
            $this->info('');
        }

        $this->info('=== EXPLICACIÓN ===');
        $this->info('');
        $this->warn('ANTES: No tenías sistema de roles formal.');
        $this->warn('Solo tenías un campo "role" en users con valores: admin, transportista, almacen');
        $this->info('');
        $this->info('AHORA: Tienes 4 roles según el flujo real:');
        $this->info('');

        $this->line('<fg=green>1. PLANTA</> (Cliente)');
        $this->line('   - Crea envíos desde planta');
        $this->line('   - Ver sus envíos y documentos');
        $this->line('   - Monitoreo en tiempo real de sus envíos');
        $this->info('');

        $this->line('<fg=green>2. ADMINISTRADOR</>');
        $this->line('   - Asigna envíos a transportistas (individual y múltiple)');
        $this->line('   - Gestión de rutas multi-entrega');
        $this->line('   - Ver documentos y monitoreo de todos los envíos');
        $this->line('   - Gestionar vehículos y transportistas');
        $this->info('');

        $this->line('<fg=green>3. TRANSPORTISTA</>');
        $this->line('   - Aceptar/rechazar envíos asignados');
        $this->line('   - Acceso a documentación de envíos');
        $this->line('   - Monitoreo y simulación de movimiento');
        $this->line('   - Reportar incidentes');
        $this->info('');

        $this->line('<fg=green>4. ALMACEN</>');
        $this->line('   - Recibe envíos que llegan a su almacén');
        $this->line('   - Ver tracking en tiempo real');
        $this->line('   - Acceso a documentos (nota de entrega/venta)');
        $this->line('   - Reportar incidentes con pedidos');
        $this->line('   - Firmar al recibir el pedido');
        $this->info('');

        $this->line('<fg=cyan>Total de permisos:</> ' . Permission::count());
        $this->line('<fg=cyan>Total de usuarios con roles:</> ' . User::role(['planta', 'administrador', 'transportista', 'almacen'])->count());
        
        return 0;
    }
}
