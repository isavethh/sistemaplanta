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
        if (!Schema::hasTable('notas_venta')) {
            Schema::create('notas_venta', function (Blueprint $table) {
                $table->id();
                $table->string('numero_nota', 50)->unique();
                $table->foreignId('envio_id')->nullable()->constrained('envios')->nullOnDelete();
                $table->timestamp('fecha_emision')->useCurrent();
                $table->string('almacen_nombre', 255)->nullable();
                $table->text('almacen_direccion')->nullable();
                $table->integer('total_cantidad')->nullable();
                $table->decimal('total_precio', 10, 2)->nullable();
                $table->decimal('subtotal', 10, 2)->nullable();
                $table->decimal('porcentaje_iva', 5, 2)->default(13);
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        } else {
            // Si la tabla existe, agregar columnas que puedan faltar
            Schema::table('notas_venta', function (Blueprint $table) {
                if (!Schema::hasColumn('notas_venta', 'subtotal')) {
                    $table->decimal('subtotal', 10, 2)->nullable()->after('total_precio');
                }
                if (!Schema::hasColumn('notas_venta', 'porcentaje_iva')) {
                    $table->decimal('porcentaje_iva', 5, 2)->default(13)->after('subtotal');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_venta');
    }
};
