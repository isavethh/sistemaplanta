<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehiculo;
use App\Models\TipoTransporte;

class CrearVehiculosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener tipo de transporte (o crear uno por defecto)
        $tipoTransporte = TipoTransporte::firstOrCreate(
            ['nombre' => 'Terrestre'],
            ['descripcion' => 'Transporte por carretera']
        );

        // Array de vehículos
        $vehiculos = [
            // CAMIONES GRANDES (>5 toneladas)
            [
                'placa' => 'SCZ-1380',
                'marca' => 'Volvo',
                'modelo' => 'FH16',
                'anio' => 2022,
                'tipo_vehiculo' => 'Camión',
                'licencia_requerida' => 'C',
                'capacidad_carga' => 15000,
                'capacidad_volumen' => 85.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'SCZ-2450',
                'marca' => 'Mercedes-Benz',
                'modelo' => 'Actros',
                'anio' => 2021,
                'tipo_vehiculo' => 'Camión',
                'licencia_requerida' => 'C',
                'capacidad_carga' => 18000,
                'capacidad_volumen' => 95.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'LPZ-3690',
                'marca' => 'Scania',
                'modelo' => 'R450',
                'anio' => 2023,
                'tipo_vehiculo' => 'Camión',
                'licencia_requerida' => 'C',
                'capacidad_carga' => 20000,
                'capacidad_volumen' => 100.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'CBB-4120',
                'marca' => 'Freightliner',
                'modelo' => 'Cascadia',
                'anio' => 2022,
                'tipo_vehiculo' => 'Camión',
                'licencia_requerida' => 'C',
                'capacidad_carga' => 16000,
                'capacidad_volumen' => 90.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            
            // CAMIONETAS MEDIANAS (2-5 toneladas)
            [
                'placa' => 'SCZ-5670',
                'marca' => 'Toyota',
                'modelo' => 'Hilux Carga',
                'anio' => 2023,
                'tipo_vehiculo' => 'Camioneta',
                'licencia_requerida' => 'B',
                'capacidad_carga' => 3000,
                'capacidad_volumen' => 25.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'LPZ-6890',
                'marca' => 'Isuzu',
                'modelo' => 'NQR',
                'anio' => 2022,
                'tipo_vehiculo' => 'Camioneta',
                'licencia_requerida' => 'B',
                'capacidad_carga' => 4500,
                'capacidad_volumen' => 35.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'CBB-7230',
                'marca' => 'Mitsubishi',
                'modelo' => 'Canter',
                'anio' => 2021,
                'tipo_vehiculo' => 'Camioneta',
                'licencia_requerida' => 'B',
                'capacidad_carga' => 4000,
                'capacidad_volumen' => 30.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'SCZ-8450',
                'marca' => 'Ford',
                'modelo' => 'F-350',
                'anio' => 2023,
                'tipo_vehiculo' => 'Camioneta',
                'licencia_requerida' => 'B',
                'capacidad_carga' => 3500,
                'capacidad_volumen' => 28.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            
            // VEHÍCULOS PEQUEÑOS (<2 toneladas)
            [
                'placa' => 'LPZ-9120',
                'marca' => 'Nissan',
                'modelo' => 'NV350',
                'anio' => 2022,
                'tipo_vehiculo' => 'Van',
                'licencia_requerida' => 'A',
                'capacidad_carga' => 1500,
                'capacidad_volumen' => 15.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'CBB-1560',
                'marca' => 'Chevrolet',
                'modelo' => 'N300 Max',
                'anio' => 2023,
                'tipo_vehiculo' => 'Van',
                'licencia_requerida' => 'A',
                'capacidad_carga' => 1200,
                'capacidad_volumen' => 12.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'SCZ-2780',
                'marca' => 'Hyundai',
                'modelo' => 'Porter',
                'anio' => 2022,
                'tipo_vehiculo' => 'Furgón',
                'licencia_requerida' => 'A',
                'capacidad_carga' => 1800,
                'capacidad_volumen' => 18.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'LPZ-3340',
                'marca' => 'Suzuki',
                'modelo' => 'Super Carry',
                'anio' => 2021,
                'tipo_vehiculo' => 'Van',
                'licencia_requerida' => 'A',
                'capacidad_carga' => 1000,
                'capacidad_volumen' => 10.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            
            // CAMIONES ADICIONALES
            [
                'placa' => 'SCZ-4590',
                'marca' => 'Kenworth',
                'modelo' => 'T680',
                'anio' => 2023,
                'tipo_vehiculo' => 'Camión',
                'licencia_requerida' => 'C',
                'capacidad_carga' => 17000,
                'capacidad_volumen' => 92.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'CBB-5670',
                'marca' => 'MAN',
                'modelo' => 'TGX',
                'anio' => 2022,
                'tipo_vehiculo' => 'Camión',
                'licencia_requerida' => 'C',
                'capacidad_carga' => 19000,
                'capacidad_volumen' => 98.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
            [
                'placa' => 'LPZ-6780',
                'marca' => 'Iveco',
                'modelo' => 'Stralis',
                'anio' => 2021,
                'tipo_vehiculo' => 'Camión',
                'licencia_requerida' => 'C',
                'capacidad_carga' => 16500,
                'capacidad_volumen' => 88.00,
                'disponible' => true,
                'estado' => 'activo'
            ],
        ];

        foreach ($vehiculos as $vehiculoData) {
            Vehiculo::firstOrCreate(
                ['placa' => $vehiculoData['placa']],
                array_merge($vehiculoData, [
                    'tipo_transporte_id' => $tipoTransporte->id,
                ])
            );
        }

        $this->command->info('✅ Vehículos creados exitosamente:');
        $this->command->info('');
        $this->command->info('🚛 CAMIONES GRANDES (Lic C): 6 unidades');
        $this->command->info('   - Volvo FH16, Mercedes Actros, Scania R450');
        $this->command->info('   - Freightliner, Kenworth, MAN, Iveco');
        $this->command->info('   - Capacidad: 15-20 toneladas');
        $this->command->info('');
        $this->command->info('🚐 CAMIONETAS MEDIANAS (Lic B): 4 unidades');
        $this->command->info('   - Toyota Hilux, Isuzu NQR, Mitsubishi Canter, Ford F-350');
        $this->command->info('   - Capacidad: 3-4.5 toneladas');
        $this->command->info('');
        $this->command->info('🚙 VEHÍCULOS PEQUEÑOS (Lic A): 4 unidades');
        $this->command->info('   - Nissan NV350, Chevrolet N300, Hyundai Porter, Suzuki');
        $this->command->info('   - Capacidad: 1-1.8 toneladas');
        $this->command->info('');
        $this->command->info('📊 TOTAL: 15 vehículos disponibles');
    }
}

