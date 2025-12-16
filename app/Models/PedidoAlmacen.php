<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoAlmacen extends Model
{
    use HasFactory;

    protected $table = 'pedidos_almacen';

    protected $fillable = [
        'codigo',
        'almacen_id',
        'usuario_propietario_id',
        'fecha_requerida',
        'hora_requerida',
        'estado',
        'latitud',
        'longitud',
        'direccion_completa',
        'envio_id',
        'observaciones',
        'fecha_envio_trazabilidad',
        'fecha_aceptacion_trazabilidad',
        'fecha_propuesta_enviada',
        'fecha_propuesta_aceptada',
    ];

    protected $casts = [
        'fecha_requerida' => 'date',
        'hora_requerida' => 'datetime',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'fecha_envio_trazabilidad' => 'datetime',
        'fecha_aceptacion_trazabilidad' => 'datetime',
        'fecha_propuesta_enviada' => 'datetime',
        'fecha_propuesta_aceptada' => 'datetime',
    ];

    // Relaciones
    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function propietario()
    {
        return $this->belongsTo(User::class, 'usuario_propietario_id');
    }

    public function productos()
    {
        return $this->hasMany(PedidoProducto::class, 'pedido_almacen_id');
    }

    public function envio()
    {
        return $this->belongsTo(Envio::class, 'envio_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnviadosTrazabilidad($query)
    {
        return $query->where('estado', 'enviado_trazabilidad');
    }

    public function scopeAceptadosTrazabilidad($query)
    {
        return $query->where('estado', 'aceptado_trazabilidad');
    }

    public function scopePropuestaEnviada($query)
    {
        return $query->where('estado', 'propuesta_enviada');
    }

    public function scopePropuestaAceptada($query)
    {
        return $query->where('estado', 'propuesta_aceptada');
    }

    public function scopeCancelados($query)
    {
        return $query->where('estado', 'cancelado');
    }

    public function scopeEntregados($query)
    {
        return $query->where('estado', 'entregado');
    }

    // Helpers
    public function calcularTotales()
    {
        $this->save(); // Los totales se calculan desde los productos relacionados
    }

    public function enviarATrazabilidad()
    {
        $this->estado = 'enviado_trazabilidad';
        $this->fecha_envio_trazabilidad = now();
        $this->save();
    }

    public function aceptarEnTrazabilidad()
    {
        $this->estado = 'aceptado_trazabilidad';
        $this->fecha_aceptacion_trazabilidad = now();
        $this->save();
    }

    public function marcarPropuestaEnviada()
    {
        $this->estado = 'propuesta_enviada';
        $this->fecha_propuesta_enviada = now();
        $this->save();
    }

    public function aceptarPropuesta()
    {
        $this->estado = 'propuesta_aceptada';
        $this->fecha_propuesta_aceptada = now();
        $this->save();
    }

    public function cancelar()
    {
        $this->estado = 'cancelado';
        $this->save();
    }

    public function marcarEntregado()
    {
        $this->estado = 'entregado';
        $this->save();
    }
}
