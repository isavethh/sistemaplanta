<?php

namespace App\Http\Controllers;

use App\Models\PropuestaVehiculo;
use App\Models\Envio;
use App\Services\PropuestaVehiculosService;
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
        $propuesta = PropuestaVehiculo::with(['envio.almacenDestino', 'envio.productos.producto', 'aprobadoPor'])
            ->findOrFail($id);

        return view('propuestas-vehiculos.show', compact('propuesta'));
    }
}
