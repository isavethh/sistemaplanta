<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropuestaVehiculo extends Model
{
    use HasFactory;

    protected $table = 'propuestas_vehiculos';

    protected $fillable = [
        'envio_id',
        'codigo_envio',
        'propuesta_data',
        'estado',
        'observaciones_trazabilidad',
        'aprobado_por',
        'fecha_propuesta',
        'fecha_decision',
    ];

    protected $casts = [
        'propuesta_data' => 'array',
        'fecha_propuesta' => 'datetime',
        'fecha_decision' => 'datetime',
    ];

    /**
     * Relación con Envio
     */
    public function envio()
    {
        return $this->belongsTo(Envio::class, 'envio_id');
    }

    /**
     * Relación con Usuario (quien aprobó/rechazó)
     */
    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    /**
     * Scope para propuestas aprobadas
     */
    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    /**
     * Scope para propuestas rechazadas
     */
    public function scopeRechazadas($query)
    {
        return $query->where('estado', 'rechazada');
    }

    /**
     * Scope para propuestas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
}
