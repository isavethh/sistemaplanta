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
        if (!Schema::hasTable('checklist_condiciones')) {
            Schema::create('checklist_condiciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('envio_id')->constrained('envios')->cascadeOnDelete();
                $table->foreignId('almacen_id')->nullable()->constrained('almacenes')->nullOnDelete();
                $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->string('estado_general', 50)->nullable();
                $table->boolean('productos_completos')->nullable();
                $table->boolean('empaque_intacto')->nullable();
                $table->boolean('temperatura_adecuada')->nullable();
                $table->boolean('sin_danos_visibles')->nullable();
                $table->boolean('documentacion_completa')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamp('fecha_revision')->useCurrent();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_condiciones');
    }
};

