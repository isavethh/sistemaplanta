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
        if (!Schema::hasTable('detalle_envios')) {
            Schema::create('detalle_envios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('envio_id')->constrained('envios')->cascadeOnDelete();
                $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
                $table->decimal('cantidad', 10, 2);
                $table->decimal('peso_por_unidad', 10, 3)->nullable();
                $table->decimal('precio_por_unidad', 10, 2)->nullable();
                $table->decimal('subtotal', 12, 2)->nullable();
                $table->decimal('peso_total', 10, 3)->nullable();
                $table->foreignId('tipo_empaque_id')->nullable()->constrained('tipos_empaque')->nullOnDelete();
                $table->foreignId('unidad_medida_id')->nullable()->constrained('unidades_medida')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_envios');
    }
};

