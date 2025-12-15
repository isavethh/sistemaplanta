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
        if (!Schema::hasTable('transportistas')) {
            Schema::create('transportistas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuarios')->cascadeOnDelete();
                $table->string('licencia', 50)->nullable();
                $table->string('tipo_licencia', 20)->nullable();
                $table->date('fecha_vencimiento_licencia')->nullable();
                $table->foreignId('vehiculo_asignado_id')->nullable()->constrained('vehiculos')->nullOnDelete();
                $table->boolean('disponible')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportistas');
    }
};

