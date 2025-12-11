<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposEmpaqueSeeder extends Seeder
{
    public function run()
    {
        $empaques = [
            [
                'nombre' => 'Caja Pequeña',
                'largo_cm' => 30,
                'ancho_cm' => 20,
                'alto_cm' => 15,
                'peso_maximo_kg' => 5,
                'volumen_cm3' => 30 * 20 * 15, // 9,000
                'icono' => '📦',
            ],
            [
                'nombre' => 'Caja Mediana',
                'largo_cm' => 40,
                'ancho_cm' => 30,
                'alto_cm' => 25,
                'peso_maximo_kg' => 15,
                'volumen_cm3' => 40 * 30 * 25, // 30,000
                'icono' => '📦',
            ],
            [
                'nombre' => 'Caja Grande',
                'largo_cm' => 60,
                'ancho_cm' => 40,
                'alto_cm' => 40,
                'peso_maximo_kg' => 30,
                'volumen_cm3' => 60 * 40 * 40, // 96,000
                'icono' => '📦',
            ],
            [
                'nombre' => 'Caja Extra Grande',
                'largo_cm' => 80,
                'ancho_cm' => 60,
                'alto_cm' => 50,
                'peso_maximo_kg' => 50,
                'volumen_cm3' => 80 * 60 * 50, // 240,000
                'icono' => '📦',
            ],
            [
                'nombre' => 'Bolsa Plastica Pequeña',
                'largo_cm' => 25,
                'ancho_cm' => 15,
                'alto_cm' => 5,
                'peso_maximo_kg' => 2,
                'volumen_cm3' => 25 * 15 * 5, // 1,875
                'icono' => '🛍️',
            ],
            [
                'nombre' => 'Bolsa Plastica Mediana',
                'largo_cm' => 40,
                'ancho_cm' => 30,
                'alto_cm' => 10,
                'peso_maximo_kg' => 5,
                'volumen_cm3' => 40 * 30 * 10, // 12,000
                'icono' => '🛍️',
            ],
            [
                'nombre' => 'Bolsa Plastica Grande',
                'largo_cm' => 60,
                'ancho_cm' => 40,
                'alto_cm' => 15,
                'peso_maximo_kg' => 10,
                'volumen_cm3' => 60 * 40 * 15, // 36,000
                'icono' => '🛍️',
            ],
            [
                'nombre' => 'Pallet Estandar',
                'largo_cm' => 120,
                'ancho_cm' => 100,
                'alto_cm' => 15,
                'peso_maximo_kg' => 1000,
                'volumen_cm3' => 120 * 100 * 15, // 180,000
                'icono' => '📐',
            ],
            [
                'nombre' => 'Caja de Carton Corrugado',
                'largo_cm' => 50,
                'ancho_cm' => 35,
                'alto_cm' => 30,
                'peso_maximo_kg' => 20,
                'volumen_cm3' => 50 * 35 * 30, // 52,500
                'icono' => '📦',
            ],
            [
                'nombre' => 'Contenedor Plastico con Tapa',
                'largo_cm' => 45,
                'ancho_cm' => 35,
                'alto_cm' => 28,
                'peso_maximo_kg' => 25,
                'volumen_cm3' => 45 * 35 * 28, // 44,100
                'icono' => '🗃️',
            ],
        ];

        foreach ($empaques as $empaque) {
            DB::table('tipos_empaque')->updateOrInsert(
                ['nombre' => $empaque['nombre']],
                $empaque
            );
        }

        echo "✅ Tipos de empaque con medidas creados exitosamente!\n";
    }
}

