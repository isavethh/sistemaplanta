<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaVenta extends Model
{
    protected $table = 'notas_venta';
    
    protected $fillable = [
        'numero_nota',
        'envio_id',
        'fecha_emision',
        'almacen_nombre',
        'almacen_direccion',
        'total_cantidad',
        'total_precio',
        'subtotal',
        'porcentaje_iva',
        'observaciones',
    ];
    
    protected $casts = [
        'fecha_emision' => 'datetime',
        'total_cantidad' => 'integer',
        'total_precio' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'porcentaje_iva' => 'decimal:2',
    ];
    
    public function envio(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Envio::class, 'envio_id');
    }
    
    /**
     * Generar número de nota de venta
     */
    public static function generarNumeroNota($envioCodigo): string
    {
        $fecha = now();
        $codigoEnvio = explode('-', $envioCodigo);
        $sufijo = end($codigoEnvio);
        
        return sprintf(
            'NV-%s%s%s-%s',
            $fecha->format('Y'),
            str_pad($fecha->format('m'), 2, '0', STR_PAD_LEFT),
            str_pad($fecha->format('d'), 2, '0', STR_PAD_LEFT),
            $sufijo
        );
    }
}
