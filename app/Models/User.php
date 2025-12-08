<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tipo',
        'telefono',
        'direccion',
        'licencia',
        'disponible',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'disponible' => 'boolean',
        ];
    }

    // Relaciones
    public function enviosComoCliente()
    {
        return $this->hasMany(Envio::class, 'cliente_id');
    }

    public function enviosComoTransportista()
    {
        return $this->hasMany(Envio::class, 'transportista_id');
    }

    public function vehiculo()
    {
        return $this->hasOne(Vehiculo::class, 'transportista_id');
    }

    public function almacenesAcargo()
    {
        return $this->hasMany(Almacen::class, 'encargado_id');
    }

    // Scopes
    public function scopeClientes($query)
    {
        return $query->where('tipo', 'cliente')->orWhere('role', 'cliente');
    }

    public function scopeTransportistas($query)
    {
        return $query->where('tipo', 'transportista')->orWhere('role', 'transportista');
    }

    public function scopeDisponibles($query)
    {
        return $query->where('disponible', true);
    }

    // Helpers - Según el flujo real del sistema
    public function esPlanta()
    {
        // Planta = Cliente que crea envíos desde planta
        return $this->hasRole('planta') || 
               $this->tipo === 'cliente' || 
               $this->role === 'cliente' ||
               $this->tipo === 'planta' ||
               $this->role === 'planta';
    }

    public function esAdministrador()
    {
        // Administrador = Asigna envíos a transportistas
        return $this->hasRole('administrador') || 
               $this->tipo === 'admin' || 
               $this->role === 'admin';
    }

    public function esTransportista()
    {
        // Transportista = Acepta/rechaza, monitorea, entrega
        return $this->hasRole('transportista') || 
               $this->tipo === 'transportista' || 
               $this->role === 'transportista';
    }

    public function esAlmacen()
    {
        // Almacén = Recibe envíos, firma, reporta incidentes
        return $this->hasRole('almacen') || 
               $this->tipo === 'almacen' || 
               $this->role === 'almacen';
    }

    // Alias para compatibilidad
    public function esCliente()
    {
        return $this->esPlanta();
    }

    public function esAdmin()
    {
        return $this->esAdministrador();
    }

    public function puedeConducir($licenciaRequerida)
    {
        if (!$this->licencia) return false;
        
        $jerarquia = ['A' => 3, 'B' => 2, 'C' => 1];
        return $jerarquia[$this->licencia] >= $jerarquia[$licenciaRequerida];
    }
}
