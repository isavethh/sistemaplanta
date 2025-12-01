<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envio_asignaciones', function (Blueprint $table) {
            $table->timestamp('fecha_aceptacion')->nullable()->after('fecha_asignacion');
        });
    }

    public function down(): void
    {
        Schema::table('envio_asignaciones', function (Blueprint $table) {
            $table->dropColumn('fecha_aceptacion');
        });
    }
};

