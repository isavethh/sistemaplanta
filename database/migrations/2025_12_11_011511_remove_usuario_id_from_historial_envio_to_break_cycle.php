<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Eliminar usuario_id de historial_envio para romper el ciclo
     * 
     * Ciclo actual: users → almacenes → envios → historial_envio → users
     * 
     * Solución: Eliminar usuario_id de historial_envio
     * El usuario se puede obtener a través de:
     * - historial_envio → envio → almacen_destino → usuario_almacen_id
     * - O almacenar en datos_extra cuando sea necesario
     */
    public function up(): void
    {
        if (Schema::hasTable('historial_envio')) {
            // Migrar usuario_id a datos_extra antes de eliminar la columna
            $registros = DB::table('historial_envio')
                ->whereNotNull('usuario_id')
                ->get();
            
            foreach ($registros as $registro) {
                $datosExtra = json_decode($registro->datos_extra ?? '{}', true);
                if (!isset($datosExtra['usuario_id'])) {
                    $datosExtra['usuario_id'] = $registro->usuario_id;
                    DB::table('historial_envio')
                        ->where('id', $registro->id)
                        ->update(['datos_extra' => json_encode($datosExtra)]);
                }
            }
            
            // Eliminar foreign key
            Schema::table('historial_envio', function (Blueprint $table) {
                try {
                    $table->dropForeign(['usuario_id']);
                } catch (\Exception $e) {
                    // Foreign key no existe o ya fue eliminada
                }
            });
            
            // Eliminar columna usuario_id
            Schema::table('historial_envio', function (Blueprint $table) {
                if (Schema::hasColumn('historial_envio', 'usuario_id')) {
                    $table->dropColumn('usuario_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Recrear columna si es necesario revertir
        if (Schema::hasTable('historial_envio') && !Schema::hasColumn('historial_envio', 'usuario_id')) {
            Schema::table('historial_envio', function (Blueprint $table) {
                $table->foreignId('usuario_id')->nullable()->after('descripcion')->constrained('users')->onDelete('set null');
            });
            
            // Intentar restaurar usuario_id desde datos_extra
            $registros = DB::table('historial_envio')->get();
            foreach ($registros as $registro) {
                $datosExtra = json_decode($registro->datos_extra ?? '{}', true);
                if (isset($datosExtra['usuario_id'])) {
                    DB::table('historial_envio')
                        ->where('id', $registro->id)
                        ->update(['usuario_id' => $datosExtra['usuario_id']]);
                }
            }
        }
    }
};
