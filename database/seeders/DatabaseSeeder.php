<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Primero crear roles y permisos (requerido para asignar roles a usuarios)
        $this->call(RolesAndPermissionsSeeder::class);
        
        // 2. Crear datos iniciales básicos (categorías, tipos de empaque, unidades de medida, etc.)
        $this->call(InitialSeeder::class);
        
        // 3. Crear usuarios del sistema (Mario, Carlos, Jorge, etc.)
        $this->call(CrearUsuariosPorRolSeeder::class);
        
        // 4. Crear tamaños de vehículos si no existen
        $this->call(TamanoVehiculoSeeder::class);
        
        // 5. Crear tipos de empaque si no existen
        $this->call(TiposEmpaqueSeeder::class);
    }
}
