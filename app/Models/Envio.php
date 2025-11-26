<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Envio extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'cliente_id',
        'almacen_destino_id',
        'categoria',
        'fecha_creacion',
        'fecha_estimada_entrega',
        'hora_estimada',
        'estado',
        'total_cantidad',
        'total_peso',
        'total_precio',
        'observaciones',
        'fecha_asignacion',
        'fecha_inicio_transito',
        'fecha_entrega',
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'fecha_estimada_entrega' => 'date',
        'total_peso' => 'decimal:3',
        'total_precio' => 'decimal:2',
        'total_cantidad' => 'integer',
        'fecha_asignacion' => 'datetime',
        'fecha_inicio_transito' => 'datetime',
        'fecha_entrega' => 'datetime',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function productos()
    {
        return $this->hasMany(EnvioProducto::class);
    }

    public function asignacion()
    {
        return $this->hasOne(EnvioAsignacion::class);
    }
    
    // Helper para obtener la planta (origen)
    public function getPlantaOrigenAttribute()
    {
        return Almacen::where('es_planta', true)->first();
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnTransito($query)
    {
        return $query->where('estado', 'en_transito');
    }

    public function scopeEntregados($query)
    {
        return $query->where('estado', 'entregado');
    }

    public function scopeParaAlmacen($query, $almacenId)
    {
        return $query->where('almacen_destino_id', $almacenId);
    }

    // Helpers
    public function calcularTotales()
    {
        $this->total_cantidad = $this->productos->sum('cantidad');
        $this->total_peso = $this->productos->sum('total_peso');
        $this->total_precio = $this->productos->sum('total_precio');
        $this->save();
    }

    public function iniciarTransito()
    {
        $this->estado = 'en_transito';
        $this->fecha_inicio_transito = now();
        $this->save();
    }

    public function marcarEntregado()
    {
        $this->estado = 'entregado';
        $this->fecha_entrega = now();
        $this->save();
    }
}
