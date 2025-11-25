<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa')->unique();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->integer('anio')->nullable();
            $table->string('tipo_vehiculo')->nullable()->comment('Camión, Camioneta, Auto, etc');
            $table->foreignId('tipo_transporte_id')->nullable()->constrained('tipos_transporte')->nullOnDelete();
            $table->string('licencia_requerida')->default('B')->comment('A, B o C');
            $table->decimal('capacidad_carga', 10, 2)->nullable()->comment('Capacidad de peso');
            $table->foreignId('unidad_medida_carga_id')->nullable()->constrained('unidades_medida')->nullOnDelete();
            $table->decimal('capacidad_volumen', 10, 2)->nullable()->comment('En m3');
            $table->foreignId('transportista_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('disponible')->default(true);
            $table->string('estado')->default('activo')->comment('activo, mantenimiento, inactivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
