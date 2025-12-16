<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Incidente;

class Envio extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'cliente_id', // Usuario que solicita el envío (única relación directa con users)
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
        'ruta_entrega_id',
        'pedido_almacen_id',
        'propuesta_pdf_path',
        'propuesta_enviada_at',
        'propuesta_aceptada_at',
        'cancelacion_aprobada_almacen',
        'cancelacion_aprobada_trazabilidad',
        'cancelacion_aprobada_at',
        'cancelacion_pdf_path',
        'disconformidad_almacen',
        'disconformidad_trazabilidad',
        'firma_transportista', // Firma del transportista (texto o base64)
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
        'propuesta_enviada_at' => 'datetime',
        'propuesta_aceptada_at' => 'datetime',
        'cancelacion_aprobada_almacen' => 'boolean',
        'cancelacion_aprobada_trazabilidad' => 'boolean',
        'cancelacion_aprobada_at' => 'datetime',
        'disconformidad_almacen' => 'boolean',
        'disconformidad_trazabilidad' => 'boolean',
    ];

    /**
     * Boot del modelo - generar código automáticamente si no existe
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($envio) {
            // Si no tiene código, generarlo automáticamente
            if (empty($envio->codigo)) {
                $envio->codigo = static::generarCodigo();
            }
        });

        static::saving(function ($envio) {
            // Si no tiene código al guardar, generarlo
            if (empty($envio->codigo)) {
                $envio->codigo = static::generarCodigo();
            }
        });
    }

    /**
     * Generar código único para el envío
     */
    public static function generarCodigo(): string
    {
        return 'ENV-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

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

    public function historial()
    {
        return $this->hasMany(HistorialEnvio::class)->orderBy('fecha_hora');
    }

    public function pedidoAlmacen()
    {
        return $this->belongsTo(PedidoAlmacen::class, 'pedido_almacen_id');
    }

    public function incidentes()
    {
        return $this->hasMany(Incidente::class);
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

    public function scopePendienteAprobacionTrazabilidad($query)
    {
        return $query->where('estado', 'pendiente_aprobacion_trazabilidad');
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
