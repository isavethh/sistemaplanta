<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    protected $fillable = [
        'almacen_origen_id',
        'almacen_destino_id',
        'distancia_km',
        'tiempo_estimado_minutos',
        'ruta_descripcion',
    ];

    protected $casts = [
        'distancia_km' => 'decimal:2',
        'tiempo_estimado_minutos' => 'integer',
    ];

    // Relaciones
    public function almacenOrigen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    // Helpers
    public function calcularDistancia()
    {
        if (!$this->almacenOrigen || !$this->almacenDestino) return 0;
        
        $lat1 = $this->almacenOrigen->latitud;
        $lon1 = $this->almacenOrigen->longitud;
        $lat2 = $this->almacenDestino->latitud;
        $lon2 = $this->almacenDestino->longitud;
        
        // Fórmula de Haversine para calcular distancia
        $earth_radius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earth_radius * $c;
    }
}
