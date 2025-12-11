<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('envios')) {
            Schema::table('envios', function (Blueprint $table) {
                if (!Schema::hasColumn('envios', 'ruta_entrega_id')) {
                    $table->unsignedBigInteger('ruta_entrega_id')->nullable()->after('fecha_entrega');
                }
                // Agregar índice solo si no existe
                if (!$this->indexExists('envios', 'envios_ruta_entrega_id_index')) {
                    $table->index('ruta_entrega_id');
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropIndex(['ruta_entrega_id']);
            $table->dropColumn('ruta_entrega_id');
        });
    }
};
