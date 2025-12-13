<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\EnvioAsignacion;
use App\Models\Vehiculo;
use App\Models\User;
use App\Services\AlmacenIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionController extends Controller
{
    /**
     * Mostrar lista de envíos pendientes y asignados
     */
    public function index()
    {
        // Mostrar envíos pendientes o ya aprobados (listos para asignación)
        $enviosPendientes = Envio::with(['almacenDestino'])
            ->whereIn('estado', ['pendiente', 'aprobado'])
            ->orderBy('created_at', 'desc')
            ->get();

        $enviosAsignados = Envio::with(['almacenDestino', 'asignacion.transportista', 'asignacion.vehiculo'])
            ->whereIn('estado', ['asignado', 'aceptado'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // NUEVO: Envíos rechazados para que el admin los vea
        $enviosRechazados = Envio::with(['almacenDestino', 'asignacion.transportista', 'asignacion.vehiculo'])
            ->where('estado', 'rechazado')
            ->orderBy('fecha_rechazo', 'desc')
            ->get();

        $transportistas = User::transportistas()
            ->where('disponible', true)
            ->get();

        $vehiculos = Vehiculo::disponibles()->get();

        return view('asignaciones.index', compact('enviosPendientes', 'enviosAsignados', 'enviosRechazados', 'transportistas', 'vehiculos'));
    }

    /**
     * Asignar transportista y vehículo a un envío
     */
    public function asignar(Request $request)
    {
        try {
            $validated = $request->validate([
                'envio_id' => 'required|exists:envios,id',
                'vehiculo_id' => 'required|exists:vehiculos,id',
                'transportista_id' => 'nullable|exists:users,id', // Opcional: si viene, se asigna al vehículo
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', 'Datos inválidos: ' . implode(', ', $e->validator->errors()->all()));
        }

        DB::beginTransaction();
        try {
            // Verificar que el envío esté en un estado asignable (pendiente o aprobado)
            $envio = Envio::findOrFail($request->envio_id);
            if (! in_array($envio->estado, ['pendiente', 'aprobado'])) {
                DB::rollBack();
                return back()->with('error', 'El envío no se puede asignar en su estado actual: ' . $envio->estado);
            }

            // Verificar que el vehículo exista
            $vehiculo = Vehiculo::with('transportista')->findOrFail($request->vehiculo_id);
            
            // Obtener o asignar transportista
            $transportista = null;
            
            // Si viene transportista_id en el request, asignarlo al vehículo
            if ($request->has('transportista_id') && $request->transportista_id) {
                // Verificar que el transportista exista y sea válido
                $transportista = User::where('id', $request->transportista_id)
                    ->where(function($q) {
                        $q->where('tipo', 'transportista')
                          ->orWhere('role', 'transportista');
                    })
                    ->first();
                
                if (!$transportista) {
                    DB::rollBack();
                    return back()->with('error', 'El transportista seleccionado no es válido.');
                }
                
                // Asignar el transportista al vehículo
                $vehiculo->update(['transportista_id' => $request->transportista_id]);
                \Log::info("✅ Transportista {$transportista->name} (ID: {$request->transportista_id}) asignado al vehículo {$vehiculo->placa}");
            } else {
                // Si no viene transportista_id, obtenerlo del vehículo
                $vehiculo->refresh(); // Recargar para obtener el transportista_id actualizado
                $vehiculo->load('transportista');
                $transportista = $vehiculo->transportista;
            }
            
            // Verificar que tengamos un transportista válido
            if (!$transportista || !$transportista->id) {
                DB::rollBack();
                \Log::error("❌ Intento de asignar envío {$request->envio_id} a vehículo {$vehiculo->placa} sin transportista");
                return back()->with('error', 'El vehículo seleccionado (' . $vehiculo->placa . ') no tiene un transportista asignado. Por favor, selecciona un transportista en el formulario.');
            }
            
            \Log::info("🔍 Verificando asignación para envío {$request->envio_id}, vehículo {$vehiculo->placa}, transportista {$transportista->name} (ID: {$transportista->id})");

            // Verificar si ya existe una asignación para este envío
            $asignacionExistente = EnvioAsignacion::where('envio_id', $request->envio_id)->first();
            
            if ($asignacionExistente) {
                // Actualizar asignación existente
                $asignacionExistente->update([
                    'vehiculo_id' => $request->vehiculo_id,
                    'fecha_asignacion' => now(),
                ]);
                $asignacion = $asignacionExistente;
            } else {
                // Crear nueva asignación
                $asignacion = EnvioAsignacion::create([
                    'envio_id' => $request->envio_id,
                    'vehiculo_id' => $request->vehiculo_id,
                    'fecha_asignacion' => now(),
                ]);
            }

            // Actualizar estado del envío y fecha de asignación
            $envio->update([
                'estado' => 'asignado',
                'fecha_asignacion' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            
            // Notificar a sistema-almacen-PSIII sobre la asignación
            try {
                $almacenService = new AlmacenIntegrationService();
                $almacenService->notifyAsignacion($envio);
            } catch (\Exception $e) {
                \Log::warning("No se pudo notificar asignación a almacenes: " . $e->getMessage());
                // No fallar la asignación si la notificación falla
            }
            
            \Log::info("✅ Envío {$envio->codigo} asignado a transportista {$transportista->name}");
            
            return back()->with('success', "✅ Envío {$envio->codigo} asignado correctamente a {$transportista->name}. El transportista podrá verlo en la app móvil.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("❌ Error al asignar envío: " . $e->getMessage());
            return back()->with('error', 'Error al asignar: ' . $e->getMessage() . ' | Línea: ' . $e->getLine());
        }
    }

    /**
     * Remover asignación (solo si no ha sido aceptada ni iniciada)
     */
    public function remover($envioId)
    {
        DB::beginTransaction();
        try {
            $envio = Envio::with('asignacion')->findOrFail($envioId);
            
            if ($envio->estado !== 'asignado') {
                return back()->with('error', 'Solo se pueden remover asignaciones no aceptadas.');
            }

            // Eliminar asignación
            if ($envio->asignacion) {
                $envio->asignacion->delete();
            }

            // Volver estado a pendiente
            $envio->update(['estado' => 'pendiente']);

            DB::commit();
            return back()->with('success', 'Asignación removida. El envío vuelve a estar pendiente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al remover: ' . $e->getMessage());
        }
    }

    /**
     * Sincronizar con backend de Node.js
     */
    private function sincronizarConNodeJS($envio)
    {
        try {
            $envio->load(['almacenDestino', 'productos', 'asignacion.transportista', 'asignacion.vehiculo']);

            $data = [
                'laravel_envio_id' => $envio->id,
                'codigo' => $envio->codigo,
                'almacen_destino_id' => $envio->almacen_destino_id,
                'almacen_destino_nombre' => $envio->almacenDestino->nombre ?? null,
                'fecha_estimada_entrega' => $envio->fecha_estimada_entrega,
                'hora_estimada' => $envio->hora_estimada,
                'estado' => $envio->estado,
                'total_cantidad' => $envio->productos->sum('cantidad'),
                'total_peso' => $envio->productos->sum('total_peso'),
                'total_precio' => $envio->productos->sum('total_precio'),
                'transportista_id' => $envio->asignacion->vehiculo->transportista_id ?? null,
                'vehiculo_id' => $envio->asignacion->vehiculo_id ?? null,
            ];

            $nodeApiUrl = env('NODE_API_URL', 'http://localhost:3000/api');
            
            $ch = curl_init($nodeApiUrl . '/envios/sync');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            // Log error pero no fallar
            \Log::warning('Error sincronizando con Node.js: ' . $e->getMessage());
        }
    }
}





