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
        'vehiculo_id', // El transportista se obtiene a través del vehículo (vehiculo.transportista_id)
        'fecha_asignacion',
        'fecha_aceptacion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_aceptacion' => 'datetime',
    ];

    public function envio()
    {
        return $this->belongsTo(Envio::class);
    }

    // Obtener transportista a través del vehículo (rompe el cuadrado de conexiones)
    public function transportista()
    {
        return $this->hasOneThrough(
            User::class,
            Vehiculo::class,
            'id', // FK en vehiculos
            'id', // FK en users
            'vehiculo_id', // Local key en envio_asignaciones
            'transportista_id' // Local key en vehiculos
        );
    }

    // Helper para obtener el transportista directamente
    public function getTransportistaAttribute()
    {
        return $this->vehiculo?->transportista;
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}

