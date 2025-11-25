<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoQR extends Model
{
    use HasFactory;

    protected $table = 'codigosqr';

    protected $fillable = ['codigo', 'descripcion'];
}
