<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Eliminar relación duplicada entre envios y users
     * 
     * Problema: envios tiene DOS relaciones con users:
     * 1. cliente_id - el cliente que solicita el envío
     * 2. created_by - el usuario que creó el envío
     * 
     * Solución: Mantener solo cliente_id (quien solicita el envío)
     * Si created_by existe y tiene datos diferentes, migrarlos a cliente_id
     */
    public function up(): void
    {
        if (Schema::hasTable('envios')) {
            // Si existe created_by, migrar datos a cliente_id si cliente_id está vacío
            if (Schema::hasColumn('envios', 'created_by') && Schema::hasColumn('envios', 'cliente_id')) {
                // Migrar created_by a cliente_id donde cliente_id sea NULL
                DB::statement('
                    UPDATE envios 
                    SET cliente_id = created_by 
                    WHERE cliente_id IS NULL AND created_by IS NOT NULL
                ');
                
                // Eliminar created_by
                Schema::table('envios', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['created_by']);
                    } catch (\Exception $e) {
                        // Foreign key no existe o ya fue eliminada
                    }
                    $table->dropColumn('created_by');
                });
            }
            
            // Eliminar índice de created_by si existe
            Schema::table('envios', function (Blueprint $table) {
                if ($this->indexExists('envios', 'idx_envios_created_by')) {
                    $table->dropIndex('idx_envios_created_by');
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
        // No revertir - mantener solo cliente_id es la estructura correcta
    }
};
