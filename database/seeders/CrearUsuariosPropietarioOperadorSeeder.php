<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Almacen;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CrearUsuariosPropietarioOperadorSeeder extends Seeder
{
    /**
     * Crear usuarios de ejemplo para los nuevos roles: propietario y operador
     */
    public function run(): void
    {
        // Asegurar que los roles existan
        $rolePropietario = Role::firstOrCreate(['name' => 'propietario', 'guard_name' => 'web']);
        $roleOperador = Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);

        // 1. PROPIETARIO (Almacenes)
        $propietario1 = User::firstOrCreate(
            ['email' => 'propietario1@sistema.com'],
            [
                'name' => 'Juan Propietario',
                'password' => Hash::make('propietario123'),
                'tipo' => 'propietario',
                'role' => 'propietario',
                'telefono' => '71234568',
                'direccion' => 'Av. Principal #123, Santa Cruz, Bolivia',
            ]
        );
        $propietario1->syncRoles(['propietario']);

        // Crear almacén para el propietario
        $almacen1 = Almacen::firstOrCreate(
            ['nombre' => 'Almacén Centro'],
            [
                'usuario_almacen_id' => $propietario1->id,
                'latitud' => -17.8146,
                'longitud' => -63.1561,
                'direccion_completa' => 'Av. Principal #123, Santa Cruz, Bolivia',
                'es_planta' => false,
                'activo' => true,
            ]
        );

        $propietario2 = User::firstOrCreate(
            ['email' => 'propietario2@sistema.com'],
            [
                'name' => 'María Propietaria',
                'password' => Hash::make('propietario123'),
                'tipo' => 'propietario',
                'role' => 'propietario',
                'telefono' => '71234569',
                'direccion' => 'Av. Libertad #456, Santa Cruz, Bolivia',
            ]
        );
        $propietario2->syncRoles(['propietario']);

        // Crear almacén para el segundo propietario
        $almacen2 = Almacen::firstOrCreate(
            ['nombre' => 'Almacén Norte'],
            [
                'usuario_almacen_id' => $propietario2->id,
                'latitud' => -17.8000,
                'longitud' => -63.1500,
                'direccion_completa' => 'Av. Libertad #456, Santa Cruz, Bolivia',
                'es_planta' => false,
                'activo' => true,
            ]
        );

        // 2. OPERADOR (Trazabilidad)
        $operador1 = User::firstOrCreate(
            ['email' => 'operador1@sistema.com'],
            [
                'name' => 'Pedro Operador',
                'password' => Hash::make('operador123'),
                'tipo' => 'operador',
                'role' => 'operador',
                'telefono' => '71234570',
                'direccion' => 'Planta Trazabilidad, Santa Cruz, Bolivia',
            ]
        );
        $operador1->syncRoles(['operador']);

        $operador2 = User::firstOrCreate(
            ['email' => 'operador2@sistema.com'],
            [
                'name' => 'Ana Operadora',
                'password' => Hash::make('operador123'),
                'tipo' => 'operador',
                'role' => 'operador',
                'telefono' => '71234571',
                'direccion' => 'Planta Trazabilidad, Santa Cruz, Bolivia',
            ]
        );
        $operador2->syncRoles(['operador']);

        $this->command->info('✅ Usuarios de Propietario y Operador creados exitosamente!');
        $this->command->info('');
        $this->command->info('🏪 PROPIETARIOS (Almacenes):');
        $this->command->info('');
        $this->command->info('   Propietario 1:');
        $this->command->info('   - Email: propietario1@sistema.com');
        $this->command->info('   - Password: propietario123');
        $this->command->info('   - Almacén: Almacén Centro');
        $this->command->info('');
        $this->command->info('   Propietario 2:');
        $this->command->info('   - Email: propietario2@sistema.com');
        $this->command->info('   - Password: propietario123');
        $this->command->info('   - Almacén: Almacén Norte');
        $this->command->info('');
        $this->command->info('🏭 OPERADORES (Trazabilidad):');
        $this->command->info('');
        $this->command->info('   Operador 1:');
        $this->command->info('   - Email: operador1@sistema.com');
        $this->command->info('   - Password: operador123');
        $this->command->info('');
        $this->command->info('   Operador 2:');
        $this->command->info('   - Email: operador2@sistema.com');
        $this->command->info('   - Password: operador123');
        $this->command->info('');
    }
}
