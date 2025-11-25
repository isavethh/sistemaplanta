<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'codigo',
        'nombre',
        'descripcion',
        'peso_unitario',
        'volumen_unitario',
        'precio_base',
        'stock_minimo',
        'activo',
    ];

    protected $casts = [
        'peso_unitario' => 'decimal:3',
        'volumen_unitario' => 'decimal:3',
        'precio_base' => 'decimal:2',
        'stock_minimo' => 'integer',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    // Helpers
    public function getStockTotal()
    {
        return InventarioAlmacen::where('producto_nombre', $this->nombre)->sum('cantidad');
    }
}
