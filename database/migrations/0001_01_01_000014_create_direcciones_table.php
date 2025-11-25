<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_origen_id')->nullable()->constrained('almacenes')->nullOnDelete()->comment('Planta de origen');
            $table->foreignId('almacen_destino_id')->nullable()->constrained('almacenes')->nullOnDelete()->comment('Almacén destino');
            $table->decimal('distancia_km', 10, 2)->nullable()->comment('Distancia calculada');
            $table->integer('tiempo_estimado_minutos')->nullable();
            $table->text('ruta_descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};

