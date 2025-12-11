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
        Schema::create('historial_envio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_id')->constrained('envios')->onDelete('cascade');
            $table->string('evento', 50); // 'creado', 'asignado', 'aceptado', 'en_transito', 'entregado', 'incidente', 'cancelado'
            $table->text('descripcion')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_hora');
            $table->jsonb('datos_extra')->nullable(); // Para lat, lng, IP, etc
            $table->timestamps();
            
            $table->index(['envio_id', 'fecha_hora']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_envio');
    }
};
