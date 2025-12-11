<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SimpleUsersSeeder extends Seeder
{
    /**
     * Crear solo 3 usuarios de ejemplo
     */
    public function run(): void
    {
        // 1. ADMIN
        $admin = User::firstOrCreate(
            ['email' => 'mario@sistema.com'],
            [
                'name' => 'Mario Admin',
                'password' => Hash::make('admin123'),
                'tipo' => 'administrador',
                'role' => 'admin',
            ]
        );
        $admin->syncRoles(['admin']);

        // 2. TRANSPORTISTA
        $transportista = User::firstOrCreate(
            ['email' => 'carlos@sistema.com'],
            [
                'name' => 'Carlos Mamani',
                'password' => Hash::make('trans123'),
                'tipo' => 'transportista',
                'role' => 'transportista',
                'licencia' => 'A',
                'telefono' => '71234567',
                'disponible' => true,
            ]
        );
        $transportista->syncRoles(['transportista']);

        // 3. ALMACEN
        $almacen = User::firstOrCreate(
            ['email' => 'jorge@sistema.com'],
            [
                'name' => 'Jorge Almacenero',
                'password' => Hash::make('almacen123'),
                'tipo' => 'almacen',
                'role' => 'almacen',
            ]
        );
        $almacen->syncRoles(['almacen']);

        $this->command->info('✅ Usuarios creados exitosamente:');
        $this->command->info('');
        $this->command->info('👨‍💼 ADMIN:');
        $this->command->info('   Email: mario@sistema.com');
        $this->command->info('   Password: admin123');
        $this->command->info('');
        $this->command->info('🚚 TRANSPORTISTA:');
        $this->command->info('   Email: carlos@sistema.com');
        $this->command->info('   Password: trans123');
        $this->command->info('');
        $this->command->info('📦 ALMACEN:');
        $this->command->info('   Email: jorge@sistema.com');
        $this->command->info('   Password: almacen123');
    }
}

