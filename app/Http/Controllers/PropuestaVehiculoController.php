<?php

namespace App\Http\Controllers;

use App\Models\PropuestaVehiculo;
use App\Models\Envio;
use App\Services\PropuestaVehiculosService;
use App\Services\CubicajeInteligenteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PropuestaVehiculoController extends Controller
{
    /**
     * Mostrar lista de propuestas de vehículos
     */
    public function index(Request $request)
    {
        // Log para debugging
        Log::info('🔍 [PropuestaVehiculoController] Listando propuestas', [
            'total_db' => PropuestaVehiculo::count(),
            'filtros' => $request->all(),
        ]);

        // Primero intentar obtener propuestas guardadas
        $query = PropuestaVehiculo::with(['envio.almacenDestino', 'aprobadoPor'])
            ->orderBy('fecha_propuesta', 'desc');

        // Filtro por estado
        if ($request->has('estado') && $request->estado !== '') {
            $query->where('estado', $request->estado);
        }

        // Filtro por código de envío
        if ($request->has('codigo') && $request->codigo !== '') {
            $query->where('codigo_envio', 'like', '%' . $request->codigo . '%');
        }

        $propuestasGuardadas = $query->get();
        $totalGuardadas = $propuestasGuardadas->count();

        // Si no hay propuestas guardadas o son muy pocas, buscar envíos de Trazabilidad y generar propuestas dinámicamente
        if ($totalGuardadas == 0) {
            Log::info('⚠️ [PropuestaVehiculoController] No hay propuestas guardadas, generando desde envíos de Trazabilidad');
            
            $propuestaService = new PropuestaVehiculosService();
            $propuestasDinamicas = collect();

            // Buscar envíos de Trazabilidad que no tienen propuesta guardada
            $enviosTrazabilidad = Envio::with(['almacenDestino', 'productos.producto', 'productos.tipoEmpaque'])
                ->where(function($query) {
                    $query->where('estado', 'pendiente_aprobacion_trazabilidad')
                        ->orWhereRaw("observaciones LIKE '%TRAZABILIDAD%'")
                        ->orWhereRaw("observaciones LIKE '%trazabilidad%'")
                        ->orWhereRaw("observaciones LIKE '%Trazabilidad%'");
                })
                ->whereDoesntHave('propuestaVehiculo')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('📦 [PropuestaVehiculoController] Envíos de Trazabilidad encontrados', [
                'count' => $enviosTrazabilidad->count(),
            ]);

            foreach ($enviosTrazabilidad as $envio) {
                try {
                    $propuestaData = $propuestaService->calcularPropuestaVehiculos($envio);
                    
                    // Crear un objeto temporal similar a PropuestaVehiculo para mostrarlo
                    $propuestaTemporal = new PropuestaVehiculo([
                        'envio_id' => $envio->id,
                        'codigo_envio' => $envio->codigo,
                        'propuesta_data' => $propuestaData,
                        'estado' => $envio->estado === 'pendiente_aprobacion_trazabilidad' ? 'pendiente' : 'pendiente',
                        'fecha_propuesta' => $envio->created_at ?? now(),
                    ]);
                    
                    // Asignar relación para que funcione el acceso a envio
                    $propuestaTemporal->setRelation('envio', $envio);
                    $propuestasDinamicas->push($propuestaTemporal);

                    // Intentar guardar en la base de datos para futuras consultas
                    try {
                        PropuestaVehiculo::updateOrCreate(
                            ['envio_id' => $envio->id],
                            [
                                'codigo_envio' => $envio->codigo,
                                'propuesta_data' => $propuestaData,
                                'estado' => 'pendiente',
                                'fecha_propuesta' => $envio->created_at ?? now(),
                            ]
                        );
                    } catch (\Exception $saveException) {
                        Log::warning('⚠️ No se pudo guardar propuesta temporal', [
                            'envio_id' => $envio->id,
                            'error' => $saveException->getMessage(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('⚠️ Error generando propuesta dinámica', [
                        'envio_id' => $envio->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Combinar propuestas guardadas y dinámicas
            $propuestas = $propuestasGuardadas->concat($propuestasDinamicas)->sortByDesc('fecha_propuesta')->values();
            
            // Aplicar paginación manual
            $page = $request->get('page', 1);
            $perPage = 20;
            $total = $propuestas->count();
            $offset = ($page - 1) * $perPage;
            $items = $propuestas->slice($offset, $perPage)->values();
            
            // Crear objeto de paginación manual
            $propuestas = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            // Si hay propuestas guardadas, usar paginación normal
            $propuestas = $query->paginate(20);
        }

        Log::info('✅ [PropuestaVehiculoController] Propuestas encontradas', [
            'count' => $propuestas->total(),
            'current_page' => $propuestas->currentPage(),
        ]);

        // Estadísticas (incluir dinámicas si es necesario)
        $stats = [
            'total' => PropuestaVehiculo::count() + ($totalGuardadas == 0 ? $propuestas->total() - PropuestaVehiculo::count() : 0),
            'aprobadas' => PropuestaVehiculo::where('estado', 'aprobada')->count(),
            'rechazadas' => PropuestaVehiculo::where('estado', 'rechazada')->count(),
            'pendientes' => PropuestaVehiculo::where('estado', 'pendiente')->count() + ($totalGuardadas == 0 ? max(0, $propuestas->total() - PropuestaVehiculo::count()) : 0),
        ];

        return view('propuestas-vehiculos.index', compact('propuestas', 'stats'));
    }

    /**
     * Mostrar detalles de una propuesta
     */
    public function show($id)
    {
        $propuesta = PropuestaVehiculo::with(['envio.almacenDestino', 'envio.productos.producto', 'envio.productos.tipoEmpaque', 'aprobadoPor'])
            ->findOrFail($id);

        if (!$propuesta->envio) {
            abort(404, 'El envío asociado a esta propuesta no existe');
        }

        $envio = $propuesta->envio;
        
        // Obtener datos de la propuesta (recalcular si no tiene)
        if ($propuesta->propuesta_data && isset($propuesta->propuesta_data['totales'])) {
            $propuestaData = $propuesta->propuesta_data;
        } else {
            $propuestaService = new PropuestaVehiculosService();
            $propuestaData = $propuestaService->calcularPropuestaVehiculos($envio);
        }

        // Convertir al formato que espera la vista de trazabilidad (CubicajeInteligenteService)
        $cubicaje = $this->formatearCubicajeParaVista($propuestaData, $envio);

        // Crear objeto similar a PedidoAlmacen para la vista
        $pedido = (object)[
            'id' => $propuesta->id,
            'codigo' => $propuesta->codigo_envio,
            'almacen' => $envio->almacenDestino,
            'propietario' => null,
            'fecha_requerida' => $envio->fecha_estimada_entrega ?? now(),
            'hora_requerida' => $envio->hora_estimada ?? null,
            'estado' => $propuesta->estado === 'pendiente' ? 'propuesta_enviada' : ($propuesta->estado === 'aprobada' ? 'propuesta_aceptada' : 'propuesta_rechazada'),
            'direccion_completa' => $envio->almacenDestino->direccion_completa ?? null,
            'latitud' => $envio->almacenDestino->latitud ?? null,
            'longitud' => $envio->almacenDestino->longitud ?? null,
            'productos' => $envio->productos,
        ];

        return view('trazabilidad.ver-propuesta', compact('pedido', 'cubicaje'));
    }

    /**
     * Formatear propuesta al formato que espera la vista (CubicajeInteligenteService)
     */
    private function formatearCubicajeParaVista($propuestaData, $envio)
    {
        $formateado = [
            'totales' => [
                'peso_kg' => $propuestaData['totales']['peso_kg'] ?? 0,
                'volumen_m3' => $propuestaData['totales']['volumen_m3'] ?? 0,
                'cantidad_productos' => $propuestaData['totales']['cantidad_productos'] ?? 0,
            ],
        ];

        // Tipo de transporte
        if (isset($propuestaData['tipo_transporte_requerido'])) {
            $formateado['tipo_transporte'] = $propuestaData['tipo_transporte_requerido'];
        }

        // Vehículo recomendado (usar el primero de los vehículos propuestos)
        if (isset($propuestaData['vehiculos_propuestos']) && count($propuestaData['vehiculos_propuestos']) > 0) {
            $vehiculoProp = $propuestaData['vehiculos_propuestos'][0];
            if (isset($vehiculoProp['vehiculo']) && $vehiculoProp['vehiculo']) {
                $vehiculo = $vehiculoProp['vehiculo'];
                $formateado['vehiculo_recomendado'] = [
                    'vehiculo' => $vehiculo,
                    'capacidad_carga_kg' => $vehiculo->capacidad_carga ?? ($vehiculoProp['peso_asignado_kg'] * 1.2),
                    'capacidad_volumen_m3' => $vehiculo->capacidad_volumen ?? 0,
                    'porcentaje_uso' => $vehiculoProp['porcentaje_uso'] ?? 0,
                    'tipo_transporte' => $vehiculoProp['tipo_transporte'] ?? null,
                    'tamano' => $vehiculoProp['tamano'] ?? null,
                    'transportista' => isset($vehiculo->transportista) ? $vehiculo->transportista : null,
                ];
            }
        }

        // Capacidad requerida si no hay vehículo
        if (!isset($formateado['vehiculo_recomendado'])) {
            $formateado['capacidad_requerida'] = [
                'peso_minimo_kg' => ($formateado['totales']['peso_kg'] * 1.2),
            ];
        }

        // Recomendación de empaque
        $formateado['recomendacion_empaque'] = [];
        foreach ($envio->productos as $producto) {
            if ($producto->tipoEmpaque) {
                $formateado['recomendacion_empaque'][] = [
                    'producto' => $producto->producto_nombre,
                    'tipo_empaque' => $producto->tipoEmpaque,
                    'cantidad_cajas' => max(1, ceil($producto->cantidad / 10)),
                    'cantidad_producto' => $producto->cantidad,
                    'items_por_caja' => 10,
                    'dimensiones_caja' => [
                        'largo_cm' => 50,
                        'ancho_cm' => 40,
                        'alto_cm' => 30,
                    ],
                ];
            }
        }

        return $formateado;
    }
}
