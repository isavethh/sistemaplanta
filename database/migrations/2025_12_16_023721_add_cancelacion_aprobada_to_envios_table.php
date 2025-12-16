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
            $table->boolean('cancelacion_aprobada_almacen')->default(false)->after('observaciones')->comment('Si el almacén aprobó la cancelación');
            $table->boolean('cancelacion_aprobada_trazabilidad')->default(false)->after('cancelacion_aprobada_almacen')->comment('Si trazabilidad aprobó la cancelación');
            $table->timestamp('cancelacion_aprobada_at')->nullable()->after('cancelacion_aprobada_trazabilidad')->comment('Fecha en que se aprobó la cancelación');
            $table->string('cancelacion_pdf_path')->nullable()->after('cancelacion_aprobada_at')->comment('Ruta del PDF de cancelación');
            $table->boolean('disconformidad_almacen')->default(false)->after('cancelacion_pdf_path')->comment('Si el almacén marcó desconformidad');
            $table->boolean('disconformidad_trazabilidad')->default(false)->after('disconformidad_almacen')->comment('Si trazabilidad marcó desconformidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropColumn([
                'cancelacion_aprobada_almacen',
                'cancelacion_aprobada_trazabilidad',
                'cancelacion_aprobada_at',
                'cancelacion_pdf_path',
                'disconformidad_almacen',
                'disconformidad_trazabilidad'
            ]);
        });
    }
};
