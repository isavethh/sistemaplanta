<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
// use App\Models\Direccion; // Tabla eliminada - no se usa
use App\Models\Almacen;
use App\Models\TipoEmpaque;
use App\Models\UnidadMedida;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Vehiculo;
class InitialSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios
        $admin = User::firstOrCreate([
            'email' => 'admin@orgtrack.com'
        ], [
            'name' => 'Admin',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);
        
        // Asignar rol de Spatie al admin
        if (!$admin->hasRole('admin')) {
            $roleAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $admin->assignRole($roleAdmin);
        }
        
        $transportista = User::firstOrCreate([
            'email' => 'trans@orgtrack.com'
        ], [
            'name' => 'Transportista',
            'password' => bcrypt('trans123'),
            'role' => 'transportista',
        ]);
        
        // Asignar rol de Spatie al transportista
        if (!$transportista->hasRole('transportista')) {
            $roleTransportista = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'transportista', 'guard_name' => 'web']);
            $transportista->assignRole($roleTransportista);
        }
        // Direcciones - Tabla eliminada, ahora se usan direcciones directamente en almacenes
        // Almacén planta (con dirección completa en el mismo registro)
        $almacenPlanta = Almacen::create([
            'nombre' => 'Planta Central',
            'direccion_completa' => 'Av. Cristo Redentor, Santa Cruz',
            'latitud' => -17.7833,
            'longitud' => -63.1821,
            'usuario_almacen_id' => $admin->id,
            'es_planta' => true,
        ]);
        // Tipos de empaque
        $empaque1 = TipoEmpaque::create(['nombre' => 'Caja']);
        $empaque2 = TipoEmpaque::create(['nombre' => 'Bolsa']);
        $empaque3 = TipoEmpaque::create(['nombre' => 'Canasta']);
        // Unidades de medida
        $unidad1 = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg']);
        $unidad2 = UnidadMedida::create(['nombre' => 'Unidad', 'abreviatura' => 'u']);
        $unidad3 = UnidadMedida::create(['nombre' => 'Paquete', 'abreviatura' => 'paq']);
        // Categorías y productos
        $catVerduras = Categoria::create(['nombre' => 'Verduras']);
        $catFrutas = Categoria::create(['nombre' => 'Frutas']);
        
        // Generar códigos únicos para productos (usar firstOrCreate para evitar duplicados)
        $productosVerduras = ['Lechuga', 'Tomate', 'Zanahoria'];
        foreach($productosVerduras as $index => $nombre) {
            $codigo = 'VER-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            \App\Models\Producto::firstOrCreate(
                ['codigo' => $codigo],
                [
                    'categoria_id' => $catVerduras->id,
                    'nombre' => $nombre
                ]
            );
        }
        
        $productosFrutas = ['Manzana', 'Banana', 'Naranja'];
        foreach($productosFrutas as $index => $nombre) {
            $codigo = 'FRU-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            \App\Models\Producto::firstOrCreate(
                ['codigo' => $codigo],
                [
                    'categoria_id' => $catFrutas->id,
                    'nombre' => $nombre
                ]
            );
        }
        // Vehículos (usar estructura correcta de la tabla)
        // Nota: Los vehículos requieren más campos, así que solo creamos si hay tipos de transporte y tamaños disponibles
        $tipoTransporte = \App\Models\TipoTransporte::first();
        $tamanoVehiculo = \App\Models\TamanoVehiculo::first();
        $unidadMedida = UnidadMedida::first();
        
        if ($tipoTransporte && $tamanoVehiculo && $unidadMedida) {
            Vehiculo::firstOrCreate(
                ['placa' => '1234ABC'],
                [
                    'tipo_vehiculo' => 'Camión',
                    'tipo_transporte_id' => $tipoTransporte->id,
                    'tamano_vehiculo_id' => $tamanoVehiculo->id,
                    'capacidad_carga' => 1000,
                    'unidad_medida_carga_id' => $unidadMedida->id,
                    'capacidad_volumen' => 10,
                    'transportista_id' => $transportista->id,
                    'licencia_requerida' => 'B',
                    'disponible' => true,
                    'estado' => 'activo'
                ]
            );
            
            Vehiculo::firstOrCreate(
                ['placa' => '5678DEF'],
                [
                    'tipo_vehiculo' => 'Furgón',
                    'tipo_transporte_id' => $tipoTransporte->id,
                    'tamano_vehiculo_id' => $tamanoVehiculo->id,
                    'capacidad_carga' => 500,
                    'unidad_medida_carga_id' => $unidadMedida->id,
                    'capacidad_volumen' => 5,
                    'transportista_id' => $transportista->id,
                    'licencia_requerida' => 'B',
                    'disponible' => true,
                    'estado' => 'activo'
                ]
            );
        }
    }
}
