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
        Schema::create('pedido_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_almacen_id')->constrained('pedidos_almacen')->onDelete('cascade');
            $table->string('producto_nombre')->comment('Nombre del producto desde API Trazabilidad');
            $table->string('producto_codigo')->nullable()->comment('Código del producto desde API Trazabilidad');
            $table->integer('cantidad')->comment('Cantidad solicitada');
            $table->decimal('peso_unitario', 10, 3)->default(0)->comment('Peso por unidad en kg');
            $table->decimal('precio_unitario', 10, 2)->default(0)->comment('Precio por unidad');
            $table->decimal('total_peso', 12, 3)->default(0)->comment('Peso total del producto');
            $table->decimal('total_precio', 12, 2)->default(0)->comment('Precio total del producto');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_productos');
    }
};
