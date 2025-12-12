<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Eliminar tabla estados_vehiculo que no se está usando
     * 
     * Razones:
     * - La tabla está vacía (0 registros)
     * - No tiene foreign keys de otras tablas
     * - La tabla vehiculos usa un campo 'estado' como string, no como foreign key
     * - No hay vistas que la usen
     * - El controlador existe pero no se usa realmente
     */
    public function up(): void
    {
        if (Schema::hasTable('estados_vehiculo')) {
            Schema::dropIfExists('estados_vehiculo');
        }
    }

    public function down(): void
    {
        // Recrear tabla si es necesario revertir
        Schema::create('estados_vehiculo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });
    }
};

