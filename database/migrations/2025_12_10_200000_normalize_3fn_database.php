<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalizar base de datos según Tercera Forma Normal (3FN)
     * 
     * Cambios:
     * 1. Agregar foreign key de producto_id en envio_productos (en lugar de solo producto_nombre)
     * 2. Asegurar que todas las relaciones estén correctamente definidas
     * 3. Eliminar redundancias y dependencias transitivas
     */
    public function up(): void
    {
        // 1. Agregar producto_id a envio_productos si no existe
        // Esto permite normalizar la relación con productos
        if (!Schema::hasColumn('envio_productos', 'producto_id')) {
            Schema::table('envio_productos', function (Blueprint $table) {
                $table->foreignId('producto_id')->nullable()->after('envio_id')->constrained('productos')->nullOnDelete();
            });
        }

        // 2. Agregar índices para mejorar rendimiento
        Schema::table('envios', function (Blueprint $table) {
            if (!Schema::hasColumn('envios', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('almacen_destino_id')->constrained('users')->nullOnDelete()->comment('Usuario que creó el envío');
            }
        });

        // 3. Normalizar envio_asignaciones - asegurar que tenga índice único para evitar duplicados
        Schema::table('envio_asignaciones', function (Blueprint $table) {
            // Agregar índice único para evitar asignaciones duplicadas
            $table->unique(['envio_id', 'transportista_id', 'vehiculo_id'], 'unique_asignacion');
        });

        // 4. Asegurar que tipos_empaque tenga todas las columnas necesarias
        if (!Schema::hasColumn('tipos_empaque', 'largo_cm')) {
            Schema::table('tipos_empaque', function (Blueprint $table) {
                $table->decimal('largo_cm', 10, 2)->nullable()->after('nombre');
                $table->decimal('ancho_cm', 10, 2)->nullable()->after('largo_cm');
                $table->decimal('alto_cm', 10, 2)->nullable()->after('ancho_cm');
                $table->decimal('peso_maximo_kg', 10, 2)->nullable()->after('alto_cm');
                $table->string('icono')->nullable()->after('peso_maximo_kg')->default('📦');
            });
        }

        // 5. Asegurar que envio_productos tenga campos de dimensiones si no existen
        if (!Schema::hasColumn('envio_productos', 'alto_producto_cm')) {
            Schema::table('envio_productos', function (Blueprint $table) {
                $table->decimal('alto_producto_cm', 10, 2)->nullable()->after('total_precio');
                $table->decimal('ancho_producto_cm', 10, 2)->nullable()->after('alto_producto_cm');
                $table->decimal('largo_producto_cm', 10, 2)->nullable()->after('ancho_producto_cm');
            });
        }
    }

    public function down(): void
    {
        // Revertir cambios
        Schema::table('envio_productos', function (Blueprint $table) {
            if (Schema::hasColumn('envio_productos', 'producto_id')) {
                $table->dropForeign(['producto_id']);
                $table->dropColumn('producto_id');
            }
            if (Schema::hasColumn('envio_productos', 'alto_producto_cm')) {
                $table->dropColumn(['alto_producto_cm', 'ancho_producto_cm', 'largo_producto_cm']);
            }
        });

        Schema::table('envios', function (Blueprint $table) {
            if (Schema::hasColumn('envios', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('envio_asignaciones', function (Blueprint $table) {
            $table->dropUnique('unique_asignacion');
        });

        Schema::table('tipos_empaque', function (Blueprint $table) {
            if (Schema::hasColumn('tipos_empaque', 'largo_cm')) {
                $table->dropColumn(['largo_cm', 'ancho_cm', 'alto_cm', 'peso_maximo_kg', 'icono']);
            }
        });
    }
};

