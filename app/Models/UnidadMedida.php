<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidades_medida';

    protected $fillable = ['nombre', 'abreviatura'];

    public function envios()
    {
        return $this->hasMany(Envio::class, 'unidad_medida_id');
    }
}
