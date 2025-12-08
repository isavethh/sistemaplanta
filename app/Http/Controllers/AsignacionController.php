<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\EnvioAsignacion;
use App\Models\Vehiculo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionController extends Controller
{
    /**
     * Mostrar lista de envíos pendientes y asignados
     */
    public function index()
    {
        $enviosPendientes = Envio::with(['almacenDestino'])
            ->where('estado', 'pendiente')
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
                'transportista_id' => 'required|exists:users,id',
                'vehiculo_id' => 'required|exists:vehiculos,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', 'Datos inválidos: ' . implode(', ', $e->validator->errors()->all()));
        }

        DB::beginTransaction();
        try {
            // Verificar que el envío esté pendiente
            $envio = Envio::findOrFail($request->envio_id);
            if ($envio->estado !== 'pendiente') {
                DB::rollBack();
                return back()->with('error', 'El envío ya fue asignado o está en otro estado. Estado actual: ' . $envio->estado);
            }

            // Verificar que el transportista exista y sea tipo transportista
            $transportista = User::where('id', $request->transportista_id)
                ->where('tipo', 'transportista')
                ->first();
            
            if (!$transportista) {
                DB::rollBack();
                return back()->with('error', 'El transportista seleccionado no es válido.');
            }

            // Verificar que el vehículo no esté ocupado
            $vehiculoOcupado = EnvioAsignacion::whereHas('envio', function($query) {
                $query->whereIn('estado', ['asignado', 'aceptado', 'en_transito']);
            })->where('vehiculo_id', $request->vehiculo_id)->exists();

            if ($vehiculoOcupado) {
                DB::rollBack();
                return back()->with('error', 'El vehículo seleccionado ya está asignado a otro envío activo.');
            }

            // Crear asignación
            $asignacion = EnvioAsignacion::create([
                'envio_id' => $request->envio_id,
                'transportista_id' => $request->transportista_id,
                'vehiculo_id' => $request->vehiculo_id,
                'fecha_asignacion' => now(),
            ]);

            // Actualizar estado del envío y fecha de asignación
            $envio->update([
                'estado' => 'asignado',
                'fecha_asignacion' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            
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
                'transportista_id' => $envio->asignacion->transportista_id ?? null,
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





