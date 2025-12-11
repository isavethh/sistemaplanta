<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Almacen;

class AsignarAlmacenesAUsuariosSeeder extends Seeder
{
    /**
     * Asignar almacenes a usuarios de tipo almacen
     */
    public function run(): void
    {
        $this->command->info('🔗 Asignando almacenes a usuarios...');
        
        // Obtener usuarios de tipo almacen
        $usuariosAlmacen = User::where('tipo', 'almacen')
            ->orWhere('role', 'almacen')
            ->orWhereHas('roles', function($q) {
                $q->where('name', 'almacen');
            })
            ->get();
        
        // Obtener almacenes disponibles (que no sean planta y no tengan usuario asignado)
        $almacenesDisponibles = Almacen::where('es_planta', false)
            ->where('activo', true)
            ->whereNull('usuario_almacen_id')
            ->get();
        
        if ($usuariosAlmacen->isEmpty()) {
            $this->command->warn('⚠️  No se encontraron usuarios de tipo almacen');
            return;
        }
        
        if ($almacenesDisponibles->isEmpty()) {
            $this->command->warn('⚠️  No hay almacenes disponibles sin usuario asignado');
            $this->command->info('💡 Creando almacenes para usuarios...');
            
            // Crear almacenes para usuarios que no tienen
            foreach ($usuariosAlmacen as $index => $usuario) {
                $almacen = Almacen::create([
                    'nombre' => 'Almacén ' . $usuario->name,
                    'usuario_almacen_id' => $usuario->id,
                    'latitud' => -17.7833 + ($index * 0.01),
                    'longitud' => -63.1821 + ($index * 0.01),
                    'direccion_completa' => 'Dirección del almacén de ' . $usuario->name,
                    'es_planta' => false,
                    'activo' => true,
                ]);
                
                $this->command->info("✅ Almacén '{$almacen->nombre}' creado y asignado a {$usuario->name}");
            }
        } else {
            // Asignar almacenes disponibles a usuarios
            foreach ($usuariosAlmacen as $index => $usuario) {
                // Verificar si ya tiene un almacén asignado
                $almacenExistente = Almacen::where('usuario_almacen_id', $usuario->id)->first();
                
                if ($almacenExistente) {
                    $this->command->info("ℹ️  Usuario {$usuario->name} ya tiene almacén asignado: {$almacenExistente->nombre}");
                    continue;
                }
                
                // Asignar almacén disponible
                if (isset($almacenesDisponibles[$index])) {
                    $almacen = $almacenesDisponibles[$index];
                    $almacen->update(['usuario_almacen_id' => $usuario->id]);
                    $this->command->info("✅ Almacén '{$almacen->nombre}' asignado a {$usuario->name}");
                } else {
                    // Si no hay más almacenes disponibles, crear uno nuevo
                    $almacen = Almacen::create([
                        'nombre' => 'Almacén ' . $usuario->name,
                        'usuario_almacen_id' => $usuario->id,
                        'latitud' => -17.7833 + ($index * 0.01),
                        'longitud' => -63.1821 + ($index * 0.01),
                        'direccion_completa' => 'Dirección del almacén de ' . $usuario->name,
                        'es_planta' => false,
                        'activo' => true,
                    ]);
                    
                    $this->command->info("✅ Almacén '{$almacen->nombre}' creado y asignado a {$usuario->name}");
                }
            }
        }
        
        $this->command->info('');
        $this->command->info('✅ Asignación de almacenes completada');
    }
}

