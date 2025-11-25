<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacenes';

    protected $fillable = [
        'nombre',
        'usuario_almacen_id',
        'latitud',
        'longitud',
        'direccion_completa',
        'es_planta',
        'activo',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'es_planta' => 'boolean',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function usuarioAlmacen()
    {
        return $this->belongsTo(User::class, 'usuario_almacen_id');
    }

    public function inventario()
    {
        return $this->hasMany(InventarioAlmacen::class);
    }

    public function direccionesComoOrigen()
    {
        return $this->hasMany(Direccion::class, 'almacen_origen_id');
    }

    public function direccionesComoDestino()
    {
        return $this->hasMany(Direccion::class, 'almacen_destino_id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePlanta($query)
    {
        return $query->where('es_planta', true);
    }

    public function scopeNoPlanta($query)
    {
        return $query->where('es_planta', false);
    }

    // Helpers
    public function getCoordenadasAttribute()
    {
        return [$this->latitud, $this->longitud];
    }
}
