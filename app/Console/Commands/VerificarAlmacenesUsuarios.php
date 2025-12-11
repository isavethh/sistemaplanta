<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Almacen;

class VerificarAlmacenesUsuarios extends Command
{
    protected $signature = 'almacenes:verificar';
    protected $description = 'Verificar y asignar almacenes a usuarios de tipo almacen';

    public function handle()
    {
        $this->info('🔍 Verificando almacenes de usuarios...');
        
        $usuarios = User::where('email', 'jorge@sistema.com')
            ->orWhere('tipo', 'almacen')
            ->orWhere('role', 'almacen')
            ->get();
        
        foreach ($usuarios as $usuario) {
            $almacen = Almacen::where('usuario_almacen_id', $usuario->id)->first();
            
            if ($almacen) {
                $this->info("✅ {$usuario->name} ({$usuario->email}) tiene almacén: {$almacen->nombre}");
            } else {
                $this->warn("⚠️  {$usuario->name} ({$usuario->email}) NO tiene almacén asignado");
                
                // Buscar un almacén disponible
                $almacenDisponible = Almacen::where('es_planta', false)
                    ->where('activo', true)
                    ->whereNull('usuario_almacen_id')
                    ->first();
                
                if ($almacenDisponible) {
                    $almacenDisponible->update(['usuario_almacen_id' => $usuario->id]);
                    $this->info("   ✅ Almacén '{$almacenDisponible->nombre}' asignado");
                } else {
                    // Crear nuevo almacén
                    $nuevoAlmacen = Almacen::create([
                        'nombre' => 'Almacén ' . $usuario->name,
                        'usuario_almacen_id' => $usuario->id,
                        'latitud' => -17.7833,
                        'longitud' => -63.1821,
                        'direccion_completa' => 'Dirección del almacén de ' . $usuario->name,
                        'es_planta' => false,
                        'activo' => true,
                    ]);
                    $this->info("   ✅ Nuevo almacén '{$nuevoAlmacen->nombre}' creado y asignado");
                }
            }
        }
        
        return 0;
    }
}

