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
        Schema::create('tamano_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique()->comment('Pequeño, Mediano, Grande, Extra Grande');
            $table->string('descripcion')->nullable();
            $table->decimal('capacidad_min', 10, 2)->nullable()->comment('Capacidad mínima en toneladas');
            $table->decimal('capacidad_max', 10, 2)->nullable()->comment('Capacidad máxima en toneladas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tamano_vehiculos');
    }
};
