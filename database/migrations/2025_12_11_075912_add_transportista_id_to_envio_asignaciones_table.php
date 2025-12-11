<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('envio_asignaciones', function (Blueprint $table) {
            // Agregar transportista_id si no existe (para permitir que cualquier vehículo sea usado por cualquier transportista)
            if (!Schema::hasColumn('envio_asignaciones', 'transportista_id')) {
                $table->unsignedBigInteger('transportista_id')->nullable()->after('envio_id');
                $table->foreign('transportista_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envio_asignaciones', function (Blueprint $table) {
            if (Schema::hasColumn('envio_asignaciones', 'transportista_id')) {
                $table->dropForeign(['transportista_id']);
                $table->dropColumn('transportista_id');
            }
        });
    }
};
