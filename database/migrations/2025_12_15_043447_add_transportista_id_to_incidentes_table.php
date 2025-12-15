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
            // Agregar transportista_id si no existe
            if (!Schema::hasColumn('incidentes', 'transportista_id')) {
                $table->foreignId('transportista_id')->nullable()->after('envio_id')->constrained('users')->onDelete('set null');
            }
            
            // Agregar accion si no existe
            if (!Schema::hasColumn('incidentes', 'accion')) {
                // Agregar como nullable
                $table->enum('accion', ['cancelar', 'continuar'])->nullable()->after('foto_url')->comment('Acción tomada: cancelar envío o continuar con incidente');
            }
            
            // Agregar ubicacion_lat si no existe
            if (!Schema::hasColumn('incidentes', 'ubicacion_lat')) {
                $table->decimal('ubicacion_lat', 10, 8)->nullable()->after('estado')->comment('Latitud donde ocurrió el incidente');
            }
            
            // Agregar ubicacion_lng si no existe
            if (!Schema::hasColumn('incidentes', 'ubicacion_lng')) {
                $table->decimal('ubicacion_lng', 11, 8)->nullable()->after('ubicacion_lat')->comment('Longitud donde ocurrió el incidente');
            }
            
            // Agregar notificado_admin si no existe
            if (!Schema::hasColumn('incidentes', 'notificado_admin')) {
                $table->boolean('notificado_admin')->default(false)->after('ubicacion_lng')->comment('Si se notificó al admin de plantaCruds');
            }
            
            // Agregar notificado_almacen si no existe
            if (!Schema::hasColumn('incidentes', 'notificado_almacen')) {
                $table->boolean('notificado_almacen')->default(false)->after('notificado_admin')->comment('Si se notificó al almacén destino');
            }
        });
        
        // Actualizar registros existentes con valores por defecto
        if (Schema::hasColumn('incidentes', 'accion')) {
            \DB::table('incidentes')->whereNull('accion')->update(['accion' => 'continuar']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidentes', function (Blueprint $table) {
            if (Schema::hasColumn('incidentes', 'notificado_almacen')) {
                $table->dropColumn('notificado_almacen');
            }
            if (Schema::hasColumn('incidentes', 'notificado_admin')) {
                $table->dropColumn('notificado_admin');
            }
            if (Schema::hasColumn('incidentes', 'ubicacion_lng')) {
                $table->dropColumn('ubicacion_lng');
            }
            if (Schema::hasColumn('incidentes', 'ubicacion_lat')) {
                $table->dropColumn('ubicacion_lat');
            }
            if (Schema::hasColumn('incidentes', 'accion')) {
                $table->dropColumn('accion');
            }
            if (Schema::hasColumn('incidentes', 'transportista_id')) {
                $table->dropForeign(['transportista_id']);
                $table->dropColumn('transportista_id');
            }
        });
    }
};
