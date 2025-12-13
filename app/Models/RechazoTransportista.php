<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechazoTransportista extends Model
{
    use HasFactory;

    protected $table = 'rechazos_transportista';

    protected $fillable = [
        'envio_id',
        'transportista_id',
        'codigo_envio',
        'motivo_rechazo',
        'fecha_rechazo',
    ];

    protected $casts = [
        'fecha_rechazo' => 'datetime',
    ];

    /**
     * Relación con Envio
     */
    public function envio()
    {
        return $this->belongsTo(Envio::class, 'envio_id');
    }

    /**
     * Relación con Transportista (User)
     */
    public function transportista()
    {
        return $this->belongsTo(User::class, 'transportista_id');
    }
}
