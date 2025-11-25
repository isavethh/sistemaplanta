<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envio_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_id')->constrained('envios')->onDelete('cascade');
            $table->string('producto_nombre');
            $table->integer('cantidad')->default(1);
            $table->decimal('peso_unitario', 12, 3)->default(0);
            $table->foreignId('unidad_medida_id')->nullable()->constrained('unidades_medida')->nullOnDelete();
            $table->foreignId('tipo_empaque_id')->nullable()->constrained('tipos_empaque')->nullOnDelete();
            $table->decimal('precio_unitario', 12, 2)->default(0)->comment('En Bolivianos');
            $table->decimal('total_peso', 12, 3)->default(0);
            $table->decimal('total_precio', 12, 2)->default(0)->comment('En Bolivianos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envio_productos');
    }
};
