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
        if (!Schema::hasTable('rutas_entrega')) {
            Schema::create('rutas_entrega', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 50)->unique();
                $table->foreignId('transportista_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos')->nullOnDelete();
                $table->date('fecha')->default(now());
                $table->string('estado', 50)->default('pendiente');
                $table->timestamp('hora_salida')->nullable();
                $table->timestamp('hora_fin')->nullable();
                $table->decimal('km_recorridos', 10, 2)->default(0);
                $table->integer('total_envios')->default(0);
                $table->decimal('total_peso', 10, 2)->default(0);
                $table->text('observaciones')->nullable();
                $table->decimal('ultima_latitud', 10, 8)->nullable();
                $table->decimal('ultima_longitud', 11, 8)->nullable();
                $table->timestamp('ultima_actualizacion')->nullable();
                $table->timestamps();
                
                $table->index('transportista_id');
                $table->index('fecha');
                $table->index('estado');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas_entrega');
    }
};

