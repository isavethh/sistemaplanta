<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Envio;
use App\Models\PropuestaVehiculo;
use App\Services\PropuestaVehiculosService;
use Illuminate\Support\Facades\Log;

class MigrarPropuestasExistentes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'propuestas:migrar-existentes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar propuestas de vehículos de envíos existentes que vienen de Trazabilidad';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando envíos de Trazabilidad para migrar propuestas...');

        // Buscar envíos que vienen de Trazabilidad
        $envios = Envio::where(function($query) {
                $query->where('estado', 'pendiente_aprobacion_trazabilidad')
                    ->orWhere('estado', 'pendiente')
                    ->orWhere('estado', 'cancelado');
            })
            ->where(function($query) {
                $query->whereRaw("observaciones LIKE '%TRAZABILIDAD%'")
                    ->orWhereRaw("observaciones LIKE '%trazabilidad%'")
                    ->orWhereRaw("observaciones LIKE '%Trazabilidad%'")
                    ->orWhere('estado', 'pendiente_aprobacion_trazabilidad');
            })
            ->get();

        $this->info("Encontrados {$envios->count()} envíos de Trazabilidad");

        $propuestaService = new PropuestaVehiculosService();
        $creadas = 0;
        $actualizadas = 0;
        $errores = 0;

        foreach ($envios as $envio) {
            try {
                // Determinar el estado de la propuesta basado en el estado del envío y observaciones
                $estadoPropuesta = 'pendiente';
                $observaciones = $envio->observaciones ?? '';
                
                // Si el envío está en estado 'pendiente' y viene de Trazabilidad, 
                // probablemente fue aprobado (porque cuando se aprueba, cambia a 'pendiente')
                if ($envio->estado === 'pendiente' && strpos($observaciones, 'Trazabilidad') !== false) {
                    // Verificar si tiene decisión explícita de Trazabilidad
                    if (strpos($observaciones, 'DECISIÓN TRAZABILIDAD') !== false) {
                        if (strpos($observaciones, 'Acción: APROBAR') !== false || 
                            strpos($observaciones, 'aprobada por Trazabilidad') !== false) {
                            $estadoPropuesta = 'aprobada';
                        } elseif (strpos($observaciones, 'Acción: RECHAZAR') !== false || 
                                  strpos($observaciones, 'rechazada por Trazabilidad') !== false) {
                            $estadoPropuesta = 'rechazada';
                        } else {
                            // Si tiene DECISIÓN TRAZABILIDAD pero no especifica rechazo, asumir aprobada
                            $estadoPropuesta = 'aprobada';
                        }
                    } elseif (strpos($observaciones, 'Propuesta de vehículos aprobada') !== false) {
                        $estadoPropuesta = 'aprobada';
                    } elseif (strpos($observaciones, 'Propuesta de vehículos rechazada') !== false) {
                        $estadoPropuesta = 'rechazada';
                    } else {
                        // Si está en 'pendiente' y viene de Trazabilidad, probablemente fue aprobada
                        // (porque cuando se aprueba, el estado cambia de 'pendiente_aprobacion_trazabilidad' a 'pendiente')
                        $estadoPropuesta = 'aprobada';
                    }
                } elseif ($envio->estado === 'cancelado' && strpos($observaciones, 'Trazabilidad') !== false) {
                    // Si está cancelado y viene de Trazabilidad, probablemente fue rechazado
                    $estadoPropuesta = 'rechazada';
                } elseif ($envio->estado === 'pendiente_aprobacion_trazabilidad') {
                    // Si aún está pendiente de aprobación, mantener como pendiente
                    $estadoPropuesta = 'pendiente';
                }

                // Calcular propuesta
                $propuestaData = $propuestaService->calcularPropuestaVehiculos($envio);

                // Buscar si ya existe una propuesta
                $propuestaExistente = PropuestaVehiculo::where('envio_id', $envio->id)->first();

                if ($propuestaExistente) {
                    // Actualizar si el estado es diferente o si no tiene datos
                    $propuestaExistente->update([
                        'codigo_envio' => $envio->codigo,
                        'propuesta_data' => $propuestaData,
                        'estado' => $estadoPropuesta,
                    ]);
                    $actualizadas++;
                    $this->line("  ✓ Actualizada propuesta para envío {$envio->codigo} (ID: {$envio->id})");
                } else {
                    // Crear nueva propuesta
                    PropuestaVehiculo::create([
                        'envio_id' => $envio->id,
                        'codigo_envio' => $envio->codigo,
                        'propuesta_data' => $propuestaData,
                        'estado' => $estadoPropuesta,
                        'observaciones_trazabilidad' => null,
                        'aprobado_por' => null,
                        'fecha_propuesta' => $envio->created_at ?? now(),
                        'fecha_decision' => $estadoPropuesta !== 'pendiente' ? now() : null,
                    ]);
                    $creadas++;
                    $this->line("  ✓ Creada propuesta para envío {$envio->codigo} (ID: {$envio->id}) - Estado: {$estadoPropuesta}");
                }
            } catch (\Exception $e) {
                $errores++;
                $this->error("  ✗ Error procesando envío {$envio->codigo} (ID: {$envio->id}): " . $e->getMessage());
                Log::error('Error migrando propuesta', [
                    'envio_id' => $envio->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("\n✅ Migración completada:");
        $this->info("  - Propuestas creadas: {$creadas}");
        $this->info("  - Propuestas actualizadas: {$actualizadas}");
        $this->info("  - Errores: {$errores}");
        $this->info("  - Total procesado: " . ($creadas + $actualizadas));

        return 0;
    }
}

