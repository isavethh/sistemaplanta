<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoVehiculo extends Model
{
    use HasFactory;

    protected $table = 'estados_vehiculo';

    protected $fillable = ['nombre'];
}
