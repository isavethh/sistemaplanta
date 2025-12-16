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
        Schema::table('envios', function (Blueprint $table) {
            $table->string('propuesta_pdf_path')->nullable()->after('observaciones')->comment('Ruta del PDF de propuesta generado');
            $table->timestamp('propuesta_enviada_at')->nullable()->after('propuesta_pdf_path')->comment('Fecha en que se envió la propuesta');
            $table->timestamp('propuesta_aceptada_at')->nullable()->after('propuesta_enviada_at')->comment('Fecha en que se aceptó la propuesta');
            $table->foreignId('pedido_almacen_id')->nullable()->after('observaciones')->constrained('pedidos_almacen')->onDelete('set null')->comment('Pedido de almacén asociado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropForeign(['pedido_almacen_id']);
            $table->dropColumn(['propuesta_pdf_path', 'propuesta_enviada_at', 'propuesta_aceptada_at', 'pedido_almacen_id']);
        });
    }
};
