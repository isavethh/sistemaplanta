<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Eliminar transportista_id de envio_asignaciones para romper el cuadrado de conexiones
     * 
     * Cuadrado de conexiones actual:
     * - envios → users (cliente_id)
     * - envios → almacenes → users (usuario_almacen_id)
     * - envios → envio_asignaciones → users (transportista_id) ← REDUNDANTE
     * - envios → envio_asignaciones → vehiculos → users (transportista_id) ← CORRECTA
     * 
     * Solución: Eliminar transportista_id de envio_asignaciones
     * El transportista se obtiene a través de: vehiculo_id → vehiculos.transportista_id
     */
    public function up(): void
    {
        if (Schema::hasTable('envio_asignaciones')) {
            // Verificar asignaciones sin vehiculo_id
            $asignacionesSinVehiculo = DB::table('envio_asignaciones')
                ->whereNull('vehiculo_id')
                ->count();
            
            if ($asignacionesSinVehiculo > 0) {
                \Log::warning("Hay {$asignacionesSinVehiculo} asignaciones sin vehiculo_id. Estas asignaciones no podrán obtener el transportista.");
            }
            
            // Eliminar foreign key si existe
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                if (Schema::hasColumn('envio_asignaciones', 'transportista_id')) {
                    try {
                        $table->dropForeign(['transportista_id']);
                    } catch (\Exception $e) {
                        // Foreign key no existe o ya fue eliminada
                    }
                }
            });
            
            // Eliminar índice único si incluye transportista_id
            try {
                DB::statement("DROP INDEX IF EXISTS unique_asignacion");
            } catch (\Exception $e) {
                // Índice no existe
            }
            
            // Eliminar columna transportista_id
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                if (Schema::hasColumn('envio_asignaciones', 'transportista_id')) {
                    $table->dropColumn('transportista_id');
                }
            });
            
            // Crear nuevo índice único sin transportista_id (solo envio_id y vehiculo_id)
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                try {
                    $table->unique(['envio_id', 'vehiculo_id'], 'unique_asignacion_envio_vehiculo');
                } catch (\Exception $e) {
                    // Índice ya existe o hay datos duplicados
                }
            });
        }
    }

    public function down(): void
    {
        // Recrear columna si es necesario revertir
        if (Schema::hasTable('envio_asignaciones') && !Schema::hasColumn('envio_asignaciones', 'transportista_id')) {
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                $table->unsignedBigInteger('transportista_id')->nullable()->after('envio_id');
                $table->foreign('transportista_id')->references('id')->on('users')->onDelete('set null');
            });
            
            // Intentar restaurar transportista_id desde vehiculos
            DB::statement('
                UPDATE envio_asignaciones ea
                SET transportista_id = v.transportista_id
                FROM vehiculos v
                WHERE ea.vehiculo_id = v.id
                AND ea.transportista_id IS NULL
            ');
            
            // Recrear índice único con transportista_id
            try {
                DB::statement("DROP INDEX IF EXISTS unique_asignacion_envio_vehiculo");
                Schema::table('envio_asignaciones', function (Blueprint $table) {
                    $table->unique(['envio_id', 'transportista_id', 'vehiculo_id'], 'unique_asignacion');
                });
            } catch (\Exception $e) {
                // Error al recrear
            }
        }
    }
};

