<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Direccion;
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
        // Direcciones
        $dirPlanta = Direccion::create([
            'calle' => 'Av. Cristo Redentor',
            'ciudad' => 'Santa Cruz',
            'departamento' => 'Santa Cruz',
            'lat' => -17.7833,
            'lng' => -63.1821,
            'descripcion' => 'Planta central, Av. Cristo Redentor, Santa Cruz',
        ]);
        $dir1 = Direccion::create([
            'calle' => 'Calle Libertad',
            'ciudad' => 'Santa Cruz',
            'departamento' => 'Santa Cruz',
            'lat' => -17.7890,
            'lng' => -63.1800,
            'descripcion' => 'Cliente 1, Calle Libertad',
        ]);
        $dir2 = Direccion::create([
            'calle' => 'Calle Aroma',
            'ciudad' => 'Santa Cruz',
            'departamento' => 'Santa Cruz',
            'lat' => -17.7900,
            'lng' => -63.1850,
            'descripcion' => 'Cliente 2, Calle Aroma',
        ]);
        // Almacén planta
        $almacenPlanta = Almacen::create([
            'nombre' => 'Planta Central',
            'direccion_id' => $dirPlanta->id,
            'user_id' => $admin->id,
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
