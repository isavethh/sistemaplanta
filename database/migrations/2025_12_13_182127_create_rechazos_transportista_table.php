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
        Schema::create('rechazos_transportista', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('envio_id');
            $table->unsignedBigInteger('transportista_id');
            $table->string('codigo_envio', 50);
            $table->text('motivo_rechazo')->nullable();
            $table->timestamp('fecha_rechazo')->useCurrent();
            $table->timestamps();
            
            $table->foreign('envio_id')->references('id')->on('envios')->onDelete('cascade');
            $table->foreign('transportista_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('transportista_id');
            $table->index('fecha_rechazo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rechazos_transportista');
    }
};
