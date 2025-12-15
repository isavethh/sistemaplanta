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
        if (!Schema::hasTable('evidencias_entrega')) {
            Schema::create('evidencias_entrega', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ruta_parada_id')->nullable()->constrained('ruta_paradas')->cascadeOnDelete();
                $table->foreignId('envio_id')->nullable()->constrained('envios')->cascadeOnDelete();
                $table->string('item_id', 100)->nullable();
                $table->string('tipo', 50);
                $table->string('nombre', 200)->nullable();
                $table->text('url')->nullable();
                $table->text('base64')->nullable();
                $table->timestamp('created_at')->useCurrent();
                
                $table->index('ruta_parada_id');
                $table->index('envio_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidencias_entrega');
    }
};

