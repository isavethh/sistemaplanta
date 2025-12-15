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
        if (!Schema::hasTable('planta')) {
            Schema::create('planta', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 255);
                $table->string('direccion', 500);
                $table->string('ciudad', 100);
                $table->string('departamento', 100);
                $table->string('pais', 100);
                $table->decimal('latitud', 10, 8);
                $table->decimal('longitud', 11, 8);
                $table->string('telefono', 20)->nullable();
                $table->string('email', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planta');
    }
};

