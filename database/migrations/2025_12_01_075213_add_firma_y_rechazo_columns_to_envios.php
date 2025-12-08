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
            $table->text('firma_transportista')->nullable()->after('observaciones');
            $table->timestamp('fecha_rechazo')->nullable()->after('fecha_entrega');
            $table->text('motivo_rechazo')->nullable()->after('fecha_rechazo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropColumn(['firma_transportista', 'fecha_rechazo', 'motivo_rechazo']);
        });
    }
};
