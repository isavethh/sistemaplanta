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
        $transportista = User::firstOrCreate([
            'email' => 'trans@orgtrack.com'
        ], [
            'name' => 'Transportista',
            'password' => bcrypt('trans123'),
            'role' => 'transportista',
        ]);
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
        foreach(['Lechuga','Tomate','Zanahoria'] as $nombre) {
            \App\Models\Producto::create(['categoria_id' => $catVerduras->id, 'nombre' => $nombre]);
        }
        foreach(['Manzana','Banana','Naranja'] as $nombre) {
            \App\Models\Producto::create(['categoria_id' => $catFrutas->id, 'nombre' => $nombre]);
        }
        // Vehículos
        Vehiculo::create(['placa' => '1234ABC', 'tipo' => 'Camión', 'capacidad' => 1000, 'user_id' => $transportista->id]);
        Vehiculo::create(['placa' => '5678DEF', 'tipo' => 'Furgón', 'capacidad' => 500, 'user_id' => $transportista->id]);
    }
}
