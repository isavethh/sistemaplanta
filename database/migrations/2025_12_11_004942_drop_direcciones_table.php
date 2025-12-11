<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Eliminar tabla direcciones que tiene doble conexión innecesaria con almacenes
     * 
     * La tabla direcciones almacena rutas entre almacenes, pero:
     * - No se usa en la lógica de envíos (envios usa directamente almacen_destino_id)
     * - Las rutas se calculan dinámicamente usando coordenadas
     * - Tiene doble conexión (almacen_origen_id y almacen_destino_id) que es redundante
     */
    public function up(): void
    {
        // Eliminar foreign keys primero
        if (Schema::hasTable('direcciones')) {
            Schema::table('direcciones', function (Blueprint $table) {
                $table->dropForeign(['almacen_origen_id']);
                $table->dropForeign(['almacen_destino_id']);
            });
            
            // Eliminar la tabla
            Schema::dropIfExists('direcciones');
        }
    }

    public function down(): void
    {
        // Recrear tabla si es necesario revertir
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
};
