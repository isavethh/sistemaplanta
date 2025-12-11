<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tipos_empaque', function (Blueprint $table) {
            // Medidas del empaque (en cm)
            $table->decimal('largo_cm', 8, 2)->nullable()->after('nombre');
            $table->decimal('ancho_cm', 8, 2)->nullable()->after('largo_cm');
            $table->decimal('alto_cm', 8, 2)->nullable()->after('ancho_cm');
            
            // Capacidad de peso (en kg)
            $table->decimal('peso_maximo_kg', 8, 2)->nullable()->after('alto_cm');
            
            // Volumen calculado (en cm³)
            $table->decimal('volumen_cm3', 12, 2)->nullable()->after('peso_maximo_kg');
            
            // Icono o imagen
            $table->string('icono')->nullable()->after('volumen_cm3');
        });
    }

    public function down()
    {
        Schema::table('tipos_empaque', function (Blueprint $table) {
            $table->dropColumn([
                'largo_cm',
                'ancho_cm',
                'alto_cm',
                'peso_maximo_kg',
                'volumen_cm3',
                'icono'
            ]);
        });
    }
};
