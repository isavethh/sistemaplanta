<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Eliminar transportista_id de envio_asignaciones para romper la triangulación
     * 
     * Triangulación actual: 
     * - envio_asignaciones → users (a través de transportista_id)
     * - envio_asignaciones → vehiculos (a través de vehiculo_id)
     * - vehiculos → users (a través de transportista_id)
     * 
     * Solución: Eliminar transportista_id de envio_asignaciones
     * El transportista se obtiene a través de: vehiculo_id → vehiculos.transportista_id
     */
    public function up(): void
    {
        if (Schema::hasTable('envio_asignaciones')) {
            // Verificar asignaciones sin vehiculo_id (no se permiten)
            $asignacionesSinVehiculo = DB::table('envio_asignaciones')
                ->whereNull('vehiculo_id')
                ->count();
            
            if ($asignacionesSinVehiculo > 0) {
                \Log::warning("Hay {$asignacionesSinVehiculo} asignaciones sin vehiculo_id. Estas asignaciones no podrán obtener el transportista.");
            }
            
            // Verificar vehículos sin transportista_id (se permiten, pero las asignaciones no podrán obtener transportista a través del vehículo)
            $vehiculosSinTransportista = DB::table('vehiculos')
                ->whereNull('transportista_id')
                ->count();
            
            if ($vehiculosSinTransportista > 0) {
                \Log::warning("Hay {$vehiculosSinTransportista} vehículos sin transportista_id. Las asignaciones que usen estos vehículos no podrán obtener el transportista a través del vehículo.");
            }
            
            // Verificar consistencia: que transportista_id de asignación coincida con transportista_id del vehículo
            $inconsistencias = DB::table('envio_asignaciones as ea')
                ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
                ->whereColumn('ea.transportista_id', '!=', 'v.transportista_id')
                ->count();
            
            if ($inconsistencias > 0) {
                \Log::warning("Hay {$inconsistencias} asignaciones donde el transportista no coincide con el del vehículo. Se continuará de todas formas.");
            }
            
            // Eliminar foreign key
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                try {
                    $table->dropForeign(['transportista_id']);
                } catch (\Exception $e) {
                    // Foreign key no existe o ya fue eliminada
                }
            });
            
            // Eliminar índice único si incluye transportista_id
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                try {
                    $table->dropUnique('unique_asignacion');
                } catch (\Exception $e) {
                    // Índice no existe
                }
            });
            
            // Eliminar columna transportista_id
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                if (Schema::hasColumn('envio_asignaciones', 'transportista_id')) {
                    $table->dropColumn('transportista_id');
                }
            });
            
            // Crear nuevo índice único sin transportista_id
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                try {
                    $table->unique(['envio_id', 'vehiculo_id'], 'unique_asignacion_envio_vehiculo');
                } catch (\Exception $e) {
                    // Índice ya existe
                }
            });
        }
    }

    public function down(): void
    {
        // Recrear columna si es necesario revertir
        if (Schema::hasTable('envio_asignaciones') && !Schema::hasColumn('envio_asignaciones', 'transportista_id')) {
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                $table->foreignId('transportista_id')->nullable()->after('envio_id')->constrained('users')->onDelete('cascade');
            });
            
            // Intentar restaurar transportista_id desde vehiculos
            DB::statement('
                UPDATE envio_asignaciones ea
                SET transportista_id = v.transportista_id
                FROM vehiculos v
                WHERE ea.vehiculo_id = v.id
                AND ea.transportista_id IS NULL
            ');
            
            // Recrear índice único
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                try {
                    $table->dropUnique('unique_asignacion_envio_vehiculo');
                    $table->unique(['envio_id', 'transportista_id', 'vehiculo_id'], 'unique_asignacion');
                } catch (\Exception $e) {
                    // Error al recrear
                }
            });
        }
    }
};
