<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('envio_productos', function (Blueprint $table) {
            // Medidas estimadas del producto individual (opcional)
            $table->decimal('alto_producto_cm', 8, 2)->nullable()->after('volumen_unitario');
            $table->decimal('ancho_producto_cm', 8, 2)->nullable()->after('alto_producto_cm');
            $table->decimal('largo_producto_cm', 8, 2)->nullable()->after('ancho_producto_cm');
        });
    }

    public function down()
    {
        Schema::table('envio_productos', function (Blueprint $table) {
            $table->dropColumn(['alto_producto_cm', 'ancho_producto_cm', 'largo_producto_cm']);
        });
    }
};
