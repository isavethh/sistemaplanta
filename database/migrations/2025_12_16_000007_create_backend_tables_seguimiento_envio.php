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
        if (!Schema::hasTable('seguimiento_envio')) {
            Schema::create('seguimiento_envio', function (Blueprint $table) {
                $table->id();
                $table->foreignId('envio_id')->constrained('envios')->cascadeOnDelete();
                $table->decimal('latitud', 10, 8);
                $table->decimal('longitud', 11, 8);
                $table->decimal('velocidad', 5, 2)->nullable();
                $table->timestamp('timestamp')->useCurrent();
                
                $table->index(['envio_id', 'timestamp']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento_envio');
    }
};

