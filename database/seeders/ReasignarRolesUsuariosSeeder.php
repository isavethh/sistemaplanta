<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class ReasignarRolesUsuariosSeeder extends Seeder
{
    /**
     * Reasignar roles a usuarios según el flujo real del sistema
     */
    public function run(): void
    {
        $this->command->info('🔄 Reasignando roles a usuarios existentes...');
        $this->command->info('');

        $users = User::all();

        foreach ($users as $user) {
            $roleToAssign = $this->determineRoleReal($user);
            
            if ($roleToAssign) {
                $user->syncRoles([]); // Quitar roles anteriores
                $user->assignRole($roleToAssign);
                
                $this->command->info("✅ {$user->name} ({$user->email}) -> {$roleToAssign}");
            }
        }

        $this->command->info('');
        $this->command->info('✅ Roles reasignados según el flujo real del sistema');
    }

    /**
     * Determinar el rol real según el usuario
     */
    private function determineRoleReal(User $user): ?string
    {
        $role = $user->role ?? $user->tipo ?? '';
        $email = strtolower($user->email);

        // Mapeo basado en el flujo real
        $mapping = [
            'admin' => 'administrador',
            'administrador' => 'administrador',
            'transportista' => 'transportista',
            'almacen' => 'almacen',
            'cliente' => 'planta',
            'planta' => 'planta',
        ];

        // Lógica específica por email
        if (strpos($email, 'admin') !== false || $email === 'm@gmail.com') {
            return 'administrador';
        }

        if (strpos($email, 'transportista') !== false || 
            strpos($email, 'transport') !== false ||
            in_array($email, ['isavethg@gmail.com', 'edu@gmail.com', 'p@gmail.com', 'ed@gmail.com', 'xime@gmail.com'])) {
            return 'transportista';
        }

        return $mapping[strtolower($role)] ?? 'planta';
    }
}
