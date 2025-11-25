<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioAsignacion extends Model
{
    use HasFactory;

    protected $table = 'envio_asignaciones';

    protected $fillable = [
        'envio_id',
        'transportista_id',
        'vehiculo_id',
        'fecha_asignacion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];

    public function envio()
    {
        return $this->belongsTo(Envio::class);
    }

    public function transportista()
    {
        return $this->belongsTo(User::class, 'transportista_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}

