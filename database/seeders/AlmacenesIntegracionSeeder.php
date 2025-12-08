<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Almacen;

/**
 * Seeder para configurar correctamente los almacenes para la integración.
 * 
 * Asegura que:
 * 1. Exista un almacén con es_planta=true (origen de todos los envíos)
 * 2. Existan almacenes destino (es_planta=false)
 */
class AlmacenesIntegracionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏭 Configurando almacenes para integración...');

        // 1. Buscar almacén existente que contenga "Planta" y marcarlo como es_planta=true
        $planta = Almacen::where('nombre', 'like', '%Planta%')->first();

        if ($planta) {
            $planta->update([
                'es_planta' => true,
                'activo' => true,
            ]);
            $this->command->info("✅ Almacén '{$planta->nombre}' configurado como Planta Principal (origen)");
        } else {
            // Crear almacén planta si no existe
            $planta = Almacen::create([
                'nombre' => 'Planta Principal',
                'direccion_completa' => 'Av. Cristo Redentor, Santa Cruz de la Sierra, Bolivia',
                'latitud' => -17.7833,
                'longitud' => -63.1821,
                'es_planta' => true,
                'activo' => true,
            ]);
            $this->command->info("✅ Almacén 'Planta Principal' creado como origen");
        }

        // 2. Asegurar que existan al menos 2 almacenes destino
        $almacenesDestino = Almacen::where('es_planta', false)->where('activo', true)->count();

        if ($almacenesDestino < 2) {
            $destinos = [
                [
                    'nombre' => 'Almacén Norte',
                    'direccion_completa' => 'Av. Banzer Km 5, Santa Cruz de la Sierra',
                    'latitud' => -17.7500,
                    'longitud' => -63.2000,
                    'es_planta' => false,
                    'activo' => true,
                ],
                [
                    'nombre' => 'Almacén Sur',
                    'direccion_completa' => 'Av. Santos Dumont, Santa Cruz de la Sierra',
                    'latitud' => -17.8100,
                    'longitud' => -63.1700,
                    'es_planta' => false,
                    'activo' => true,
                ],
            ];

            foreach ($destinos as $destino) {
                $almacen = Almacen::firstOrCreate(
                    ['nombre' => $destino['nombre']],
                    $destino
                );
                if ($almacen->wasRecentlyCreated) {
                    $this->command->info("✅ Almacén destino '{$almacen->nombre}' creado");
                }
            }
        }

        // 3. Marcar todos los demás almacenes como es_planta=false
        Almacen::where('id', '!=', $planta->id)
            ->whereNull('es_planta')
            ->update(['es_planta' => false]);

        $this->command->info('');
        $this->command->info('📍 Configuración de almacenes:');
        $this->command->info("   🏭 Origen (Planta): {$planta->nombre}");
        $this->command->info("   📦 Destinos disponibles: " . Almacen::where('es_planta', false)->where('activo', true)->count());
    }
}
