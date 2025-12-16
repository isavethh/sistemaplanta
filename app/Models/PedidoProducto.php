<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoProducto extends Model
{
    use HasFactory;

    protected $table = 'pedido_productos';

    protected $fillable = [
        'pedido_almacen_id',
        'producto_nombre',
        'producto_codigo',
        'cantidad',
        'peso_unitario',
        'precio_unitario',
        'total_peso',
        'total_precio',
        'observaciones',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'peso_unitario' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'total_peso' => 'decimal:3',
        'total_precio' => 'decimal:2',
    ];

    // Relaciones
    public function pedidoAlmacen()
    {
        return $this->belongsTo(PedidoAlmacen::class, 'pedido_almacen_id');
    }

    // Helpers
    public function calcularTotales()
    {
        $this->total_peso = $this->cantidad * $this->peso_unitario;
        $this->total_precio = $this->cantidad * $this->precio_unitario;
        $this->save();
    }
}
