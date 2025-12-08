<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CrearUsuariosPorRolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Usuario PLANTA (solo 1)
        $planta = User::firstOrCreate(
            ['email' => 'planta@sistema.com'],
            [
                'name' => 'Planta Principal',
                'password' => Hash::make('planta123'),
                'tipo' => 'planta',
                'role' => 'planta',
            ]
        );
        $planta->syncRoles(['planta']);
        
        // 2. Usuarios ADMINISTRADOR (2)
        $admins = [
            ['name' => 'Mario Admin', 'email' => 'mario@sistema.com'],
            ['name' => 'Ana Administradora', 'email' => 'ana@sistema.com'],
        ];
        
        foreach ($admins as $adminData) {
            $admin = User::firstOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => Hash::make('admin123'),
                    'tipo' => 'administrador',
                    'role' => 'administrador',
                ]
            );
            $admin->syncRoles(['administrador']);
        }
        
        // 3. Usuarios TRANSPORTISTA (3)
        $transportistas = [
            ['name' => 'Carlos Transportista', 'email' => 'carlos@sistema.com'],
            ['name' => 'Luis Conductor', 'email' => 'luis@sistema.com'],
            ['name' => 'Pedro Chofer', 'email' => 'pedro@sistema.com'],
        ];
        
        foreach ($transportistas as $transportistaData) {
            $transportista = User::firstOrCreate(
                ['email' => $transportistaData['email']],
                [
                    'name' => $transportistaData['name'],
                    'password' => Hash::make('trans123'),
                    'tipo' => 'transportista',
                    'role' => 'transportista',
                ]
            );
            $transportista->syncRoles(['transportista']);
        }
        
        // 4. Usuarios ALMACÉN (2)
        $almacenes = [
            ['name' => 'Jorge Almacenero', 'email' => 'jorge@sistema.com'],
            ['name' => 'Rosa Recepcionista', 'email' => 'rosa@sistema.com'],
        ];
        
        foreach ($almacenes as $almacenData) {
            $almacen = User::firstOrCreate(
                ['email' => $almacenData['email']],
                [
                    'name' => $almacenData['name'],
                    'password' => Hash::make('almacen123'),
                    'tipo' => 'almacen',
                    'role' => 'almacen',
                ]
            );
            $almacen->syncRoles(['almacen']);
        }
        
        $this->command->info('✅ Usuarios creados exitosamente:');
        $this->command->info('   🌱 1 Planta: planta@sistema.com (pass: planta123)');
        $this->command->info('   👨‍💼 2 Administradores: mario@sistema.com, ana@sistema.com (pass: admin123)');
        $this->command->info('   🚚 3 Transportistas: carlos@sistema.com, luis@sistema.com, pedro@sistema.com (pass: trans123)');
        $this->command->info('   📦 2 Almacenes: jorge@sistema.com, rosa@sistema.com (pass: almacen123)');
    }
}
