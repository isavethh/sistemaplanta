<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioProducto extends Model
{
    use HasFactory;

    protected $fillable = [
        'envio_id',
        'producto_id',
        'producto_nombre',
        'cantidad',
        'peso_kg',
        'peso_unitario',
        'unidad_medida_id',
        'tipo_empaque_id',
        'precio_unitario',
        'total_peso',
        'total_precio',
        'alto_producto_cm',
        'ancho_producto_cm',
        'largo_producto_cm',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'peso_unitario' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'total_peso' => 'decimal:3',
        'total_precio' => 'decimal:2',
    ];

    // Relaciones
    public function envio()
    {
        return $this->belongsTo(Envio::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function tipoEmpaque()
    {
        return $this->belongsTo(TipoEmpaque::class);
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($producto) {
            $producto->calcularTotales();
        });

        static::updating(function ($producto) {
            $producto->calcularTotales();
        });
    }

    // Helpers
    public function calcularTotales()
    {
        $this->total_peso = $this->cantidad * $this->peso_unitario;
        $this->total_precio = $this->cantidad * $this->precio_unitario;
    }
}
