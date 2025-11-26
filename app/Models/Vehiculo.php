<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'anio',
        'tipo_vehiculo',
        'tamano_vehiculo_id',
        'tipo_transporte_id',
        'licencia_requerida',
        'capacidad_carga',
        'unidad_medida_carga_id',
        'capacidad_volumen',
        'transportista_id',
        'disponible',
        'estado',
    ];

    protected $casts = [
        'capacidad_carga' => 'decimal:2',
        'capacidad_volumen' => 'decimal:2',
        'disponible' => 'boolean',
        'anio' => 'integer',
    ];

    // Relaciones
    public function transportista()
    {
        return $this->belongsTo(User::class, 'transportista_id');
    }

    public function tamanoVehiculo()
    {
        return $this->belongsTo(TamanoVehiculo::class, 'tamano_vehiculo_id');
    }

    public function tipoTransporte()
    {
        return $this->belongsTo(TipoTransporte::class);
    }

    public function unidadMedidaCarga()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_carga_id');
    }

    public function asignaciones()
    {
        return $this->hasMany(EnvioAsignacion::class);
    }

    // Scopes
    public function scopeDisponibles($query)
    {
        return $query->where('disponible', true)->where('estado', 'activo');
    }

    public function scopeParaLicencia($query, $licencia)
    {
        return $query->where('licencia_requerida', $licencia);
    }

    // Helpers
    public function estaDisponible()
    {
        return $this->disponible && $this->estado === 'activo';
    }

    public function puedeTransportar($peso, $volumen)
    {
        return $this->capacidad_carga >= $peso && $this->capacidad_volumen >= $volumen;
    }
}
