<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('usuario_almacen_id')->nullable()->constrained('users')->nullOnDelete()->comment('Usuario que gestiona el almacén');
            $table->decimal('latitud', 10, 7)->nullable()->comment('Ubicación en mapa');
            $table->decimal('longitud', 10, 7)->nullable()->comment('Ubicación en mapa');
            $table->text('direccion_completa')->nullable();
            $table->boolean('es_planta')->default(false)->comment('Si es la planta principal');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almacenes');
    }
};
