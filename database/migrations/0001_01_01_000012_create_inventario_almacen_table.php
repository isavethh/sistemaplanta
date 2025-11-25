<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_almacen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade');
            $table->foreignId('envio_producto_id')->nullable()->constrained('envio_productos')->nullOnDelete();
            $table->string('producto_nombre');
            $table->text('descripcion')->nullable();
            $table->integer('cantidad')->default(0);
            $table->decimal('peso_total', 12, 3)->default(0);
            $table->decimal('volumen_total', 12, 3)->default(0);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->date('fecha_ingreso')->default(now());
            $table->string('lote')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_almacen');
    }
};
