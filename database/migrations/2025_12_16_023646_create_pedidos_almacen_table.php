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
        Schema::create('pedidos_almacen', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código único del pedido');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade')->comment('Almacén que hace el pedido');
            $table->foreignId('usuario_propietario_id')->constrained('users')->onDelete('cascade')->comment('Propietario que crea el pedido');
            $table->date('fecha_requerida')->comment('Fecha en que se requiere el envío');
            $table->time('hora_requerida')->nullable()->comment('Hora en que se requiere el envío');
            $table->string('estado')->default('pendiente')->comment('pendiente, enviado_trazabilidad, aceptado_trazabilidad, propuesta_enviada, propuesta_aceptada, cancelado, entregado');
            $table->decimal('latitud', 10, 7)->default(-17.8146)->comment('Latitud del almacén (Santa Cruz, Bolivia)');
            $table->decimal('longitud', 10, 7)->default(-63.1561)->comment('Longitud del almacén (Santa Cruz, Bolivia)');
            $table->string('direccion_completa')->nullable()->comment('Dirección completa del almacén');
            $table->foreignId('envio_id')->nullable()->constrained('envios')->onDelete('set null')->comment('Envío asociado cuando se acepta la propuesta');
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_envio_trazabilidad')->nullable()->comment('Fecha en que se envió a trazabilidad');
            $table->timestamp('fecha_aceptacion_trazabilidad')->nullable()->comment('Fecha en que trazabilidad aceptó el pedido');
            $table->timestamp('fecha_propuesta_enviada')->nullable()->comment('Fecha en que se envió la propuesta');
            $table->timestamp('fecha_propuesta_aceptada')->nullable()->comment('Fecha en que se aceptó la propuesta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_almacen');
    }
};
