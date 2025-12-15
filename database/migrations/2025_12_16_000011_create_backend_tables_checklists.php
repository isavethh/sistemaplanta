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
        if (!Schema::hasTable('checklists')) {
            Schema::create('checklists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ruta_parada_id')->nullable()->constrained('ruta_paradas')->cascadeOnDelete();
                $table->foreignId('envio_id')->nullable()->constrained('envios')->cascadeOnDelete();
                $table->string('tipo', 50);
                $table->jsonb('datos')->default('{}');
                $table->text('firma_base64')->nullable();
                $table->boolean('completado')->default(false);
                $table->timestamp('completado_at')->nullable();
                $table->string('completado_por', 200)->nullable();
                $table->timestamps();
                
                $table->index('ruta_parada_id');
                $table->index('tipo');
                $table->index('envio_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
};

