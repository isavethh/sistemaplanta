<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('almacen_destino_id')->constrained('almacenes')->onDelete('cascade')->comment('Almacén de destino');
            $table->string('categoria')->default('Verduras')->comment('Verduras o Frutas');
            $table->date('fecha_creacion')->default(now());
            $table->date('fecha_estimada_entrega')->nullable();
            $table->time('hora_estimada')->nullable();
            $table->string('estado')->default('pendiente')->comment('pendiente, asignado, en_transito, entregado, cancelado');
            $table->integer('total_cantidad')->default(0);
            $table->decimal('total_peso', 12, 3)->default(0)->comment('En la unidad de medida seleccionada');
            $table->decimal('total_precio', 12, 2)->default(0)->comment('En Bolivianos');
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_inicio_transito')->nullable();
            $table->timestamp('fecha_entrega')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
