<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoTransporte extends Model
{
    use HasFactory;

    protected $table = 'tipos_transporte';

    protected $fillable = [
        'nombre',
        'descripcion',
        'requiere_temperatura_controlada',
        'temperatura_minima',
        'temperatura_maxima',
        'activo',
    ];

    protected $casts = [
        'requiere_temperatura_controlada' => 'boolean',
        'temperatura_minima' => 'decimal:2',
        'temperatura_maxima' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'tipo_transporte_id');
    }
}

