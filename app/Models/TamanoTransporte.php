<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TamanoTransporte extends Model
{
    use HasFactory;

    protected $table = 'tamanos_transporte';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
}

