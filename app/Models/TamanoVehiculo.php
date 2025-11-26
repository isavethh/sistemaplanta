<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TamanoVehiculo extends Model
{
    use HasFactory;

    protected $table = 'tamano_vehiculos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'capacidad_min',
        'capacidad_max',
    ];

    protected $casts = [
        'capacidad_min' => 'decimal:2',
        'capacidad_max' => 'decimal:2',
    ];

    // Relaciones
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'tamano_vehiculo_id');
    }
}
