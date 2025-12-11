<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEnvio extends Model
{
    use HasFactory;

    protected $table = 'historial_envio';

    protected $fillable = [
        'envio_id',
        'evento',
        'descripcion',
        // usuario_id eliminado para romper ciclo (se almacena en datos_extra si es necesario)
        'fecha_hora',
        'datos_extra',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'datos_extra' => 'array',
    ];

    // Relaciones
    public function envio()
    {
        return $this->belongsTo(Envio::class);
    }

    // Método para obtener usuario (a través de envio → almacen → usuario_almacen_id)
    public function getUsuarioAttribute()
    {
        // Obtener usuario a través de la cadena de relaciones
        if ($this->envio && $this->envio->almacenDestino && $this->envio->almacenDestino->usuarioAlmacen) {
            return $this->envio->almacenDestino->usuarioAlmacen;
        }
        
        // Si está en datos_extra, intentar obtenerlo
        $datosExtra = $this->datos_extra ?? [];
        if (isset($datosExtra['usuario_id'])) {
            return \App\Models\User::find($datosExtra['usuario_id']);
        }
        
        return null;
    }

    // Método estático para registrar evento
    public static function registrar($envioId, $evento, $descripcion = null, $usuarioId = null, $datosExtra = null)
    {
        // Almacenar usuario_id en datos_extra si se proporciona
        if ($usuarioId || auth()->check()) {
            $datosExtra = $datosExtra ?? [];
            if (!is_array($datosExtra)) {
                $datosExtra = [];
            }
            $datosExtra['usuario_id'] = $usuarioId ?? auth()->id();
        }
        
        return self::create([
            'envio_id' => $envioId,
            'evento' => $evento,
            'descripcion' => $descripcion,
            'fecha_hora' => now(),
            'datos_extra' => $datosExtra,
        ]);
    }

    // Iconos por tipo de evento
    public function getIconoAttribute()
    {
        return match($this->evento) {
            'creado' => '📝',
            'asignado' => '👤',
            'aceptado' => '✅',
            'en_transito' => '🚚',
            'entregado' => '🎯',
            'incidente' => '⚠️',
            'cancelado' => '❌',
            'resuelto' => '✔️',
            default => '📌',
        };
    }

    // Color por tipo de evento
    public function getColorAttribute()
    {
        return match($this->evento) {
            'creado' => 'secondary',
            'asignado' => 'info',
            'aceptado' => 'primary',
            'en_transito' => 'warning',
            'entregado' => 'success',
            'incidente' => 'danger',
            'cancelado' => 'dark',
            'resuelto' => 'success',
            default => 'secondary',
        };
    }
}
