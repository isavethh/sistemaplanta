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
        
        // 3. Usuarios TRANSPORTISTA (10)
        $transportistas = [
            ['name' => 'Carlos Mamani', 'email' => 'carlos@sistema.com', 'licencia' => 'A'],
            ['name' => 'Luis Quispe', 'email' => 'luis@sistema.com', 'licencia' => 'B'],
            ['name' => 'Pedro Condori', 'email' => 'pedro@sistema.com', 'licencia' => 'A'],
            ['name' => 'Juan Apaza', 'email' => 'juan@sistema.com', 'licencia' => 'C'],
            ['name' => 'Miguel Flores', 'email' => 'miguel@sistema.com', 'licencia' => 'B'],
            ['name' => 'Roberto Castro', 'email' => 'roberto@sistema.com', 'licencia' => 'A'],
            ['name' => 'Javier Rojas', 'email' => 'javier@sistema.com', 'licencia' => 'C'],
            ['name' => 'Diego Mendez', 'email' => 'diego@sistema.com', 'licencia' => 'B'],
            ['name' => 'Fernando Lima', 'email' => 'fernando@sistema.com', 'licencia' => 'A'],
            ['name' => 'Oscar Vargas', 'email' => 'oscar@sistema.com', 'licencia' => 'C'],
        ];
        
        foreach ($transportistas as $transportistaData) {
            $transportista = User::firstOrCreate(
                ['email' => $transportistaData['email']],
                [
                    'name' => $transportistaData['name'],
                    'password' => Hash::make('trans123'),
                    'tipo' => 'transportista',
                    'role' => 'transportista',
                    'licencia' => $transportistaData['licencia'] ?? 'B',
                    'telefono' => '7' . rand(1000000, 9999999),
                    'disponible' => true,
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
        $this->command->info('   🚚 10 Transportistas: carlos@sistema.com, luis@sistema.com, pedro@sistema.com, etc. (pass: trans123)');
        $this->command->info('   📦 2 Almacenes: jorge@sistema.com, rosa@sistema.com (pass: almacen123)');
        $this->command->info('');
        $this->command->info('📋 Licencias de Transportistas:');
        $this->command->info('   Lic A: Carlos, Pedro, Roberto, Fernando');
        $this->command->info('   Lic B: Luis, Miguel, Diego');
        $this->command->info('   Lic C: Juan, Javier, Oscar');
    }
}
