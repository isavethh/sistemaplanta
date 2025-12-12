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
        Schema::create('incidentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_id')->nullable()->constrained('envios')->onDelete('set null');
            $table->string('tipo_incidente')->comment('Tipo de incidente reportado');
            $table->text('descripcion')->comment('Descripción detallada del incidente');
            $table->string('foto_url')->nullable()->comment('URL de la foto del incidente');
            $table->enum('estado', ['pendiente', 'en_proceso', 'resuelto'])->default('pendiente')->comment('Estado del incidente');
            $table->timestamp('fecha_reporte')->useCurrent()->comment('Fecha en que se reportó el incidente');
            $table->timestamp('fecha_resolucion')->nullable()->comment('Fecha en que se resolvió el incidente');
            $table->text('notas_resolucion')->nullable()->comment('Notas sobre la resolución del incidente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};

