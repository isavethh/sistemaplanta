<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corregir relaciones duplicadas e incorrectas entre las 4 tablas principales
     * 
     * Problemas identificados:
     * 1. Almacen tiene direccion_id en el modelo pero NO existe en la BD (relación fantasma)
     * 2. User tiene relaciones que usan columnas incorrectas:
     *    - enviosComoTransportista() usa transportista_id pero no existe en envios
     *    - almacenesAcargo() usa encargado_id pero en almacenes es usuario_almacen_id
     * 3. Verificar que no haya columnas duplicadas o relaciones circulares
     */
    public function up(): void
    {
        // 1. Verificar y eliminar direccion_id de almacenes si existe (no debería existir)
        if (Schema::hasTable('almacenes') && Schema::hasColumn('almacenes', 'direccion_id')) {
            Schema::table('almacenes', function (Blueprint $table) {
                $table->dropForeign(['direccion_id']);
                $table->dropColumn('direccion_id');
            });
        }

        // 2. Verificar que envios NO tenga transportista_id (debe estar solo en envio_asignaciones)
        if (Schema::hasTable('envios') && Schema::hasColumn('envios', 'transportista_id')) {
            Schema::table('envios', function (Blueprint $table) {
                // Eliminar si existe (relación incorrecta, debe estar en envio_asignaciones)
                try {
                    $table->dropForeign(['transportista_id']);
                } catch (\Exception $e) {
                    // No existe foreign key, continuar
                }
                $table->dropColumn('transportista_id');
            });
        }

        // 3. Verificar que almacenes NO tenga encargado_id (debe ser usuario_almacen_id)
        if (Schema::hasTable('almacenes') && Schema::hasColumn('almacenes', 'encargado_id')) {
            Schema::table('almacenes', function (Blueprint $table) {
                // Si existe encargado_id, migrar datos a usuario_almacen_id si está vacío
                DB::statement('
                    UPDATE almacenes 
                    SET usuario_almacen_id = encargado_id 
                    WHERE usuario_almacen_id IS NULL AND encargado_id IS NOT NULL
                ');
                try {
                    $table->dropForeign(['encargado_id']);
                } catch (\Exception $e) {
                    // No existe foreign key, continuar
                }
                $table->dropColumn('encargado_id');
            });
        }

        // 4. Asegurar que direcciones tenga las relaciones correctas (almacen_origen_id y almacen_destino_id)
        // Estas NO son duplicadas, son correctas (una ruta tiene origen y destino)
        // Pero verificamos que no haya registros con mismo origen y destino duplicados
        if (Schema::hasTable('direcciones')) {
            // Limpiar duplicados si existen
            DB::statement('
                DELETE FROM direcciones d1
                USING direcciones d2
                WHERE d1.id < d2.id 
                AND d1.almacen_origen_id = d2.almacen_origen_id
                AND d1.almacen_destino_id = d2.almacen_destino_id
            ');
        }

        // 5. Verificar que users NO tenga relaciones directas incorrectas
        // users -> almacenes: CORRECTO (usuario_almacen_id en almacenes)
        // users -> envios: INCORRECTO (debe ser a través de envio_asignaciones)
        // users -> direcciones: NO DEBE EXISTIR (direcciones conecta almacenes, no users)

        // 6. Asegurar índices únicos para evitar duplicados
        if (Schema::hasTable('envio_asignaciones')) {
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                // Ya debería existir unique_envio_asignacion, pero verificamos
                if (!$this->indexExists('envio_asignaciones', 'unique_envio_asignacion')) {
                    try {
                        $table->unique('envio_id', 'unique_envio_asignacion');
                    } catch (\Exception $e) {
                        // Limpiar duplicados primero
                        DB::statement('
                            DELETE FROM envio_asignaciones a1
                            USING envio_asignaciones a2
                            WHERE a1.id < a2.id 
                            AND a1.envio_id = a2.envio_id
                        ');
                        $table->unique('envio_id', 'unique_envio_asignacion');
                    }
                }
            });
        }
    }

    /**
     * Helper para verificar si un índice existe (PostgreSQL)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $result = DB::select(
                "SELECT COUNT(*) as count 
                 FROM pg_indexes 
                 WHERE schemaname = 'public' 
                 AND tablename = ? 
                 AND indexname = ?",
                [$table, $indexName]
            );
            
            return isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function down(): void
    {
        // No revertir cambios para evitar pérdida de datos
        // Las relaciones incorrectas no deben restaurarse
    }
};
