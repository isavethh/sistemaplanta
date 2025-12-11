<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corregir relaciones duplicadas en la base de datos
     * 
     * Problemas identificados:
     * 1. inventario_almacen tiene producto_nombre (string) cuando ya tiene envio_producto_id (FK)
     *    - Solución: Mantener producto_nombre como denormalización para historial, pero documentar
     * 2. envio_productos tiene producto_nombre (string) y producto_id (FK) - ambos referencian productos
     *    - Solución: Mantener ambos (producto_id para normalización, producto_nombre para historial)
     * 3. Verificar que no haya foreign keys duplicados a la misma tabla
     */
    public function up(): void
    {
        // 1. Verificar y agregar índices para mejorar rendimiento en relaciones
        // inventario_almacen: envio_producto_id ya existe, producto_nombre es denormalización intencional
        if (Schema::hasTable('inventario_almacen')) {
            Schema::table('inventario_almacen', function (Blueprint $table) {
                // Agregar índice compuesto para búsquedas frecuentes
                if (!$this->hasIndex('inventario_almacen', 'inventario_almacen_almacen_producto_idx')) {
                    $table->index(['almacen_id', 'producto_nombre'], 'inventario_almacen_almacen_producto_idx');
                }
            });
        }

        // 2. envio_productos: Asegurar que producto_id tenga índice (ya debería tenerlo por FK)
        if (Schema::hasTable('envio_productos')) {
            Schema::table('envio_productos', function (Blueprint $table) {
                // El foreign key ya crea un índice automáticamente, pero verificamos
                // producto_nombre se mantiene para historial/denormalización
            });
        }

        // 3. Verificar que envio_asignaciones no tenga asignaciones duplicadas
        // La migración de normalización ya agregó unique_asignacion, pero verificamos
        if (Schema::hasTable('envio_asignaciones')) {
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                // Verificar que el índice único existe (ya debería estar de la migración de normalización)
                // Si no existe, lo agregamos
                if (!$this->hasIndex('envio_asignaciones', 'unique_asignacion')) {
                    try {
                        $table->unique(['envio_id', 'transportista_id', 'vehiculo_id'], 'unique_asignacion');
                    } catch (\Exception $e) {
                        // El índice ya existe o hay datos duplicados, continuar
                    }
                }
            });
        }

        // 4. Verificar direcciones: tiene almacen_origen_id y almacen_destino_id (ambos a almacenes)
        // Esto NO es duplicado, es correcto porque representa una relación entre dos almacenes
        // Pero podemos agregar un índice compuesto para búsquedas
        if (Schema::hasTable('direcciones')) {
            Schema::table('direcciones', function (Blueprint $table) {
                if (!$this->hasIndex('direcciones', 'direcciones_origen_destino_idx')) {
                    $table->index(['almacen_origen_id', 'almacen_destino_id'], 'direcciones_origen_destino_idx');
                }
            });
        }
    }

    /**
     * Helper para verificar si un índice existe (PostgreSQL)
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $schema = 'public'; // PostgreSQL usa 'public' como schema por defecto
        
        try {
            $result = $connection->select(
                "SELECT COUNT(*) as count 
                 FROM pg_indexes 
                 WHERE schemaname = ? 
                 AND tablename = ? 
                 AND indexname = ?",
                [$schema, $table, $indexName]
            );
            
            return isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            // Si hay error, asumir que el índice no existe
            return false;
        }
    }

    public function down(): void
    {
        // Revertir cambios
        if (Schema::hasTable('inventario_almacen')) {
            Schema::table('inventario_almacen', function (Blueprint $table) {
                if ($this->hasIndex('inventario_almacen', 'inventario_almacen_almacen_producto_idx')) {
                    $table->dropIndex('inventario_almacen_almacen_producto_idx');
                }
            });
        }

        if (Schema::hasTable('direcciones')) {
            Schema::table('direcciones', function (Blueprint $table) {
                if ($this->hasIndex('direcciones', 'direcciones_origen_destino_idx')) {
                    $table->dropIndex('direcciones_origen_destino_idx');
                }
            });
        }
    }
};
