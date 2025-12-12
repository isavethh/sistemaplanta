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
        Schema::table('incidentes', function (Blueprint $table) {
            if (!Schema::hasColumn('incidentes', 'solicitar_ayuda')) {
                $table->boolean('solicitar_ayuda')->default(false)->comment('Indica si el transportista solicita ayuda del administrador');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidentes', function (Blueprint $table) {
            if (Schema::hasColumn('incidentes', 'solicitar_ayuda')) {
                $table->dropColumn('solicitar_ayuda');
            }
        });
    }
};
