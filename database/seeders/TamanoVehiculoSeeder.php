<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TamanoVehiculo;

class TamanoVehiculoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tamanos = [
            [
                'nombre' => 'Pequeño',
                'descripcion' => 'Vehículos pequeños como motos, autos compactos y camionetas pequeñas',
                'capacidad_min' => 0,
                'capacidad_max' => 1.5,
            ],
            [
                'nombre' => 'Mediano',
                'descripcion' => 'Camionetas medianas y furgonetas',
                'capacidad_min' => 1.5,
                'capacidad_max' => 3.5,
            ],
            [
                'nombre' => 'Grande',
                'descripcion' => 'Camiones medianos de carga',
                'capacidad_min' => 3.5,
                'capacidad_max' => 8.0,
            ],
            [
                'nombre' => 'Extra Grande',
                'descripcion' => 'Camiones grandes y tracto-camiones',
                'capacidad_min' => 8.0,
                'capacidad_max' => 20.0,
            ],
        ];

        foreach ($tamanos as $tamano) {
            TamanoVehiculo::updateOrCreate(
                ['nombre' => $tamano['nombre']],
                $tamano
            );
        }
    }
}
