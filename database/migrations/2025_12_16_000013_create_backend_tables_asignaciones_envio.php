<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Nota: El backend usa 'asignaciones_envio' pero Laravel usa 'envio_asignaciones'.
     * Esta migración crea 'asignaciones_envio' para compatibilidad con el backend.
     * Ambas tablas pueden coexistir o se puede usar un alias/sinónimo.
     */
    public function up(): void
    {
        if (!Schema::hasTable('asignaciones_envio')) {
            Schema::create('asignaciones_envio', function (Blueprint $table) {
                $table->id();
                $table->foreignId('envio_id')->constrained('envios')->cascadeOnDelete();
                $table->foreignId('transportista_id')->nullable()->constrained('transportistas')->nullOnDelete();
                $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos')->nullOnDelete();
                $table->foreignId('tipo_vehiculo_id')->nullable()->constrained('tipos_vehiculo')->nullOnDelete();
                $table->timestamp('fecha_asignacion')->useCurrent();
                $table->text('notas')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_envio');
    }
};

