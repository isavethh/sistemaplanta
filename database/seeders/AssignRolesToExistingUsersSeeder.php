<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignRolesToExistingUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder asigna roles de Spatie a usuarios existentes
     * basándose en sus campos 'role' y 'tipo' actuales
     */
    public function run(): void
    {
        $this->command->info('🔄 Migrando usuarios existentes a roles Spatie...');
        $this->command->info('');

        $users = User::all();
        $migratedCount = 0;

        foreach ($users as $user) {
            $roleToAssign = $this->determineRole($user);
            
            if ($roleToAssign) {
                // Asignar rol de Spatie
                $user->assignRole($roleToAssign);
                
                $this->command->info("✅ {$user->name} ({$user->email}) -> Rol: {$roleToAssign}");
                $migratedCount++;
            } else {
                $this->command->warn("⚠️  {$user->name} ({$user->email}) -> Sin rol definido, asignando 'cliente' por defecto");
                $user->assignRole('cliente');
                $migratedCount++;
            }
        }

        $this->command->info('');
        $this->command->info("✅ Total de usuarios migrados: {$migratedCount}");
    }

    /**
     * Determinar el rol de Spatie basándose en campos legacy
     */
    private function determineRole(User $user): ?string
    {
        $role = $user->role ?? $user->tipo ?? '';

        // Mapeo de roles legacy a roles Spatie
        $mapping = [
            'admin' => 'super-admin',  // Usuarios admin existentes serán super-admin
            'administrador' => 'admin',
            'transportista' => 'transportista',
            'almacen' => 'gestor-almacen',
            'cliente' => 'cliente',
            'despachador' => 'despachador',
        ];

        // Si el usuario es admin@orgtrack.com, asignar super-admin
        if ($user->email === 'admin@orgtrack.com') {
            return 'super-admin';
        }

        // Si el usuario es transportista, asignar rol transportista
        if ($user->email === 'trans@orgtrack.com' || strpos($user->email, 'transportista') !== false) {
            return 'transportista';
        }

        return $mapping[strtolower($role)] ?? null;
    }
}
