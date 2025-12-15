<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Envio;
use App\Models\User;

class Incidente extends Model
{
    use HasFactory;

    protected $fillable = [
        'envio_id',
        'transportista_id',
        'tipo_incidente',
        'descripcion',
        'foto_url',
        'accion',
        'estado',
        'ubicacion_lat',
        'ubicacion_lng',
        'notificado_admin',
        'notificado_almacen',
        'fecha_reporte',
        'fecha_resolucion',
        'notas_resolucion',
    ];

    protected $casts = [
        'notificado_admin' => 'boolean',
        'notificado_almacen' => 'boolean',
        'fecha_reporte' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'ubicacion_lat' => 'decimal:8',
        'ubicacion_lng' => 'decimal:8',
    ];

    // Relaciones
    public function envio()
    {
        return $this->belongsTo(Envio::class);
    }

    public function transportista()
    {
        return $this->belongsTo(User::class, 'transportista_id');
    }
}
