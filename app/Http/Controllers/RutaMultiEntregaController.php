<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Vehiculo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RutaMultiEntregaController extends Controller
{
    /**
     * URL del backend Node.js
     */
    protected $nodeApiUrl;

    public function __construct()
    {
        $this->nodeApiUrl = env('NODE_API_URL', 'http://localhost:3001/api');
    }

    /**
     * Mostrar vista para crear ruta multi-entrega
     */
    public function create()
    {
        // Obtener envíos pendientes de asignación
        $enviosPendientes = Envio::with(['almacenDestino', 'productos'])
            ->whereIn('estado', ['pendiente', 'asignado'])
            ->whereNull('ruta_entrega_id')
            ->orderBy('fecha_estimada_entrega', 'asc')
            ->get();

        // Transportistas disponibles
        $transportistas = User::where('tipo', 'transportista')
            ->where('disponible', true)
            ->orderBy('name')
            ->get();

        // Vehículos disponibles
        $vehiculos = Vehiculo::with(['tamanoVehiculo', 'tipoTransporte'])
            ->where('disponible', true)
            ->get();

        return view('rutas-multi.create', compact('enviosPendientes', 'transportistas', 'vehiculos'));
    }

    /**
     * Guardar nueva ruta multi-entrega
     */
    public function store(Request $request)
    {
        $request->validate([
            'transportista_id' => 'required|exists:users,id',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'envios_ids' => 'required|array|min:1',
            'envios_ids.*' => 'exists:envios,id',
            'fecha' => 'nullable|date',
        ]);

        // VALIDACIÓN: Todos los envíos deben tener la misma fecha de entrega
        $envios = Envio::whereIn('id', $request->envios_ids)->get();
        
        $fechasUnicas = $envios->pluck('fecha_estimada_entrega')
            ->filter()
            ->unique()
            ->values();
        
        if ($fechasUnicas->count() > 1) {
            return back()->withInput()->with('error', 
                'ERROR: No se pueden agrupar envíos con diferentes fechas de entrega en la misma ruta. ' .
                'Todos los envíos deben tener la misma fecha estimada de entrega. ' .
                'Fechas detectadas: ' . $fechasUnicas->map(function($fecha) {
                    return \Carbon\Carbon::parse($fecha)->format('d/m/Y');
                })->implode(', ')
            );
        }

        try {
            // Llamar al API de Node.js para crear la ruta
            $response = Http::timeout(10)->post("{$this->nodeApiUrl}/rutas-entrega", [
                'transportista_id' => $request->transportista_id,
                'vehiculo_id' => $request->vehiculo_id,
                'envios_ids' => $request->envios_ids,
                'fecha' => $request->fecha ?? now()->toDateString(),
            ]);

            if ($response->successful() && $response->json('success')) {
                $ruta = $response->json('ruta');
                Log::info("✅ Ruta multi-entrega creada: {$ruta['codigo']} con {$ruta['total_envios']} envíos");
                
                return redirect()->route('rutas-multi.index')
                    ->with('success', "Ruta {$ruta['codigo']} creada exitosamente con {$ruta['total_envios']} envíos.");
            } else {
                $error = $response->json('message') ?? 'Error desconocido';
                return back()->withInput()->with('error', "Error al crear ruta: {$error}");
            }
        } catch (\Exception $e) {
            Log::error("Error al crear ruta multi-entrega: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error de conexión con el servidor: ' . $e->getMessage());
        }
    }

    /**
     * Listar todas las rutas multi-entrega
     */
    public function index(Request $request)
    {
        try {
            $params = [];
            if ($request->filled('estado')) {
                $params['estado'] = $request->estado;
            }
            if ($request->filled('fecha')) {
                $params['fecha'] = $request->fecha;
            }

            $response = Http::timeout(10)->get("{$this->nodeApiUrl}/rutas-entrega", $params);

            if ($response->successful()) {
                $rutas = $response->json('rutas') ?? [];
            } else {
                $rutas = [];
                session()->flash('warning', 'No se pudieron obtener las rutas del servidor.');
            }
        } catch (\Exception $e) {
            Log::error("Error al obtener rutas: " . $e->getMessage());
            $rutas = [];
            session()->flash('error', 'Error de conexión con el servidor.');
        }

        return view('rutas-multi.index', compact('rutas'));
    }

    /**
     * Ver detalle de una ruta
     */
    public function show($id)
    {
        try {
            $response = Http::timeout(10)->get("{$this->nodeApiUrl}/rutas-entrega/{$id}");

            if ($response->successful() && $response->json('success')) {
                $ruta = $response->json('ruta');
                return view('rutas-multi.show', compact('ruta'));
            } else {
                return redirect()->route('rutas-multi.index')
                    ->with('error', 'Ruta no encontrada.');
            }
        } catch (\Exception $e) {
            Log::error("Error al obtener ruta: " . $e->getMessage());
            return redirect()->route('rutas-multi.index')
                ->with('error', 'Error de conexión con el servidor.');
        }
    }

    /**
     * Ver resumen completo de una ruta (para PDF)
     */
    public function resumen($id)
    {
        try {
            $response = Http::timeout(10)->get("{$this->nodeApiUrl}/rutas-entrega/{$id}/resumen");

            if ($response->successful() && $response->json('success')) {
                $resumen = $response->json('resumen');
                return view('rutas-multi.resumen', compact('resumen'));
            } else {
                return redirect()->route('rutas-multi.index')
                    ->with('error', 'Ruta no encontrada.');
            }
        } catch (\Exception $e) {
            Log::error("Error al obtener resumen: " . $e->getMessage());
            return redirect()->route('rutas-multi.index')
                ->with('error', 'Error de conexión con el servidor.');
        }
    }

    /**
     * Dashboard de monitoreo de rutas en tiempo real
     */
    public function monitoreo()
    {
        try {
            $response = Http::timeout(10)->get("{$this->nodeApiUrl}/rutas-entrega/estadisticas");

            if ($response->successful()) {
                $estadisticas = $response->json('estadisticas');
                $rutasActivas = $response->json('rutasActivas') ?? [];
            } else {
                $estadisticas = null;
                $rutasActivas = [];
            }
        } catch (\Exception $e) {
            Log::error("Error al obtener estadísticas: " . $e->getMessage());
            $estadisticas = null;
            $rutasActivas = [];
        }

        return view('rutas-multi.monitoreo', compact('estadisticas', 'rutasActivas'));
    }

    /**
     * Reordenar paradas de una ruta
     */
    public function reordenarParadas(Request $request, $rutaId)
    {
        $request->validate([
            'paradas' => 'required|array',
            'paradas.*.id' => 'required|integer',
            'paradas.*.orden' => 'required|integer|min:1',
        ]);

        try {
            $response = Http::timeout(10)->put("{$this->nodeApiUrl}/rutas-entrega/{$rutaId}/paradas/reordenar", [
                'paradas' => $request->paradas,
            ]);

            if ($response->successful() && $response->json('success')) {
                return response()->json(['success' => true, 'message' => 'Paradas reordenadas exitosamente']);
            } else {
                $error = $response->json('message') ?? 'Error desconocido';
                return response()->json(['success' => false, 'message' => $error], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Obtener datos para mapa de envíos pendientes
     */
    public function enviosPendientesParaMapa()
    {
        $envios = Envio::with(['almacenDestino', 'productos'])
            ->whereIn('estado', ['pendiente', 'asignado'])
            ->whereNull('ruta_entrega_id')
            ->get()
            ->map(function ($envio) {
                return [
                    'id' => $envio->id,
                    'codigo' => $envio->codigo,
                    'destino' => $envio->almacenDestino->nombre ?? 'Sin destino',
                    'direccion' => $envio->almacenDestino->direccion ?? '',
                    'lat' => $envio->almacenDestino->latitud ?? null,
                    'lng' => $envio->almacenDestino->longitud ?? null,
                    'peso' => $envio->productos->sum('total_peso'),
                    'cantidad' => $envio->productos->sum('cantidad'),
                    'fecha_estimada' => $envio->fecha_estimada_entrega,
                    'estado' => $envio->estado,
                ];
            })
            ->filter(function ($envio) {
                return $envio['lat'] !== null && $envio['lng'] !== null;
            })
            ->values();

        return response()->json(['envios' => $envios]);
    }

    /**
     * Mostrar documentos de entrega (checklists y fotos)
     */
    public function documentos($id)
    {
        try {
            $response = Http::timeout(10)->get("{$this->nodeApiUrl}/rutas-entrega/{$id}");

            if ($response->successful() && $response->json('success')) {
                $ruta = $response->json('ruta');
                
                // Obtener documentos/evidencias de cada parada
                $paradasConDocumentos = [];
                if (isset($ruta['paradas']) && is_array($ruta['paradas'])) {
                    foreach ($ruta['paradas'] as $parada) {
                        $paradaInfo = [
                            'id' => $parada['id'],
                            'envio_id' => $parada['envio_id'] ?? null,
                            'envio_codigo' => $parada['envio']['codigo'] ?? 'N/A',
                            'destino' => $parada['envio']['almacen_destino']['nombre'] ?? 'Sin destino',
                            'direccion' => $parada['envio']['almacen_destino']['direccion'] ?? '',
                            'estado' => $parada['estado'] ?? 'pendiente',
                            'orden' => $parada['orden'] ?? 0,
                            'hora_llegada' => $parada['hora_llegada'] ?? null,
                            'hora_entrega' => $parada['hora_entrega'] ?? null,
                            'checklist' => [],
                            'fotos' => [],
                            'firma' => null,
                            'notas' => $parada['notas'] ?? null,
                        ];

                        // Obtener evidencias desde el backend
                        if (isset($parada['envio_id'])) {
                            try {
                                $evidenciasResponse = Http::timeout(5)->get("{$this->nodeApiUrl}/evidencias/envio/{$parada['envio_id']}");
                                if ($evidenciasResponse->successful()) {
                                    $evidencias = $evidenciasResponse->json('evidencias') ?? [];
                                    foreach ($evidencias as $ev) {
                                        if ($ev['tipo'] == 'foto' || $ev['tipo'] == 'foto_entrega') {
                                            $paradaInfo['fotos'][] = [
                                                'url' => $ev['url'] ?? $ev['archivo'] ?? '',
                                                'descripcion' => $ev['descripcion'] ?? '',
                                                'fecha' => $ev['created_at'] ?? null,
                                            ];
                                        } elseif ($ev['tipo'] == 'firma') {
                                            $paradaInfo['firma'] = $ev['url'] ?? $ev['archivo'] ?? '';
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::warning("No se pudieron obtener evidencias para envio {$parada['envio_id']}: " . $e->getMessage());
                            }
                        }

                        // Checklist genérico basado en estado
                        $paradaInfo['checklist'] = $this->generarChecklist($parada);

                        $paradasConDocumentos[] = $paradaInfo;
                    }
                }

                return view('rutas-multi.documentos', [
                    'ruta' => $ruta,
                    'paradas' => $paradasConDocumentos,
                ]);
            } else {
                return redirect()->route('rutas-multi.index')
                    ->with('error', 'No se pudo obtener información de la ruta');
            }
        } catch (\Exception $e) {
            Log::error("Error al obtener documentos de ruta {$id}: " . $e->getMessage());
            return redirect()->route('rutas-multi.index')
                ->with('error', 'Error al cargar documentos: ' . $e->getMessage());
        }
    }

    /**
     * Generar checklist basado en el estado de la parada
     */
    private function generarChecklist($parada)
    {
        $checklist = [
            ['item' => 'Parada asignada', 'completado' => true, 'icono' => 'fa-check-circle'],
        ];

        $estado = $parada['estado'] ?? 'pendiente';

        if (in_array($estado, ['en_camino', 'llegada', 'entregado', 'completado', 'completada'])) {
            $checklist[] = ['item' => 'En camino a destino', 'completado' => true, 'icono' => 'fa-truck'];
        } else {
            $checklist[] = ['item' => 'En camino a destino', 'completado' => false, 'icono' => 'fa-truck'];
        }

        if (in_array($estado, ['llegada', 'entregado', 'completado', 'completada'])) {
            $checklist[] = ['item' => 'Llegada al destino', 'completado' => true, 'icono' => 'fa-map-marker-alt'];
        } else {
            $checklist[] = ['item' => 'Llegada al destino', 'completado' => false, 'icono' => 'fa-map-marker-alt'];
        }

        if (in_array($estado, ['entregado', 'completado', 'completada'])) {
            $checklist[] = ['item' => 'Entrega realizada', 'completado' => true, 'icono' => 'fa-box-open'];
            $checklist[] = ['item' => 'Documentación firmada', 'completado' => true, 'icono' => 'fa-signature'];
        } else {
            $checklist[] = ['item' => 'Entrega realizada', 'completado' => false, 'icono' => 'fa-box-open'];
            $checklist[] = ['item' => 'Documentación firmada', 'completado' => false, 'icono' => 'fa-signature'];
        }

        return $checklist;
    }
}

