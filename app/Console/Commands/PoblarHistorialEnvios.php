<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Envio;
use App\Models\HistorialEnvio;
use App\Models\Incidente;
use Illuminate\Support\Facades\DB;

class PoblarHistorialEnvios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'historial:poblar {--fresh : Borrar historial existente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pobla el historial de envíos con datos existentes basado en las fechas de los envíos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('🗑️  Borrando historial existente...');
            HistorialEnvio::truncate();
        }

        $envios = Envio::with('asignacion.transportista', 'asignacion.vehiculo')->get();
        $this->info("📦 Procesando {$envios->count()} envíos...");

        $bar = $this->output->createProgressBar($envios->count());
        $bar->start();

        foreach ($envios as $envio) {
            // 1. CREADO
            HistorialEnvio::create([
                'envio_id' => $envio->id,
                'evento' => 'creado',
                'descripcion' => "Envío creado desde Planta hacia {$envio->almacenDestino->nombre}. {$envio->productos->count()} productos, {$envio->total_peso} kg, Bs {$envio->total_precio}",
                'usuario_id' => 1, // Asumimos admin
                'fecha_hora' => $envio->fecha_creacion ?? $envio->created_at,
                'datos_extra' => [
                    'almacen_destino' => $envio->almacenDestino->nombre ?? 'N/A',
                    'total_productos' => $envio->productos->count(),
                    'peso_total' => $envio->total_peso,
                ],
            ]);

            // 2. ASIGNADO (si tiene asignación)
            if ($envio->asignacion && $envio->fecha_asignacion) {
                HistorialEnvio::create([
                    'envio_id' => $envio->id,
                    'evento' => 'asignado',
                    'descripcion' => "Envío asignado a transportista {$envio->asignacion->transportista->name} con vehículo {$envio->asignacion->vehiculo->placa}",
                    'usuario_id' => 1, // Asumimos admin
                    'fecha_hora' => $envio->fecha_asignacion,
                    'datos_extra' => [
                        'transportista' => $envio->asignacion->transportista->name,
                        'vehiculo' => $envio->asignacion->vehiculo->placa,
                        'transportista_id' => $envio->asignacion->transportista_id,
                    ],
                ]);
            }

            // 3. ACEPTADO (si tiene fecha de aceptación)
            if ($envio->asignacion && $envio->asignacion->fecha_aceptacion) {
                HistorialEnvio::create([
                    'envio_id' => $envio->id,
                    'evento' => 'aceptado',
                    'descripcion' => "Transportista {$envio->asignacion->transportista->name} aceptó el envío",
                    'usuario_id' => $envio->asignacion->transportista_id,
                    'fecha_hora' => $envio->asignacion->fecha_aceptacion,
                ]);
            }

            // 4. EN TRÁNSITO (si tiene fecha inicio tránsito)
            if ($envio->fecha_inicio_transito) {
                HistorialEnvio::create([
                    'envio_id' => $envio->id,
                    'evento' => 'en_transito',
                    'descripcion' => "Envío en tránsito hacia {$envio->almacenDestino->nombre}",
                    'usuario_id' => $envio->asignacion->transportista_id ?? null,
                    'fecha_hora' => $envio->fecha_inicio_transito,
                ]);
            }

            // 5. INCIDENTES (si existen)
            $incidentes = DB::table('incidentes')->where('envio_id', $envio->id)->get();
            foreach ($incidentes as $incidente) {
                HistorialEnvio::create([
                    'envio_id' => $envio->id,
                    'evento' => 'incidente',
                    'descripcion' => "Incidente reportado: {$incidente->tipo_incidente}. {$incidente->descripcion}",
                    'usuario_id' => $envio->asignacion->transportista_id ?? null,
                    'fecha_hora' => $incidente->created_at,
                    'datos_extra' => [
                        'tipo' => $incidente->tipo_incidente,
                        'incidente_id' => $incidente->id,
                    ],
                ]);

                // Si el incidente está resuelto
                if ($incidente->estado == 'resuelto' && $incidente->fecha_resolucion) {
                    HistorialEnvio::create([
                        'envio_id' => $envio->id,
                        'evento' => 'resuelto',
                        'descripcion' => "Incidente resuelto: {$incidente->tipo_incidente}. {$incidente->notas_resolucion}",
                        'usuario_id' => 1,
                        'fecha_hora' => $incidente->fecha_resolucion,
                        'datos_extra' => [
                            'tipo' => $incidente->tipo_incidente,
                            'incidente_id' => $incidente->id,
                        ],
                    ]);
                }
            }

            // 6. ENTREGADO (si tiene fecha de entrega)
            if ($envio->fecha_entrega && $envio->estado == 'entregado') {
                HistorialEnvio::create([
                    'envio_id' => $envio->id,
                    'evento' => 'entregado',
                    'descripcion' => "Envío entregado exitosamente en {$envio->almacenDestino->nombre}",
                    'usuario_id' => $envio->asignacion->transportista_id ?? null,
                    'fecha_hora' => $envio->fecha_entrega,
                    'datos_extra' => [
                        'latitud' => $envio->almacenDestino->latitud,
                        'longitud' => $envio->almacenDestino->longitud,
                    ],
                ]);
            }

            // 7. CANCELADO (si está cancelado)
            if ($envio->estado == 'cancelado') {
                HistorialEnvio::create([
                    'envio_id' => $envio->id,
                    'evento' => 'cancelado',
                    'descripcion' => "Envío cancelado",
                    'usuario_id' => 1,
                    'fecha_hora' => $envio->updated_at,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ Historial poblado exitosamente');
        
        $totalEventos = HistorialEnvio::count();
        $this->info("📊 Total de eventos creados: {$totalEventos}");
    }
}
