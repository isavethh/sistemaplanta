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
        if (!Schema::hasTable('ruta_paradas')) {
            Schema::create('ruta_paradas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ruta_entrega_id')->constrained('rutas_entrega')->cascadeOnDelete();
                $table->foreignId('envio_id')->constrained('envios')->cascadeOnDelete();
                $table->integer('orden');
                $table->string('estado', 50)->default('pendiente');
                $table->timestamp('hora_llegada')->nullable();
                $table->timestamp('hora_entrega')->nullable();
                $table->decimal('latitud', 10, 7)->nullable();
                $table->decimal('longitud', 10, 7)->nullable();
                $table->decimal('distancia_km', 10, 2)->nullable();
                $table->string('nombre_receptor', 200)->nullable();
                $table->string('cargo_receptor', 100)->nullable();
                $table->string('dni_receptor', 50)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
                
                $table->index('ruta_entrega_id');
                $table->index('envio_id');
                $table->index('estado');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruta_paradas');
    }
};

