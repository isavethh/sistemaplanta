<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalizar base de datos a 3FN y corregir relaciones duplicadas
     * 
     * Problemas identificados:
     * 1. envio_productos tiene producto_nombre (string) pero debería tener producto_id (FK)
     * 2. inventario_almacen tiene producto_nombre redundante cuando ya tiene envio_producto_id
     * 3. envios no tiene created_by (usuario que creó el envío)
     * 4. Falta índice único en envio_asignaciones para evitar duplicados
     * 5. Relaciones duplicadas entre tablas
     */
    public function up(): void
    {
        // 1. Agregar producto_id a envio_productos si no existe (normalización)
        if (Schema::hasTable('envio_productos')) {
            Schema::table('envio_productos', function (Blueprint $table) {
                if (!Schema::hasColumn('envio_productos', 'producto_id')) {
                    $table->foreignId('producto_id')->nullable()->after('envio_id')
                        ->constrained('productos')->nullOnDelete()
                        ->comment('FK a productos (normalización 3FN)');
                }
            });
        }

        // 2. Agregar created_by a envios si no existe
        if (Schema::hasTable('envios')) {
            Schema::table('envios', function (Blueprint $table) {
                if (!Schema::hasColumn('envios', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('almacen_destino_id')
                        ->constrained('users')->nullOnDelete()
                        ->comment('Usuario que creó el envío');
                }
            });
        }

        // 3. Asegurar índice único en envio_asignaciones para evitar duplicados
        if (Schema::hasTable('envio_asignaciones')) {
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                // Verificar si el índice único ya existe
                $indexExists = $this->indexExists('envio_asignaciones', 'unique_envio_asignacion');
                
                if (!$indexExists) {
                    try {
                        // Intentar crear índice único compuesto
                        // Nota: envio_id debe ser único (un envío solo puede tener una asignación activa)
                        $table->unique('envio_id', 'unique_envio_asignacion');
                    } catch (\Exception $e) {
                        // Si hay datos duplicados, primero limpiarlos
                        DB::statement('
                            DELETE FROM envio_asignaciones a1
                            USING envio_asignaciones a2
                            WHERE a1.id < a2.id 
                            AND a1.envio_id = a2.envio_id
                        ');
                        // Luego crear el índice
                        $table->unique('envio_id', 'unique_envio_asignacion');
                    }
                }
            });
        }

        // 4. Agregar índices para mejorar rendimiento en relaciones frecuentes
        if (Schema::hasTable('envio_productos')) {
            Schema::table('envio_productos', function (Blueprint $table) {
                if (!$this->indexExists('envio_productos', 'idx_envio_productos_producto_id')) {
                    $table->index('producto_id', 'idx_envio_productos_producto_id');
                }
            });
        }

        if (Schema::hasTable('inventario_almacen')) {
            Schema::table('inventario_almacen', function (Blueprint $table) {
                // Índice compuesto para búsquedas por almacén y producto
                if (!$this->indexExists('inventario_almacen', 'idx_inventario_almacen_producto')) {
                    $table->index(['almacen_id', 'envio_producto_id'], 'idx_inventario_almacen_producto');
                }
            });
        }

        if (Schema::hasTable('envios')) {
            Schema::table('envios', function (Blueprint $table) {
                if (!$this->indexExists('envios', 'idx_envios_created_by')) {
                    $table->index('created_by', 'idx_envios_created_by');
                }
                if (!$this->indexExists('envios', 'idx_envios_ruta_entrega_id')) {
                    $table->index('ruta_entrega_id', 'idx_envios_ruta_entrega_id');
                }
            });
        }

        // 5. Normalizar direcciones: verificar que no haya relaciones duplicadas
        // (almacen_origen_id y almacen_destino_id son correctos, no son duplicados)
        if (Schema::hasTable('direcciones')) {
            Schema::table('direcciones', function (Blueprint $table) {
                // Índice único para evitar rutas duplicadas entre los mismos almacenes
                if (!$this->indexExists('direcciones', 'unique_direccion_origen_destino')) {
                    try {
                        $table->unique(['almacen_origen_id', 'almacen_destino_id'], 'unique_direccion_origen_destino');
                    } catch (\Exception $e) {
                        // Si hay duplicados, limpiarlos primero
                        DB::statement('
                            DELETE FROM direcciones d1
                            USING direcciones d2
                            WHERE d1.id < d2.id 
                            AND d1.almacen_origen_id = d2.almacen_origen_id
                            AND d1.almacen_destino_id = d2.almacen_destino_id
                        ');
                        $table->unique(['almacen_origen_id', 'almacen_destino_id'], 'unique_direccion_origen_destino');
                    }
                }
            });
        }

        // 6. ruta_entrega_id ya debería estar agregado por la migración anterior
        // Solo verificamos que exista el índice
        if (Schema::hasTable('envios') && Schema::hasColumn('envios', 'ruta_entrega_id')) {
            Schema::table('envios', function (Blueprint $table) {
                if (!$this->indexExists('envios', 'envios_ruta_entrega_id_index') && 
                    !$this->indexExists('envios', 'idx_envios_ruta_entrega_id')) {
                    $table->index('ruta_entrega_id', 'idx_envios_ruta_entrega_id');
                }
            });
        }
    }

    /**
     * Helper para verificar si un índice existe (PostgreSQL)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $result = DB::select(
                "SELECT COUNT(*) as count 
                 FROM pg_indexes 
                 WHERE schemaname = 'public' 
                 AND tablename = ? 
                 AND indexname = ?",
                [$table, $indexName]
            );
            
            return isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function down(): void
    {
        // Revertir cambios (solo índices, no columnas para evitar pérdida de datos)
        if (Schema::hasTable('envio_asignaciones')) {
            Schema::table('envio_asignaciones', function (Blueprint $table) {
                if ($this->indexExists('envio_asignaciones', 'unique_envio_asignacion')) {
                    $table->dropUnique('unique_envio_asignacion');
                }
            });
        }

        if (Schema::hasTable('direcciones')) {
            Schema::table('direcciones', function (Blueprint $table) {
                if ($this->indexExists('direcciones', 'unique_direccion_origen_destino')) {
                    $table->dropUnique('unique_direccion_origen_destino');
                }
            });
        }

        if (Schema::hasTable('envio_productos')) {
            Schema::table('envio_productos', function (Blueprint $table) {
                if ($this->indexExists('envio_productos', 'idx_envio_productos_producto_id')) {
                    $table->dropIndex('idx_envio_productos_producto_id');
                }
            });
        }

        if (Schema::hasTable('inventario_almacen')) {
            Schema::table('inventario_almacen', function (Blueprint $table) {
                if ($this->indexExists('inventario_almacen', 'idx_inventario_almacen_producto')) {
                    $table->dropIndex('idx_inventario_almacen_producto');
                }
            });
        }

        if (Schema::hasTable('envios')) {
            Schema::table('envios', function (Blueprint $table) {
                if ($this->indexExists('envios', 'idx_envios_created_by')) {
                    $table->dropIndex('idx_envios_created_by');
                }
                if ($this->indexExists('envios', 'idx_envios_ruta_entrega_id')) {
                    $table->dropIndex('idx_envios_ruta_entrega_id');
                }
            });
        }
    }
};
