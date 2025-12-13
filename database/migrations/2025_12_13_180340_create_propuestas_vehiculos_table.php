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
        Schema::create('propuestas_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('envio_id');
            $table->string('codigo_envio', 50);
            $table->json('propuesta_data'); // Datos completos de la propuesta (vehículos, pesos, volúmenes, etc.)
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('observaciones_trazabilidad')->nullable();
            $table->unsignedBigInteger('aprobado_por')->nullable(); // ID del usuario que aprobó/rechazó en Trazabilidad
            $table->timestamp('fecha_propuesta')->useCurrent();
            $table->timestamp('fecha_decision')->nullable();
            $table->timestamps();
            
            $table->foreign('envio_id')->references('id')->on('envios')->onDelete('cascade');
            $table->index('codigo_envio');
            $table->index('estado');
            $table->index('fecha_propuesta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propuestas_vehiculos');
    }
};
