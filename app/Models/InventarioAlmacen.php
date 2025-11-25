<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioAlmacen extends Model
{
    use HasFactory;

    protected $table = 'inventario_almacen';

    protected $fillable = [
        'almacen_id',
        'envio_producto_id',
        'producto_nombre',
        'descripcion',
        'cantidad',
        'peso_total',
        'volumen_total',
        'precio_unitario',
        'fecha_ingreso',
        'lote',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'peso_total' => 'decimal:3',
        'volumen_total' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'fecha_ingreso' => 'date',
    ];

    // Relaciones
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function envioProducto()
    {
        return $this->belongsTo(EnvioProducto::class);
    }

    // Helpers
    public function getValorTotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
}
