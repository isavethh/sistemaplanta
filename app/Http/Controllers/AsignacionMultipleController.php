<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\EnvioAsignacion;
use App\Models\Vehiculo;
use App\Models\User;
use App\Services\AlmacenIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AsignacionMultipleController extends Controller
{
    /**
     * Mostrar interfaz de asignación múltiple por fecha
     */
    public function index(Request $request)
    {
        // Fecha seleccionada (por defecto hoy)
        $fechaSeleccionada = $request->get('fecha', now()->format('Y-m-d'));
        
        // Obtener envíos pendientes para esa fecha de entrega
        $enviosPendientes = Envio::with(['almacenDestino', 'productos'])
            ->where('estado', 'pendiente')
            ->whereDate('fecha_estimada_entrega', $fechaSeleccionada)
            ->orderBy('hora_estimada')
            ->get();
        
        \Log::info("📅 Fecha seleccionada: {$fechaSeleccionada}");
        \Log::info("📦 Envíos pendientes para esa fecha: " . $enviosPendientes->count());
        
        // Obtener transportistas disponibles
        $transportistas = User::where(function($query) {
                $query->where('tipo', 'transportista')
                      ->orWhere('role', 'transportista');
            })
            ->orderBy('name')
            ->get();
        
        // Obtener SOLO vehículos NO UTILIZADOS (disponibles)
        $vehiculos = Vehiculo::with(['tipoTransporte', 'tamanoVehiculo'])
            ->whereDoesntHave('asignaciones', function($query) {
                $query->whereHas('envio', function($q) {
                    $q->whereIn('estado', ['asignado', 'aceptado', 'en_transito']);
                });
            })
            ->orderBy('capacidad_carga', 'desc')
            ->get();
        
        // Obtener fechas con envíos pendientes para el selector
        $fechasDisponibles = Envio::where('estado', 'pendiente')
            ->whereNotNull('fecha_estimada_entrega')
            ->selectRaw('DATE(fecha_estimada_entrega) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->limit(30)
            ->get();
        
        return view('asignacion-multiple.index', compact(
            'enviosPendientes',
            'transportistas',
            'vehiculos',
            'fechaSeleccionada',
            'fechasDisponibles'
        ));
    }
    
    /**
     * Procesar asignación múltiple con validación de peso
     */
    public function asignar(Request $request)
    {
        try {
            $validated = $request->validate([
                'envios_ids' => 'required|array|min:1',
                'envios_ids.*' => 'required|exists:envios,id',
                'transportista_id' => 'required|exists:users,id',
                'vehiculo_id' => 'required|exists:vehiculos,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', '❌ Datos inválidos: ' . implode(', ', $e->validator->errors()->all()));
        }
        
        DB::beginTransaction();
        
        try {
            // Obtener vehículo y su capacidad
            $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);
            $capacidadMaxima = floatval($vehiculo->capacidad_carga ?? 1000);
            
            // Obtener transportista
            $transportista = User::where('id', $request->transportista_id)
                ->where(function($q) {
                    $q->where('tipo', 'transportista')
                      ->orWhere('role', 'transportista');
                })
                ->firstOrFail();
            
            // Asignar el transportista al vehículo si no lo tiene
            if (!$vehiculo->transportista_id || $vehiculo->transportista_id != $request->transportista_id) {
                $vehiculo->update(['transportista_id' => $request->transportista_id]);
                \Log::info("✅ Transportista {$transportista->name} (ID: {$request->transportista_id}) asignado al vehículo {$vehiculo->placa}");
            }
            
            // Calcular peso total de los envíos
            $envios = Envio::whereIn('id', $request->envios_ids)
                ->where('estado', 'pendiente')
                ->with('productos')
                ->get();
            
            if ($envios->isEmpty()) {
                DB::rollBack();
                return back()->with('error', '❌ No se encontraron envíos pendientes válidos con los IDs proporcionados.');
            }
            
            $pesoTotal = 0;
            $fechasDistintas = [];
            
            foreach ($envios as $envio) {
                $pesoTotal += floatval($envio->total_peso ?? 0);
                
                // Verificar que todos sean del mismo día
                if ($envio->fecha_estimada_entrega) {
                    $fecha = Carbon::parse($envio->fecha_estimada_entrega)->format('Y-m-d');
                    if (!in_array($fecha, $fechasDistintas)) {
                        $fechasDistintas[] = $fecha;
                    }
                }
            }
            
            // VALIDACIÓN: Todos deben ser del mismo día
            if (count($fechasDistintas) > 1) {
                DB::rollBack();
                return back()->with('error', '❌ ERROR: Solo se pueden asignar envíos del MISMO DÍA. Fechas encontradas: ' . implode(', ', $fechasDistintas));
            }
            
            // OPTIMIZAR ORDEN DE ALMACENES: Ordenar envíos por distancia desde la planta (nearest neighbor)
            $enviosOrdenados = $this->optimizarOrdenAlmacenes($envios);
            
            // VALIDACIÓN: No exceder capacidad del vehículo
            $porcentajeUso = ($pesoTotal / $capacidadMaxima) * 100;
            
            if ($pesoTotal > $capacidadMaxima) {
                DB::rollBack();
                
                $exceso = number_format($pesoTotal - $capacidadMaxima, 2);
                $pesoFormateado = number_format($pesoTotal, 2);
                $capacidadFormateada = number_format($capacidadMaxima, 0);
                $porcentajeFormateado = number_format($porcentajeUso, 1);
                
                return back()->with('error', 
                    "❌ SOBREPESO DETECTADO\n\n" .
                    "Peso Total: " . $pesoFormateado . " kg\n" .
                    "Capacidad Vehículo: " . $capacidadFormateada . " kg\n" .
                    "Exceso: " . $exceso . " kg (" . $porcentajeFormateado . "% de capacidad)\n\n" .
                    "⚠️ NO SE PUEDE REALIZAR EL ENVÍO. Reduce la cantidad de envíos o selecciona un vehículo con mayor capacidad."
                );
            }
            
            // Asignar cada envío en el orden optimizado
            $enviosAsignados = [];
            
            foreach ($enviosOrdenados as $envio) {
                // Actualizar o crear asignación
                // Si ya existe una asignación para este envío, la actualizamos
                // El transportista se obtiene a través del vehículo
                EnvioAsignacion::updateOrCreate(
                    ['envio_id' => $envio->id],
                    [
                        'vehiculo_id' => $request->vehiculo_id,
                        'fecha_asignacion' => now(),
                    ]
                );

                // Actualizar estado
                $envio->update([
                    'estado' => 'asignado',
                    'fecha_asignacion' => now(),
                ]);

                $enviosAsignados[] = $envio->codigo;
                
                // Notificar a sistema-almacen-PSIII sobre la asignación
                try {
                    $almacenService = new AlmacenIntegrationService();
                    $almacenService->notifyAsignacion($envio);
                } catch (\Exception $e) {
                    \Log::warning("No se pudo notificar asignación a almacenes para envío {$envio->codigo}: " . $e->getMessage());
                    // No fallar la asignación si la notificación falla
                }
                
                \Log::info("✅ Envío {$envio->codigo} asignado a {$transportista->name}");
            }
            
            // Crear ruta multi-entrega en el backend Node.js
            $rutaMultiEntrega = null;
            try {
                $nodeApiUrl = env('NODE_API_URL', 'http://localhost:3001/api');
                // Usar IDs en el orden optimizado
                $enviosIds = collect($enviosOrdenados)->pluck('id')->toArray();
                
                \Log::info("🛣️ Creando ruta multi-entrega para asignación múltiple con " . count($enviosIds) . " envíos");
                
                $response = \Illuminate\Support\Facades\Http::timeout(10)->post("{$nodeApiUrl}/rutas-entrega", [
                    'transportista_id' => $request->transportista_id,
                    'vehiculo_id' => $request->vehiculo_id,
                    'envios_ids' => $enviosIds,
                    'fecha' => $fechasDistintas[0] ?? now()->toDateString(),
                ]);

                if ($response->successful() && $response->json('success')) {
                    $rutaMultiEntrega = $response->json('ruta');
                    \Log::info("✅ Ruta multi-entrega creada: {$rutaMultiEntrega['codigo']} (ID: {$rutaMultiEntrega['id']})");
                    
                    // Actualizar los envíos para que apunten a la ruta (orden optimizado)
                    foreach ($enviosOrdenados as $envio) {
                        $envio->update(['ruta_entrega_id' => $rutaMultiEntrega['id']]);
                    }
                } else {
                    $error = $response->json('message') ?? 'Error desconocido';
                    \Log::warning("⚠️ No se pudo crear ruta multi-entrega: {$error}");
                    // Continuar sin ruta multi-entrega, los envíos ya están asignados
                }
            } catch (\Exception $e) {
                \Log::error("❌ Error al crear ruta multi-entrega: " . $e->getMessage());
                // Continuar sin ruta multi-entrega, los envíos ya están asignados
            }
            
            DB::commit();
            
            // Sincronizar con app móvil (si existe el endpoint)
            $this->sincronizarConApp($transportista->id, collect($enviosOrdenados));
            
            $numEnvios = count($enviosAsignados);
            $porcentajeFormateado = number_format($porcentajeUso, 1);
            $fechaAsignacion = isset($fechasDistintas[0]) ? $fechasDistintas[0] : 'N/A';
            $codigosEnvios = implode(', ', $enviosAsignados);
            $pesoFormateado = number_format($pesoTotal, 2);
            $capacidadFormateada = number_format($capacidadMaxima, 0);
            $transportistaNombre = $transportista->name;
            $vehiculoPlaca = $vehiculo->placa;
            
            $mensaje = "✅ ASIGNACIÓN MÚLTIPLE EXITOSA\n\n" .
                       "📦 " . $numEnvios . " envío(s) asignados\n" .
                       "👤 Transportista: " . $transportistaNombre . "\n" .
                       "🚛 Vehículo: " . $vehiculoPlaca . "\n" .
                       "⚖️ Peso Total: " . $pesoFormateado . " kg / " . $capacidadFormateada . " kg (" . $porcentajeFormateado . "%)\n" .
                       "📅 Fecha: " . $fechaAsignacion . "\n\n";
            
            if ($rutaMultiEntrega) {
                $mensaje .= "🛣️ RUTA MULTI-ENTREGA CREADA: {$rutaMultiEntrega['codigo']}\n\n";
            }
            
            $mensaje .= "Envíos: " . $codigosEnvios . "\n\n" .
                       "🔔 El transportista puede ver esta ruta en su aplicación móvil con todas las paradas, direcciones y checklists.";
            
            return back()->with('success', $mensaje);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("❌ Error en asignación múltiple: " . $e->getMessage());
            return back()->with('error', '❌ Error al asignar: ' . $e->getMessage());
        }
    }
    
    /**
     * Optimizar orden de almacenes usando algoritmo Nearest Neighbor
     * Ordena los envíos de forma que la ruta sea eficiente (menor distancia total)
     */
    private function optimizarOrdenAlmacenes($envios)
    {
        // Obtener coordenadas de la planta (origen)
        $planta = \App\Models\Almacen::where('es_planta', true)->first();
        if (!$planta) {
            // Si no hay planta, devolver en orden original
            return $envios->toArray();
        }
        
        $plantaLat = floatval($planta->latitud ?? -17.7833);
        $plantaLng = floatval($planta->longitud ?? -63.1821);
        
        // Cargar almacenes de destino con coordenadas
        $envios->load('almacenDestino');
        
        // Algoritmo Nearest Neighbor: empezar desde la planta, ir al más cercano, luego al siguiente más cercano...
        $ruta = [];
        $visitados = [];
        $posicionActual = ['lat' => $plantaLat, 'lng' => $plantaLng];
        $enviosArray = $envios->all(); // Obtener como array de modelos
        
        while (count($ruta) < count($enviosArray)) {
            $masCercano = null;
            $menorDistancia = PHP_INT_MAX;
            $indiceMasCercano = -1;
            
            // Buscar el almacén más cercano no visitado
            foreach ($enviosArray as $indice => $envio) {
                if (in_array($indice, $visitados)) {
                    continue;
                }
                
                $almacen = $envio->almacenDestino;
                if (!$almacen || !$almacen->latitud || !$almacen->longitud) {
                    continue;
                }
                
                $destinoLat = floatval($almacen->latitud);
                $destinoLng = floatval($almacen->longitud);
                
                // Calcular distancia euclidiana (suficiente para optimización)
                $distancia = sqrt(
                    pow($destinoLat - $posicionActual['lat'], 2) + 
                    pow($destinoLng - $posicionActual['lng'], 2)
                );
                
                if ($distancia < $menorDistancia) {
                    $menorDistancia = $distancia;
                    $masCercano = $envio;
                    $indiceMasCercano = $indice;
                }
            }
            
            // Agregar el más cercano a la ruta
            if ($masCercano && $indiceMasCercano >= 0) {
                $ruta[] = $masCercano;
                $visitados[] = $indiceMasCercano;
                
                // Actualizar posición actual al almacén visitado
                $almacen = $masCercano->almacenDestino;
                if ($almacen) {
                    $posicionActual = [
                        'lat' => floatval($almacen->latitud ?? $plantaLat),
                        'lng' => floatval($almacen->longitud ?? $plantaLng)
                    ];
                }
            } else {
                // Si no se encontró más cercano, agregar los restantes en orden
                foreach ($enviosArray as $indice => $envio) {
                    if (!in_array($indice, $visitados)) {
                        $ruta[] = $envio;
                        $visitados[] = $indice;
                    }
                }
                break;
            }
        }
        
        // $ruta ya contiene los modelos Eloquent en orden optimizado
        $enviosOrdenados = $ruta;
        
        \Log::info("🛣️ Orden optimizado de almacenes: " . implode(' -> ', array_map(function($e) {
            return $e->almacenDestino->nombre ?? 'N/A';
        }, $enviosOrdenados)));
        
        return $enviosOrdenados;
    }
    
    /**
     * Sincronizar con aplicación móvil
     */
    private function sincronizarConApp($transportistaId, $envios)
    {
        try {
            // Aquí se puede implementar una notificación push o webhook
            // a la aplicación móvil del transportista
            
            \Log::info("📱 Sincronizando {$envios->count()} envíos con app del transportista {$transportistaId}");
            
            // La app consultará el endpoint GET /api/transportista/{id}/envios
            // que ya existe y filtra por transportista
            
        } catch (\Exception $e) {
            \Log::warning("⚠️ No se pudo sincronizar con app: " . $e->getMessage());
        }
    }
}

