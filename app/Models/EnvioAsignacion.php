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
        // transportista_id eliminado para romper triangulación (se obtiene a través de vehiculo_id → vehiculos.transportista_id)
        'vehiculo_id',
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

    // Obtener transportista a través de vehiculo (ya no hay transportista_id directo)
    public function getTransportistaAttribute()
    {
        return $this->vehiculo ? $this->vehiculo->transportista : null;
    }
    
    // Relación para compatibilidad con código existente (obtiene transportista a través de vehiculo)
    public function transportista()
    {
        // Usar hasOneThrough para obtener el transportista a través del vehículo
        return $this->hasOneThrough(
            User::class,
            Vehiculo::class,
            'id', // Foreign key en vehiculos
            'id', // Foreign key en users
            'vehiculo_id', // Local key en envio_asignaciones
            'transportista_id' // Local key en vehiculos
        );
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}

