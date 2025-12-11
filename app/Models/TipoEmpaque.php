<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoEmpaque extends Model
{
    use HasFactory;

    protected $table = 'tipos_empaque';

    protected $fillable = [
        'nombre',
        'largo_cm',
        'ancho_cm',
        'alto_cm',
        'peso_maximo_kg',
        'volumen_cm3',
        'icono',
    ];

    public function envios()
    {
        return $this->hasMany(Envio::class, 'tipo_empaque_id');
    }
}
