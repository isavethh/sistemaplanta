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
        if (!Schema::hasTable('incidentes')) {
            Schema::create('incidentes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('envio_id')->constrained('envios')->onDelete('cascade');
                $table->foreignId('transportista_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('tipo_incidente')->comment('Tipo de incidente reportado');
                $table->text('descripcion')->comment('Descripción detallada del incidente');
                $table->string('foto_url')->nullable()->comment('URL de la foto del incidente');
                $table->enum('accion', ['cancelar', 'continuar'])->comment('Acción tomada: cancelar envío o continuar con incidente');
                $table->enum('estado', ['pendiente', 'en_proceso', 'resuelto'])->default('pendiente')->comment('Estado del incidente');
                $table->decimal('ubicacion_lat', 10, 8)->nullable()->comment('Latitud donde ocurrió el incidente');
                $table->decimal('ubicacion_lng', 11, 8)->nullable()->comment('Longitud donde ocurrió el incidente');
                $table->boolean('notificado_admin')->default(false)->comment('Si se notificó al admin de plantaCruds');
                $table->boolean('notificado_almacen')->default(false)->comment('Si se notificó al almacén destino');
                $table->timestamp('fecha_reporte')->useCurrent()->comment('Fecha en que se reportó el incidente');
                $table->timestamp('fecha_resolucion')->nullable()->comment('Fecha en que se resolvió el incidente');
                $table->text('notas_resolucion')->nullable()->comment('Notas sobre la resolución del incidente');
                $table->timestamps();
                
                $table->index(['envio_id', 'estado']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};

