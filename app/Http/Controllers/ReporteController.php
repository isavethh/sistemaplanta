<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Página principal de reportes
     */
    public function index()
    {
        return view('reportes.index');
    }

    // ========================================================================
    // REPORTE 1: OPERACIONES DE TRANSPORTE
    // ========================================================================
    
    public function operaciones(Request $request)
    {
        $filtros = $this->aplicarFiltrosFecha($request);
        
        // Estadísticas generales del período
        $estadisticas = $this->obtenerEstadisticasOperaciones($filtros);
        
        // Detalle de envíos
        $envios = $this->obtenerEnviosDetallados($filtros, $request);
        
        // Para los filtros
        $almacenes = DB::table('almacenes')->orderBy('nombre')->get();
        $transportistas = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'transportista')
            ->select('users.*')
            ->orderBy('name')
            ->get();
        
        return view('reportes.operaciones', compact(
            'estadisticas', 'envios', 'almacenes', 'transportistas', 'filtros'
        ));
    }

    public function operacionesPdf(Request $request)
    {
        $filtros = $this->aplicarFiltrosFecha($request);
        $estadisticas = $this->obtenerEstadisticasOperaciones($filtros);
        $envios = $this->obtenerEnviosDetallados($filtros, $request, false);

        $pdf = Pdf::loadView('reportes.pdf.operaciones', compact(
            'estadisticas', 'envios', 'filtros'
        ));
        
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('reporte-operaciones-' . now()->format('Y-m-d') . '.pdf');
    }

    public function operacionesCsv(Request $request)
    {
        $filtros = $this->aplicarFiltrosFecha($request);
        $envios = $this->obtenerEnviosDetallados($filtros, $request, false);
        
        $filename = 'reporte-operaciones-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ];

        $callback = function() use ($envios) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Encabezados
            fputcsv($file, [
                'Código', 'Fecha Creación', 'Estado', 'Almacén Destino', 
                'Transportista', 'Vehículo', 'Cantidad', 'Peso (kg)', 
                'Precio Total (Bs)', 'Fecha Entrega'
            ]);
            
            foreach ($envios as $envio) {
                fputcsv($file, [
                    $envio->codigo ?? '',
                    $envio->fecha_creacion ?? '',
                    $this->traducirEstado($envio->estado ?? 'pendiente'),
                    $envio->almacen_nombre ?? 'N/A',
                    $envio->transportista_nombre ?? 'Sin asignar',
                    $envio->vehiculo_placa ?? 'N/A',
                    $envio->total_cantidad ?? 0,
                    number_format($envio->total_peso ?? 0, 2),
                    number_format($envio->total_precio ?? 0, 2),
                    $envio->fecha_entrega ?? 'Pendiente'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ========================================================================
    // REPORTE 2: NOTA DE ENTREGA (DOCUMENTO LEGAL)
    // ========================================================================
    
    public function notaEntrega(Request $request)
    {
        $query = DB::table('envios as e')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->leftJoin('users as t', 'v.transportista_id', '=', 't.id')
            ->where('e.estado', 'entregado')
            ->select(
                'e.*',
                'a.nombre as almacen_nombre',
                'a.direccion_completa as almacen_direccion',
                't.name as transportista_nombre'
            )
            ->orderBy('e.fecha_entrega', 'desc');

        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('e.codigo', 'ILIKE', '%' . $request->buscar . '%')
                  ->orWhere('a.nombre', 'ILIKE', '%' . $request->buscar . '%');
            });
        }

        $enviosEntregados = $query->paginate(15);

        return view('reportes.nota-entrega', compact('enviosEntregados'));
    }

    public function notaEntregaPdf($envioId)
    {
        $envio = DB::table('envios as e')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->leftJoin('users as t', 'v.transportista_id', '=', 't.id')
            ->where('e.id', $envioId)
            ->select(
                'e.*',
                'a.nombre as almacen_nombre',
                'a.direccion_completa as almacen_direccion',
                'a.latitud as almacen_lat',
                'a.longitud as almacen_lng',
                't.name as transportista_nombre',
                't.email as transportista_email',
                't.telefono as transportista_telefono',
                'v.placa as vehiculo_placa'
            )
            ->first();

        if (!$envio) {
            return redirect()->back()->with('error', 'Envío no encontrado');
        }

        $productos = DB::table('envio_productos')->where('envio_id', $envioId)->get();

        // Obtener planta origen
        $planta = DB::table('almacenes')->where('es_planta', true)->first();

        // Obtener checklist de compromiso (salida) desde Node.js
        $checklistSalida = null;
        $evidenciasChecklist = [];
        $firmaTransportista = null;
        try {
            $nodeApiUrl = env('NODE_API_URL', 'http://localhost:3000');
            
            // PRIMERO: Buscar checklist por envio_id directamente (envíos normales)
            $response = \Http::timeout(5)->get("{$nodeApiUrl}/api/rutas-entrega/checklists", [
                'envio_id' => $envioId,
                'tipo' => 'salida'
            ]);
            
            if ($response->successful()) {
                $checklists = $response->json();
                $checklistSalida = collect($checklists['checklists'] ?? [])->first();
            }
            
            // Si no se encontró, buscar si el envío tiene una ruta asociada (rutas múltiples)
            if (!$checklistSalida) {
                $rutaEntrega = DB::table('rutas_entrega')
                    ->whereJsonContains('envio_ids', (string)$envioId)
                    ->orWhere('envio_ids', 'LIKE', '%"' . $envioId . '"%')
                    ->first();
                
                if ($rutaEntrega) {
                    // Obtener checklist desde Node.js por ruta
                    $response = \Http::timeout(5)->get("{$nodeApiUrl}/api/rutas-entrega/{$rutaEntrega->id}/checklists");
                    
                    if ($response->successful()) {
                        $checklists = $response->json();
                        // Buscar checklist de salida
                        $checklistSalida = collect($checklists['checklists'] ?? [])
                            ->where('tipo', 'salida')
                            ->first();
                    }
                }
            }
            
            // Procesar checklist si se encontró
            if ($checklistSalida && isset($checklistSalida['datos'])) {
                $datosChecklist = is_string($checklistSalida['datos']) 
                    ? json_decode($checklistSalida['datos'], true) 
                    : $checklistSalida['datos'];
                
                // Obtener firma del checklist
                $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                
                // Verificar items no marcados
                $itemsNoMarcados = [];
                $templateItems = [
                    'documentos_carga' => 'Documentos de carga completos',
                    'guias_remision' => 'Guías de remisión disponibles',
                    'carga_verificada' => 'Carga verificada y contada',
                    'carga_asegurada' => 'Carga asegurada correctamente',
                    'embalaje_correcto' => 'Embalaje en buen estado',
                    'combustible_ok' => 'Combustible suficiente',
                    'llantas_ok' => 'Llantas en buen estado',
                    'luces_ok' => 'Luces funcionando',
                    'frenos_ok' => 'Frenos funcionando',
                    'documentos_vehiculo' => 'Documentos del vehículo',
                    'licencia_conductor' => 'Licencia de conducir vigente',
                    'epp_completo' => 'EPP completo (si aplica)'
                ];
                
                foreach ($templateItems as $itemId => $itemLabel) {
                    if (!isset($datosChecklist[$itemId]) || !$datosChecklist[$itemId]) {
                        $itemsNoMarcados[] = $itemLabel;
                    }
                }
                
                // Obtener evidencias (fotos) para items no marcados
                if (!empty($itemsNoMarcados)) {
                    try {
                        // Intentar obtener evidencias por envio_id (envíos normales)
                        $evidenciasResponse = \Http::timeout(5)->get(
                            "{$nodeApiUrl}/api/rutas-entrega/evidencias",
                            ['envio_id' => $envioId, 'tipo' => 'checklist_salida']
                        );
                        
                        if ($evidenciasResponse->successful()) {
                            $evidenciasData = $evidenciasResponse->json();
                            $evidenciasChecklist = $evidenciasData['evidencias'] ?? [];
                        }
                        
                        // Si no se encontraron por envio_id, intentar por ruta_parada_id (rutas múltiples)
                        if (empty($evidenciasChecklist) && isset($checklistSalida['ruta_parada_id'])) {
                            $evidenciasResponse = \Http::timeout(5)->get(
                                "{$nodeApiUrl}/api/rutas-entrega/evidencias",
                                ['parada_id' => $checklistSalida['ruta_parada_id'], 'tipo' => 'checklist_salida']
                            );
                            if ($evidenciasResponse->successful()) {
                                $evidenciasData = $evidenciasResponse->json();
                                $evidenciasChecklist = $evidenciasData['evidencias'] ?? [];
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Error obteniendo evidencias: " . $e->getMessage());
                    }
                }
                
                $checklistSalida['items_no_marcados'] = $itemsNoMarcados;
            }
        } catch (\Exception $e) {
            \Log::warning("Error obteniendo checklist: " . $e->getMessage());
        }

        $pdf = Pdf::loadView('reportes.pdf.nota-entrega', compact('envio', 'productos', 'planta', 'checklistSalida', 'evidenciasChecklist', 'firmaTransportista'));
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('nota-entrega-' . $envio->codigo . '.pdf');
    }

    public function notaEntregaHtml($envioId)
    {
        $envio = DB::table('envios as e')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->leftJoin('users as t', 'v.transportista_id', '=', 't.id')
            ->where('e.id', $envioId)
            ->select(
                'e.*',
                'a.nombre as almacen_nombre',
                'a.direccion_completa as almacen_direccion',
                't.name as transportista_nombre',
                't.email as transportista_email',
                't.telefono as transportista_telefono',
                'v.placa as vehiculo_placa'
            )
            ->first();

        $productos = DB::table('envio_productos')->where('envio_id', $envioId)->get();
        $planta = DB::table('almacenes')->where('es_planta', true)->first();

        // Obtener checklist de compromiso (salida) desde Node.js
        $checklistSalida = null;
        $evidenciasChecklist = [];
        $firmaTransportista = null;
        try {
            $nodeApiUrl = env('NODE_API_URL', 'http://localhost:3000');
            
            // PRIMERO: Buscar checklist por envio_id directamente (envíos normales)
            $response = \Http::timeout(5)->get("{$nodeApiUrl}/api/rutas-entrega/checklists", [
                'envio_id' => $envioId,
                'tipo' => 'salida'
            ]);
            
            if ($response->successful()) {
                $checklists = $response->json();
                $checklistSalida = collect($checklists['checklists'] ?? [])->first();
            }
            
            // Si no se encontró, buscar si el envío tiene una ruta asociada (rutas múltiples)
            if (!$checklistSalida) {
                $rutaEntrega = DB::table('rutas_entrega')
                    ->whereJsonContains('envio_ids', (string)$envioId)
                    ->orWhere('envio_ids', 'LIKE', '%"' . $envioId . '"%')
                    ->first();
                
                if ($rutaEntrega) {
                    // Obtener checklist desde Node.js por ruta
                    $response = \Http::timeout(5)->get("{$nodeApiUrl}/api/rutas-entrega/{$rutaEntrega->id}/checklists");
                    
                    if ($response->successful()) {
                        $checklists = $response->json();
                        // Buscar checklist de salida
                        $checklistSalida = collect($checklists['checklists'] ?? [])
                            ->where('tipo', 'salida')
                            ->first();
                    }
                }
            }
            
            // Procesar checklist si se encontró
            if ($checklistSalida && isset($checklistSalida['datos'])) {
                $datosChecklist = is_string($checklistSalida['datos']) 
                    ? json_decode($checklistSalida['datos'], true) 
                    : $checklistSalida['datos'];
                
                // Obtener firma del checklist
                $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                
                // Verificar items no marcados
                $itemsNoMarcados = [];
                $templateItems = [
                    'documentos_carga' => 'Documentos de carga completos',
                    'guias_remision' => 'Guías de remisión disponibles',
                    'carga_verificada' => 'Carga verificada y contada',
                    'carga_asegurada' => 'Carga asegurada correctamente',
                    'embalaje_correcto' => 'Embalaje en buen estado',
                    'combustible_ok' => 'Combustible suficiente',
                    'llantas_ok' => 'Llantas en buen estado',
                    'luces_ok' => 'Luces funcionando',
                    'frenos_ok' => 'Frenos funcionando',
                    'documentos_vehiculo' => 'Documentos del vehículo',
                    'licencia_conductor' => 'Licencia de conducir vigente',
                    'epp_completo' => 'EPP completo (si aplica)'
                ];
                
                foreach ($templateItems as $itemId => $itemLabel) {
                    if (!isset($datosChecklist[$itemId]) || !$datosChecklist[$itemId]) {
                        $itemsNoMarcados[] = $itemLabel;
                    }
                }
                
                // Obtener evidencias (fotos) para items no marcados
                if (!empty($itemsNoMarcados)) {
                    try {
                        // Intentar obtener evidencias por envio_id (envíos normales)
                        $evidenciasResponse = \Http::timeout(5)->get(
                            "{$nodeApiUrl}/api/rutas-entrega/evidencias",
                            ['envio_id' => $envioId, 'tipo' => 'checklist_salida']
                        );
                        
                        if ($evidenciasResponse->successful()) {
                            $evidenciasData = $evidenciasResponse->json();
                            $evidenciasChecklist = $evidenciasData['evidencias'] ?? [];
                        }
                        
                        // Si no se encontraron por envio_id, intentar por ruta_parada_id (rutas múltiples)
                        if (empty($evidenciasChecklist) && isset($checklistSalida['ruta_parada_id'])) {
                            $evidenciasResponse = \Http::timeout(5)->get(
                                "{$nodeApiUrl}/api/rutas-entrega/evidencias",
                                ['parada_id' => $checklistSalida['ruta_parada_id'], 'tipo' => 'checklist_salida']
                            );
                            if ($evidenciasResponse->successful()) {
                                $evidenciasData = $evidenciasResponse->json();
                                $evidenciasChecklist = $evidenciasData['evidencias'] ?? [];
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Error obteniendo evidencias: " . $e->getMessage());
                    }
                }
                
                $checklistSalida['items_no_marcados'] = $itemsNoMarcados;
            }
        } catch (\Exception $e) {
            \Log::warning("Error obteniendo checklist: " . $e->getMessage());
        }

        return view('reportes.nota-entrega-vista', compact('envio', 'productos', 'planta', 'checklistSalida', 'evidenciasChecklist', 'firmaTransportista'));
    }

    // ========================================================================
    // REPORTE 3: INCIDENTES DE TRANSPORTE
    // ========================================================================
    
    public function incidentes(Request $request)
    {
        $filtros = $this->aplicarFiltrosFecha($request);
        
        // Ajustar fechas para incluir todo el día
        $fechaInicio = Carbon::parse($filtros['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::parse($filtros['fecha_fin'])->endOfDay();
        
        // Estadísticas de incidentes
        $estadisticas = $this->obtenerEstadisticasIncidentes($filtros);
        
        // Distribución por tipo
        $porTipo = DB::table('incidentes')
            ->select('tipo_incidente', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_reporte', [$fechaInicio, $fechaFin])
            ->groupBy('tipo_incidente')
            ->orderByDesc('total')
            ->get();

        // Listado de incidentes
        $query = DB::table('incidentes as i')
            ->leftJoin('envios as e', 'i.envio_id', '=', 'e.id')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->leftJoin('users as t', 'v.transportista_id', '=', 't.id')
            ->whereBetween('i.fecha_reporte', [$fechaInicio, $fechaFin])
            ->select(
                'i.*',
                'e.codigo as envio_codigo',
                'a.nombre as almacen_nombre',
                't.name as transportista_nombre'
            )
            ->orderBy('i.fecha_reporte', 'desc');

        if ($request->filled('tipo')) {
            $query->where('i.tipo_incidente', $request->tipo);
        }

        if ($request->filled('estado')) {
            $query->where('i.estado', $request->estado);
        }

        $incidentes = $query->paginate(15);

        // Tipos disponibles
        $tiposIncidente = DB::table('incidentes')
            ->select('tipo_incidente')
            ->distinct()
            ->orderBy('tipo_incidente')
            ->pluck('tipo_incidente');

        return view('reportes.incidentes', compact(
            'estadisticas', 'porTipo', 'incidentes', 'tiposIncidente', 'filtros'
        ));
    }

    public function incidentesPdf(Request $request)
    {
        $filtros = $this->aplicarFiltrosFecha($request);
        
        // Ajustar fechas para incluir todo el día
        $fechaInicio = Carbon::parse($filtros['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::parse($filtros['fecha_fin'])->endOfDay();
        
        $estadisticas = $this->obtenerEstadisticasIncidentes($filtros);
        
        $porTipo = DB::table('incidentes')
            ->select('tipo_incidente', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_reporte', [$fechaInicio, $fechaFin])
            ->groupBy('tipo_incidente')
            ->orderByDesc('total')
            ->get();

        $incidentes = DB::table('incidentes as i')
            ->leftJoin('envios as e', 'i.envio_id', '=', 'e.id')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->leftJoin('users as t', 'v.transportista_id', '=', 't.id')
            ->whereBetween('i.fecha_reporte', [$fechaInicio, $fechaFin])
            ->select('i.*', 'e.codigo as envio_codigo', 'a.nombre as almacen_nombre', 't.name as transportista_nombre')
            ->orderBy('i.fecha_reporte', 'desc')
            ->get();

        $pdf = Pdf::loadView('reportes.pdf.incidentes', compact(
            'estadisticas', 'porTipo', 'incidentes', 'filtros'
        ));
        
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('reporte-incidentes-' . now()->format('Y-m-d') . '.pdf');
    }

    public function incidentesCsv(Request $request)
    {
        $filtros = $this->aplicarFiltrosFecha($request);

        // Ajustar fechas para incluir todo el día
        $fechaInicio = Carbon::parse($filtros['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::parse($filtros['fecha_fin'])->endOfDay();

        $incidentes = DB::table('incidentes as i')
            ->leftJoin('envios as e', 'i.envio_id', '=', 'e.id')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->leftJoin('users as t', 'v.transportista_id', '=', 't.id')
            ->whereBetween('i.fecha_reporte', [$fechaInicio, $fechaFin])
            ->select('i.*', 'e.codigo as envio_codigo', 'a.nombre as almacen_nombre', 't.name as transportista_nombre')
            ->orderBy('i.fecha_reporte', 'desc')
            ->get();

        $filename = 'reporte-incidentes-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ];

        $callback = function() use ($incidentes) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, [
                'ID', 'Fecha Reporte', 'Tipo Incidente', 'Estado', 
                'Código Envío', 'Almacén', 'Transportista', 'Descripción',
                'Fecha Resolución', 'Notas Resolución'
            ]);
            
            foreach ($incidentes as $inc) {
                fputcsv($file, [
                    $inc->id,
                    $inc->fecha_reporte,
                    $inc->tipo_incidente,
                    $this->traducirEstado($inc->estado),
                    $inc->envio_codigo ?? 'N/A',
                    $inc->almacen_nombre ?? 'N/A',
                    $inc->transportista_nombre ?? 'N/A',
                    $inc->descripcion,
                    $inc->fecha_resolucion ?? 'Pendiente',
                    $inc->notas_resolucion ?? ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ========================================================================
    // REPORTE 4: PRODUCTIVIDAD DE TRANSPORTISTAS
    // ========================================================================
    
    public function productividad(Request $request)
    {
        $filtros = $this->aplicarFiltrosFecha($request);
        
        \Log::info("📊 [Productividad] Filtros aplicados: " . json_encode($filtros));
        
        // Obtener todos los transportistas (por rol Spatie o por tipo/role)
        $transportistasIds = DB::table('users as u')
            ->leftJoin('model_has_roles', 'u.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where(function($query) {
                $query->where('roles.name', 'transportista')
                      ->orWhere('u.tipo', 'transportista')
                      ->orWhere('u.role', 'transportista');
            })
            ->select('u.id', 'u.name', 'u.email')
            ->distinct()
            ->get();
        
        \Log::info("👥 [Productividad] Transportistas encontrados: " . $transportistasIds->count());
        
        // Si no hay transportistas, retornar vacío
        if ($transportistasIds->isEmpty()) {
            \Log::warning("⚠️ [Productividad] No se encontraron transportistas en el sistema");
            $transportistas = collect([]);
        } else {
            // Para cada transportista, obtener sus estadísticas
            $transportistas = $transportistasIds->map(function($user) use ($filtros) {
                // Obtener vehículos del transportista
                $vehiculosIds = DB::table('vehiculos')
                    ->where('transportista_id', $user->id)
                    ->pluck('id');
                
                \Log::info("🚛 [Productividad] Transportista {$user->name} (ID: {$user->id}) tiene {$vehiculosIds->count()} vehículos");
                
                if ($vehiculosIds->isEmpty()) {
                    // Si no tiene vehículos, retornar con valores en 0 (pero mostrar el transportista)
                    \Log::info("⚠️ [Productividad] Transportista {$user->name} no tiene vehículos asignados");
                    return (object)[
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'total_envios' => 0,
                        'entregas_completadas' => 0,
                        'en_transito' => 0,
                        'total_peso_transportado' => 0,
                        'total_items_transportados' => 0,
                        'tasa_efectividad' => 0,
                    ];
                }
                
                // Obtener envíos asignados a los vehículos del transportista en el período
                $envios = DB::table('envios as e')
                    ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                    ->whereIn('ea.vehiculo_id', $vehiculosIds)
                    ->whereBetween('e.fecha_creacion', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
                    ->select(
                        'e.id',
                        'e.estado',
                        'e.total_peso',
                        'e.total_cantidad'
                    )
                    ->get();
                
                \Log::info("📦 [Productividad] Transportista {$user->name} tiene {$envios->count()} envíos en el período");
                
                $totalEnvios = $envios->count();
                $entregasCompletadas = $envios->where('estado', 'entregado')->count();
                $enTransito = $envios->where('estado', 'en_transito')->count();
                $totalPeso = $envios->sum('total_peso') ?? 0;
                $totalItems = $envios->sum('total_cantidad') ?? 0;
                
                $tasaEfectividad = $totalEnvios > 0 
                    ? round(($entregasCompletadas / $totalEnvios) * 100, 1) 
                    : 0;
                
                return (object)[
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_envios' => $totalEnvios,
                    'entregas_completadas' => $entregasCompletadas,
                    'en_transito' => $enTransito,
                    'total_peso_transportado' => $totalPeso,
                    'total_items_transportados' => $totalItems,
                    'tasa_efectividad' => $tasaEfectividad,
                ];
            })->sortByDesc('total_envios')->values();
            
            \Log::info("✅ [Productividad] Total transportistas procesados: " . $transportistas->count());
        }

        // Incidentes por transportista
        $incidentesPorTransportista = [];
        if ($transportistas->isNotEmpty()) {
            foreach ($transportistas as $t) {
                // Obtener vehículos del transportista
                $vehiculosIds = DB::table('vehiculos')
                    ->where('transportista_id', $t->id)
                    ->pluck('id');
                
                if ($vehiculosIds->isNotEmpty()) {
                    // Obtener envíos asignados a esos vehículos
                    $enviosIds = DB::table('envio_asignaciones')
                        ->whereIn('vehiculo_id', $vehiculosIds)
                        ->pluck('envio_id');
                    
                    if ($enviosIds->isNotEmpty()) {
                        // Contar incidentes de esos envíos en el período
                        $totalIncidentes = DB::table('incidentes')
                            ->whereIn('envio_id', $enviosIds)
                            ->whereBetween('fecha_reporte', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
                            ->count();
                        
                        $incidentesPorTransportista[$t->id] = $totalIncidentes;
                    } else {
                        $incidentesPorTransportista[$t->id] = 0;
                    }
                } else {
                    $incidentesPorTransportista[$t->id] = 0;
                }
            }
        }

        // Agregar incidentes a cada transportista
        foreach ($transportistas as $t) {
            $t->total_incidentes = $incidentesPorTransportista[$t->id] ?? 0;
        }

        // Estadísticas globales
        $estadisticasGlobales = [
            'total_transportistas' => $transportistas->count(),
            'total_envios_periodo' => $transportistas->sum('total_envios'),
            'total_entregas' => $transportistas->sum('entregas_completadas'),
            'promedio_por_transportista' => $transportistas->count() > 0 
                ? round($transportistas->sum('total_envios') / $transportistas->count(), 1) 
                : 0,
            'tasa_efectividad_global' => $transportistas->sum('total_envios') > 0
                ? round(($transportistas->sum('entregas_completadas') / $transportistas->sum('total_envios')) * 100, 1)
                : 0,
        ];

        return view('reportes.productividad', compact(
            'transportistas', 'estadisticasGlobales', 'filtros'
        ));
    }

    public function productividadPdf(Request $request)
    {
        $filtros = $this->aplicarFiltrosFecha($request);
        
        // Usar la misma lógica mejorada del método productividad()
        $transportistasIds = DB::table('users as u')
            ->leftJoin('model_has_roles', 'u.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where(function($query) {
                $query->where('roles.name', 'transportista')
                      ->orWhere('u.tipo', 'transportista')
                      ->orWhere('u.role', 'transportista');
            })
            ->select('u.id', 'u.name', 'u.email')
            ->distinct()
            ->get();
        
        if ($transportistasIds->isEmpty()) {
            $transportistas = collect([]);
        } else {
            $transportistas = $transportistasIds->map(function($user) use ($filtros) {
                $vehiculosIds = DB::table('vehiculos')
                    ->where('transportista_id', $user->id)
                    ->pluck('id');
                
                if ($vehiculosIds->isEmpty()) {
                    return (object)[
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'total_envios' => 0,
                        'entregas_completadas' => 0,
                        'en_transito' => 0,
                        'total_peso_transportado' => 0,
                        'total_items_transportados' => 0,
                        'tasa_efectividad' => 0,
                        'total_incidentes' => 0,
                    ];
                }
                
                $envios = DB::table('envios as e')
                    ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                    ->whereIn('ea.vehiculo_id', $vehiculosIds)
                    ->whereBetween('e.fecha_creacion', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
                    ->select('e.id', 'e.estado', 'e.total_peso', 'e.total_cantidad')
                    ->get();
                
                $totalEnvios = $envios->count();
                $entregasCompletadas = $envios->where('estado', 'entregado')->count();
                $enTransito = $envios->where('estado', 'en_transito')->count();
                $totalPeso = $envios->sum('total_peso') ?? 0;
                $totalItems = $envios->sum('total_cantidad') ?? 0;
                
                // Obtener incidentes
                $enviosIds = $envios->pluck('id');
                $totalIncidentes = 0;
                if ($enviosIds->isNotEmpty()) {
                    $totalIncidentes = DB::table('incidentes')
                        ->whereIn('envio_id', $enviosIds)
                        ->whereBetween('fecha_reporte', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
                        ->count();
                }
                
                $tasaEfectividad = $totalEnvios > 0 
                    ? round(($entregasCompletadas / $totalEnvios) * 100, 1) 
                    : 0;
                
                return (object)[
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_envios' => $totalEnvios,
                    'entregas_completadas' => $entregasCompletadas,
                    'en_transito' => $enTransito,
                    'total_peso_transportado' => $totalPeso,
                    'total_items_transportados' => $totalItems,
                    'tasa_efectividad' => $tasaEfectividad,
                    'total_incidentes' => $totalIncidentes,
                ];
            })->sortByDesc('total_envios')->values();
        }

        // Calcular estadísticas globales
        $estadisticasGlobales = [
            'total_transportistas' => $transportistas->count(),
            'total_envios_periodo' => $transportistas->sum('total_envios'),
            'total_entregas' => $transportistas->sum('entregas_completadas'),
            'total_en_transito' => $transportistas->sum('en_transito'),
            'total_incidentes' => $transportistas->sum('total_incidentes'),
            'total_peso' => $transportistas->sum('total_peso_transportado'),
            'total_items' => $transportistas->sum('total_items_transportados'),
            'promedio_por_transportista' => $transportistas->count() > 0 
                ? round($transportistas->sum('total_envios') / $transportistas->count(), 1) 
                : 0,
            'tasa_efectividad_global' => $transportistas->sum('total_envios') > 0
                ? round(($transportistas->sum('entregas_completadas') / $transportistas->sum('total_envios')) * 100, 1)
                : 0,
        ];

        $pdf = Pdf::loadView('reportes.pdf.productividad', compact('transportistas', 'filtros', 'estadisticasGlobales'));
        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'reporte-productividad-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    public function productividadCsv(Request $request)
    {
        $filtros = $this->aplicarFiltrosFecha($request);
        
        // Usar la misma lógica mejorada del método productividad()
        $transportistasIds = DB::table('users as u')
            ->leftJoin('model_has_roles', 'u.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where(function($query) {
                $query->where('roles.name', 'transportista')
                      ->orWhere('u.tipo', 'transportista')
                      ->orWhere('u.role', 'transportista');
            })
            ->select('u.id', 'u.name', 'u.email')
            ->distinct()
            ->get();
        
        if ($transportistasIds->isEmpty()) {
            $transportistas = collect([]);
        } else {
            $transportistas = $transportistasIds->map(function($user) use ($filtros) {
                $vehiculosIds = DB::table('vehiculos')
                    ->where('transportista_id', $user->id)
                    ->pluck('id');
                
                if ($vehiculosIds->isEmpty()) {
                    return (object)[
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'total_envios' => 0,
                        'entregas_completadas' => 0,
                        'en_transito' => 0,
                        'total_peso_transportado' => 0,
                        'total_items_transportados' => 0,
                        'tasa_efectividad' => 0,
                        'total_incidentes' => 0,
                    ];
                }
                
                $envios = DB::table('envios as e')
                    ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                    ->whereIn('ea.vehiculo_id', $vehiculosIds)
                    ->whereBetween('e.fecha_creacion', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
                    ->select('e.id', 'e.estado', 'e.total_peso', 'e.total_cantidad')
                    ->get();
                
                $totalEnvios = $envios->count();
                $entregasCompletadas = $envios->where('estado', 'entregado')->count();
                $enTransito = $envios->where('estado', 'en_transito')->count();
                $totalPeso = $envios->sum('total_peso') ?? 0;
                $totalItems = $envios->sum('total_cantidad') ?? 0;
                
                // Obtener incidentes
                $enviosIds = $envios->pluck('id');
                $totalIncidentes = 0;
                if ($enviosIds->isNotEmpty()) {
                    $totalIncidentes = DB::table('incidentes')
                        ->whereIn('envio_id', $enviosIds)
                        ->whereBetween('fecha_reporte', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
                        ->count();
                }
                
                $tasaEfectividad = $totalEnvios > 0 
                    ? round(($entregasCompletadas / $totalEnvios) * 100, 1) 
                    : 0;
                
                return (object)[
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'total_envios' => $totalEnvios,
                    'entregas_completadas' => $entregasCompletadas,
                    'en_transito' => $enTransito,
                    'total_peso_transportado' => $totalPeso,
                    'total_items_transportados' => $totalItems,
                    'tasa_efectividad' => $tasaEfectividad,
                    'total_incidentes' => $totalIncidentes,
                ];
            })->sortByDesc('total_envios')->values();
        }

        $filename = 'reporte-productividad-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ];

        $callback = function() use ($transportistas, $filtros) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Encabezado del reporte
            fputcsv($file, ['REPORTE DE PRODUCTIVIDAD DE TRANSPORTISTAS']);
            fputcsv($file, ['Sistema de Gestión Logística - Planta']);
            fputcsv($file, ['Período: ' . \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y')]);
            fputcsv($file, ['Generado: ' . now()->format('d/m/Y H:i:s')]);
            fputcsv($file, []); // Línea vacía
            
            // Encabezados de columnas
            fputcsv($file, [
                '#',
                'Transportista',
                'Email',
                'Total Envíos',
                'Entregas Completadas',
                'En Tránsito',
                'Incidentes',
                'Tasa Efectividad (%)',
                'Peso Transportado (kg)',
                'Items Transportados'
            ]);
            
            // Datos de transportistas
            foreach ($transportistas as $index => $t) {
                fputcsv($file, [
                    $index + 1,
                    $t->name,
                    $t->email,
                    $t->total_envios,
                    $t->entregas_completadas,
                    $t->en_transito,
                    $t->total_incidentes,
                    number_format($t->tasa_efectividad, 1),
                    number_format($t->total_peso_transportado, 2),
                    $t->total_items_transportados
                ]);
            }
            
            // Línea vacía
            fputcsv($file, []);
            
            // Resumen
            fputcsv($file, ['RESUMEN DEL PERÍODO']);
            fputcsv($file, ['Total Transportistas Activos', $transportistas->count()]);
            fputcsv($file, ['Total Envíos Gestionados', $transportistas->sum('total_envios')]);
            fputcsv($file, ['Total Entregas Completadas', $transportistas->sum('entregas_completadas')]);
            fputcsv($file, ['Total En Tránsito', $transportistas->sum('en_transito')]);
            fputcsv($file, ['Total Incidentes', $transportistas->sum('total_incidentes')]);
            fputcsv($file, ['Peso Total Transportado (kg)', number_format($transportistas->sum('total_peso_transportado'), 2)]);
            fputcsv($file, ['Items Total Transportados', $transportistas->sum('total_items_transportados')]);
            $tasaGlobal = $transportistas->sum('total_envios') > 0
                ? round(($transportistas->sum('entregas_completadas') / $transportistas->sum('total_envios')) * 100, 1)
                : 0;
            fputcsv($file, ['Tasa Efectividad Global (%)', number_format($tasaGlobal, 1)]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ========================================================================
    // MÉTODOS AUXILIARES
    // ========================================================================
    
    private function aplicarFiltrosFecha(Request $request): array
    {
        return [
            'fecha_inicio' => $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => $request->fecha_fin ?? now()->format('Y-m-d'),
        ];
    }

    private function obtenerEstadisticasOperaciones(array $filtros): array
    {
        $base = DB::table('envios')
            ->whereBetween('fecha_creacion', [$filtros['fecha_inicio'], $filtros['fecha_fin']]);

        return [
            'total_envios' => (clone $base)->count(),
            'pendientes' => (clone $base)->where('estado', 'pendiente')->count(),
            'en_transito' => (clone $base)->where('estado', 'en_transito')->count(),
            'entregados' => (clone $base)->where('estado', 'entregado')->count(),
            'cancelados' => (clone $base)->where('estado', 'cancelado')->count(),
            'total_peso' => (clone $base)->sum('total_peso') ?? 0,
            'total_valor' => (clone $base)->sum('total_precio') ?? 0,
            'total_items' => (clone $base)->sum('total_cantidad') ?? 0,
        ];
    }

    private function obtenerEnviosDetallados(array $filtros, Request $request, bool $paginar = true)
    {
        $query = DB::table('envios as e')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->leftJoin('users as t', 'v.transportista_id', '=', 't.id')
            ->whereBetween('e.fecha_creacion', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
            ->select(
                'e.*',
                'a.nombre as almacen_nombre',
                't.name as transportista_nombre',
                'v.placa as vehiculo_placa'
            )
            ->orderBy('e.fecha_creacion', 'desc');

        if ($request->filled('estado')) {
            $query->where('e.estado', $request->estado);
        }

        if ($request->filled('almacen_id')) {
            $query->where('e.almacen_destino_id', $request->almacen_id);
        }

        if ($request->filled('transportista_id')) {
            // Usar where directamente en lugar de whereHas (que es solo para Eloquent)
            $query->where('v.transportista_id', $request->transportista_id);
        }

        return $paginar ? $query->paginate(15) : $query->get();
    }

    private function obtenerEstadisticasIncidentes(array $filtros): array
    {
        // Ajustar fechas para incluir todo el día
        $fechaInicio = Carbon::parse($filtros['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::parse($filtros['fecha_fin'])->endOfDay();
        
        $base = DB::table('incidentes')
            ->whereBetween('fecha_reporte', [$fechaInicio, $fechaFin]);

        return [
            'total' => (clone $base)->count(),
            'pendientes' => (clone $base)->where('estado', 'pendiente')->count(),
            'en_proceso' => (clone $base)->where('estado', 'en_proceso')->count(),
            'resueltos' => (clone $base)->where('estado', 'resuelto')->count(),
            'tiempo_promedio_resolucion' => $this->calcularTiempoPromedioResolucion($filtros),
        ];
    }

    private function calcularTiempoPromedioResolucion(array $filtros): string
    {
        // Ajustar fechas para incluir todo el día
        $fechaInicio = Carbon::parse($filtros['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::parse($filtros['fecha_fin'])->endOfDay();
        
        $resultado = DB::table('incidentes')
            ->whereBetween('fecha_reporte', [$fechaInicio, $fechaFin])
            ->whereNotNull('fecha_resolucion')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (fecha_resolucion::timestamp - fecha_reporte::timestamp))/3600) as horas_promedio')
            ->first();

        if ($resultado && $resultado->horas_promedio) {
            $horas = round($resultado->horas_promedio, 1);
            if ($horas < 24) {
                return $horas . ' horas';
            }
            return round($horas / 24, 1) . ' días';
        }

        return 'N/A';
    }

    private function traducirEstado(string $estado): string
    {
        return match($estado) {
            'pendiente' => 'Pendiente',
            'asignado' => 'Asignado',
            'en_transito' => 'En Tránsito',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
            'en_proceso' => 'En Proceso',
            'resuelto' => 'Resuelto',
            default => ucfirst($estado)
        };
    }

    // ========================================================================
    // REPORTES PARA TRANSPORTISTAS
    // ========================================================================
    
    /**
     * Mis Incidentes (Transportista)
     */
    public function misIncidentes(Request $request)
    {
        $transportistaId = auth()->id();
        $filtros = $this->aplicarFiltrosFecha($request);

        // Obtener envíos del transportista (a través de vehiculos)
        $misEnviosIds = DB::table('envio_asignaciones as ea')
            ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->where('v.transportista_id', $transportistaId)
            ->pluck('ea.envio_id');

        // Ajustar fechas para incluir todo el día
        $fechaInicio = Carbon::parse($filtros['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::parse($filtros['fecha_fin'])->endOfDay();
        
        // Estadísticas de incidentes del transportista (FILTRADAS POR FECHA)
        $baseQuery = DB::table('incidentes')
            ->whereIn('envio_id', $misEnviosIds)
            ->whereBetween('fecha_reporte', [$fechaInicio, $fechaFin]);
        
        $estadisticas = [
            'total' => (clone $baseQuery)->count(),
            'pendientes' => (clone $baseQuery)->where('estado', 'pendiente')->count(),
            'en_proceso' => (clone $baseQuery)->where('estado', 'en_proceso')->count(),
            'resueltos' => (clone $baseQuery)->where('estado', 'resuelto')->count(),
        ];

        // Listado de incidentes
        $query = DB::table('incidentes as i')
            ->leftJoin('envios as e', 'i.envio_id', '=', 'e.id')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->whereIn('i.envio_id', $misEnviosIds)
            ->whereBetween('i.fecha_reporte', [$fechaInicio, $fechaFin])
            ->select(
                'i.*',
                'e.codigo as envio_codigo',
                'a.nombre as almacen_nombre',
                DB::raw('COALESCE(i.solicitar_ayuda, false) as solicitar_ayuda')
            )
            ->orderBy('i.solicitar_ayuda', 'desc') // Priorizar solicitudes de ayuda
            ->orderBy('i.fecha_reporte', 'desc');

        if ($request->filled('tipo')) {
            $query->where('i.tipo_incidente', $request->tipo);
        }

        if ($request->filled('estado')) {
            $query->where('i.estado', $request->estado);
        }

        $incidentes = $query->paginate(15);

        // Tipos de incidente
        $tiposIncidente = DB::table('incidentes')
            ->whereIn('envio_id', $misEnviosIds)
            ->select('tipo_incidente')
            ->distinct()
            ->orderBy('tipo_incidente')
            ->pluck('tipo_incidente');

        return view('reportes.mis-incidentes', compact(
            'estadisticas', 'incidentes', 'tiposIncidente', 'filtros'
        ));
    }

    /**
     * Mostrar formulario para crear nuevo incidente (Transportista)
     */
    public function misIncidentesCreate(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('transportista')) {
            abort(403, 'Solo los transportistas pueden reportar incidentes.');
        }
        
        $transportistaId = $user->id;
        
        // Obtener envíos asignados al transportista (solo los que están en tránsito o asignados)
        $vehiculosIds = DB::table('vehiculos')
            ->where('transportista_id', $transportistaId)
            ->pluck('id');
        
        $envios = DB::table('envios as e')
            ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->whereIn('ea.vehiculo_id', $vehiculosIds)
            ->whereIn('e.estado', ['asignado', 'aceptado', 'en_transito'])
            ->select(
                'e.id',
                'e.codigo',
                'e.estado',
                'a.nombre as almacen_nombre',
                'e.fecha_creacion'
            )
            ->orderBy('e.fecha_creacion', 'desc')
            ->get();
        
        // Tipos de incidente predefinidos
        $tiposIncidente = [
            'accidente_vehiculo' => 'Accidente de Vehículo',
            'averia_vehiculo' => 'Avería de Vehículo',
            'robo' => 'Robo',
            'perdida_mercancia' => 'Pérdida de Mercancía',
            'daño_mercancia' => 'Daño de Mercancía',
            'retraso' => 'Retraso en Entrega',
            'problema_ruta' => 'Problema en Ruta',
            'problema_cliente' => 'Problema con Cliente',
            'otro' => 'Otro',
        ];
        
        return view('reportes.mis-incidentes-create', compact('envios', 'tiposIncidente'));
    }

    /**
     * Guardar nuevo incidente (Transportista)
     */
    public function misIncidentesStore(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('transportista')) {
            abort(403, 'Solo los transportistas pueden reportar incidentes.');
        }
        
        $transportistaId = $user->id;
        
        $request->validate([
            'envio_id' => 'required|exists:envios,id',
            'tipo_incidente' => 'required|string|max:50',
            'descripcion' => 'required|string|min:10|max:2000',
            'solicitar_ayuda' => 'nullable|boolean',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB max
        ]);
        
        // Verificar que el envío pertenece al transportista
        $vehiculosIds = DB::table('vehiculos')
            ->where('transportista_id', $transportistaId)
            ->pluck('id');
        
        $envioAsignado = DB::table('envio_asignaciones as ea')
            ->where('ea.envio_id', $request->envio_id)
            ->whereIn('ea.vehiculo_id', $vehiculosIds)
            ->exists();
        
        if (!$envioAsignado) {
            return back()->withInput()->with('error', 'No tienes permiso para reportar incidentes en este envío.');
        }
        
        // Manejar subida de foto
        $fotoUrl = null;
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreArchivo = 'incidente-' . time() . '-' . uniqid() . '.' . $foto->getClientOriginalExtension();
            $ruta = $foto->storeAs('incidentes', $nombreArchivo, 'public');
            $fotoUrl = '/storage/' . $ruta;
        }
        
        // Crear incidente
        $incidenteId = DB::table('incidentes')->insertGetId([
            'envio_id' => $request->envio_id,
            'tipo_incidente' => $request->tipo_incidente,
            'descripcion' => $request->descripcion,
            'foto_url' => $fotoUrl,
            'estado' => 'pendiente',
            'solicitar_ayuda' => $request->has('solicitar_ayuda') && $request->solicitar_ayuda,
            'fecha_reporte' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Si solicita ayuda, agregar nota especial
        if ($request->has('solicitar_ayuda') && $request->solicitar_ayuda) {
            DB::table('incidentes')
                ->where('id', $incidenteId)
                ->update([
                    'notas_resolucion' => "[SOLICITUD DE AYUDA URGENTE]\n" . 
                                         "El transportista " . $user->name . " solicita asistencia inmediata del administrador.\n" .
                                         "Fecha: " . now()->format('d/m/Y H:i:s') . "\n\n" .
                                         "Descripción del problema:\n" . $request->descripcion
                ]);
        }
        
        $mensaje = $request->has('solicitar_ayuda') && $request->solicitar_ayuda
            ? 'Incidente reportado y solicitud de ayuda enviada al administrador.'
            : 'Incidente reportado correctamente.';
        
        return redirect()->route('reportes.mis-incidentes')
            ->with('success', $mensaje);
    }

    /**
     * Mi Productividad (Transportista)
     */
    public function miProductividad(Request $request)
    {
        $transportistaId = auth()->id();
        $filtros = $this->aplicarFiltrosFecha($request);

        // Estadísticas del transportista
        $estadisticas = DB::table('envios as e')
            ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->where('v.transportista_id', $transportistaId)
            ->whereBetween('e.fecha_creacion', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
            ->selectRaw("
                COUNT(*) as total_envios,
                COUNT(CASE WHEN e.estado = 'entregado' THEN 1 END) as entregas_completadas,
                COUNT(CASE WHEN e.estado = 'en_transito' THEN 1 END) as en_transito,
                COALESCE(SUM(e.total_peso), 0) as total_peso_transportado,
                COALESCE(SUM(e.total_cantidad), 0) as total_items_transportados
            ")
            ->first();

        // Calcular tasa de efectividad
        $estadisticas->tasa_efectividad = $estadisticas->total_envios > 0 
            ? round(($estadisticas->entregas_completadas / $estadisticas->total_envios) * 100, 1) 
            : 0;

        // Incidentes
        $misEnviosIds = DB::table('envio_asignaciones as ea')
            ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->where('v.transportista_id', $transportistaId)
            ->pluck('ea.envio_id');

        $estadisticas->total_incidentes = DB::table('incidentes')
            ->whereIn('envio_id', $misEnviosIds)
            ->whereBetween('fecha_reporte', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
            ->count();

        // Envíos por mes (dinámico según el rango de fechas seleccionado)
        $enviosPorMes = [];
        $fechaInicio = Carbon::parse($filtros['fecha_inicio']);
        $fechaFin = Carbon::parse($filtros['fecha_fin']);
        
        // Calcular número de meses en el rango
        $mesesDiferencia = $fechaInicio->diffInMonths($fechaFin) + 1;
        
        // Si el rango es menor a 6 meses, mostrar todos los meses del rango
        // Si es mayor, agrupar por mes pero limitar a un máximo razonable
        $maxMeses = min($mesesDiferencia, 12); // Máximo 12 meses para evitar sobrecarga
        
        for ($i = 0; $i < $maxMeses; $i++) {
            $mes = $fechaInicio->copy()->addMonths($i);
            $inicio = $mes->copy()->startOfMonth();
            $fin = $mes->copy()->endOfMonth();
            
            // Asegurar que no exceda el rango de fechas seleccionado
            if ($inicio->lt($fechaInicio)) {
                $inicio = $fechaInicio->copy();
            }
            if ($fin->gt($fechaFin)) {
                $fin = $fechaFin->copy();
            }

            $total = DB::table('envios as e')
                ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
                ->where('v.transportista_id', $transportistaId)
                ->whereBetween('e.fecha_creacion', [$inicio, $fin])
                ->count();

            $entregados = DB::table('envios as e')
                ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
                ->where('v.transportista_id', $transportistaId)
                ->where('e.estado', 'entregado')
                ->whereBetween('e.fecha_creacion', [$inicio, $fin])
                ->count();

            $enviosPorMes[] = [
                'mes' => $mes->locale('es')->isoFormat('MMM YYYY'),
                'total' => $total,
                'entregados' => $entregados,
            ];
        }

        return view('reportes.mi-productividad', compact(
            'estadisticas', 'enviosPorMes', 'filtros'
        ));
    }

    /**
     * Exportar mi productividad a PDF
     */
    public function miProductividadPdf(Request $request)
    {
        $transportistaId = auth()->id();
        $filtros = $this->aplicarFiltrosFecha($request);
        $transportista = auth()->user();

        // Estadísticas del transportista
        $estadisticas = DB::table('envios as e')
            ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->where('v.transportista_id', $transportistaId)
            ->whereBetween('e.fecha_creacion', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
            ->selectRaw("
                COUNT(*) as total_envios,
                COUNT(CASE WHEN e.estado = 'entregado' THEN 1 END) as entregas_completadas,
                COUNT(CASE WHEN e.estado = 'en_transito' THEN 1 END) as en_transito,
                COALESCE(SUM(e.total_peso), 0) as total_peso_transportado,
                COALESCE(SUM(e.total_cantidad), 0) as total_items_transportados
            ")
            ->first();

        // Calcular tasa de efectividad
        $estadisticas->tasa_efectividad = $estadisticas->total_envios > 0 
            ? round(($estadisticas->entregas_completadas / $estadisticas->total_envios) * 100, 1) 
            : 0;

        // Incidentes
        $misEnviosIds = DB::table('envio_asignaciones as ea')
            ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->where('v.transportista_id', $transportistaId)
            ->pluck('ea.envio_id');

        $estadisticas->total_incidentes = DB::table('incidentes')
            ->whereIn('envio_id', $misEnviosIds)
            ->whereBetween('fecha_reporte', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
            ->count();

        // Envíos por mes (dinámico según el rango de fechas seleccionado)
        $enviosPorMes = [];
        $fechaInicio = Carbon::parse($filtros['fecha_inicio']);
        $fechaFin = Carbon::parse($filtros['fecha_fin']);
        
        $mesesDiferencia = $fechaInicio->diffInMonths($fechaFin) + 1;
        $maxMeses = min($mesesDiferencia, 12);
        
        for ($i = 0; $i < $maxMeses; $i++) {
            $mes = $fechaInicio->copy()->addMonths($i);
            $inicio = $mes->copy()->startOfMonth();
            $fin = $mes->copy()->endOfMonth();
            
            if ($inicio->lt($fechaInicio)) {
                $inicio = $fechaInicio->copy();
            }
            if ($fin->gt($fechaFin)) {
                $fin = $fechaFin->copy();
            }

            $total = DB::table('envios as e')
                ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
                ->where('v.transportista_id', $transportistaId)
                ->whereBetween('e.fecha_creacion', [$inicio, $fin])
                ->count();

            $entregados = DB::table('envios as e')
                ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
                ->where('v.transportista_id', $transportistaId)
                ->where('e.estado', 'entregado')
                ->whereBetween('e.fecha_creacion', [$inicio, $fin])
                ->count();

            $enviosPorMes[] = [
                'mes' => $mes->locale('es')->isoFormat('MMM YYYY'),
                'total' => $total,
                'entregados' => $entregados,
            ];
        }

        $pdf = Pdf::loadView('reportes.pdf.mi-productividad', compact(
            'estadisticas', 'enviosPorMes', 'filtros', 'transportista'
        ));
        
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'mi-productividad-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Exportar mi productividad a CSV
     */
    public function miProductividadCsv(Request $request)
    {
        $transportistaId = auth()->id();
        $filtros = $this->aplicarFiltrosFecha($request);
        $transportista = auth()->user();

        // Estadísticas del transportista
        $estadisticas = DB::table('envios as e')
            ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->where('v.transportista_id', $transportistaId)
            ->whereBetween('e.fecha_creacion', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
            ->selectRaw("
                COUNT(*) as total_envios,
                COUNT(CASE WHEN e.estado = 'entregado' THEN 1 END) as entregas_completadas,
                COUNT(CASE WHEN e.estado = 'en_transito' THEN 1 END) as en_transito,
                COALESCE(SUM(e.total_peso), 0) as total_peso_transportado,
                COALESCE(SUM(e.total_cantidad), 0) as total_items_transportados
            ")
            ->first();

        // Calcular tasa de efectividad
        $estadisticas->tasa_efectividad = $estadisticas->total_envios > 0 
            ? round(($estadisticas->entregas_completadas / $estadisticas->total_envios) * 100, 1) 
            : 0;

        // Incidentes
        $misEnviosIds = DB::table('envio_asignaciones as ea')
            ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->where('v.transportista_id', $transportistaId)
            ->pluck('ea.envio_id');

        $estadisticas->total_incidentes = DB::table('incidentes')
            ->whereIn('envio_id', $misEnviosIds)
            ->whereBetween('fecha_reporte', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
            ->count();

        // Envíos por mes
        $enviosPorMes = [];
        $fechaInicio = Carbon::parse($filtros['fecha_inicio']);
        $fechaFin = Carbon::parse($filtros['fecha_fin']);
        
        $mesesDiferencia = $fechaInicio->diffInMonths($fechaFin) + 1;
        $maxMeses = min($mesesDiferencia, 12);
        
        for ($i = 0; $i < $maxMeses; $i++) {
            $mes = $fechaInicio->copy()->addMonths($i);
            $inicio = $mes->copy()->startOfMonth();
            $fin = $mes->copy()->endOfMonth();
            
            if ($inicio->lt($fechaInicio)) {
                $inicio = $fechaInicio->copy();
            }
            if ($fin->gt($fechaFin)) {
                $fin = $fechaFin->copy();
            }

            $total = DB::table('envios as e')
                ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
                ->where('v.transportista_id', $transportistaId)
                ->whereBetween('e.fecha_creacion', [$inicio, $fin])
                ->count();

            $entregados = DB::table('envios as e')
                ->join('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
                ->where('v.transportista_id', $transportistaId)
                ->where('e.estado', 'entregado')
                ->whereBetween('e.fecha_creacion', [$inicio, $fin])
                ->count();

            $enviosPorMes[] = [
                'mes' => $mes->locale('es')->isoFormat('MMM YYYY'),
                'total' => $total,
                'entregados' => $entregados,
            ];
        }

        $filename = 'mi-productividad-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ];

        $callback = function() use ($estadisticas, $enviosPorMes, $filtros, $transportista) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Encabezado del reporte
            fputcsv($file, ['MI PRODUCTIVIDAD']);
            fputcsv($file, ['Transportista: ' . $transportista->name]);
            fputcsv($file, ['Período: ' . \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y')]);
            fputcsv($file, ['Generado: ' . now()->format('d/m/Y H:i:s')]);
            fputcsv($file, []); // Línea vacía
            
            // Estadísticas principales
            fputcsv($file, ['ESTADÍSTICAS PRINCIPALES']);
            fputcsv($file, ['Total Envíos Asignados', $estadisticas->total_envios]);
            fputcsv($file, ['Entregas Completadas', $estadisticas->entregas_completadas]);
            fputcsv($file, ['En Tránsito', $estadisticas->en_transito]);
            fputcsv($file, ['Tasa de Efectividad (%)', number_format($estadisticas->tasa_efectividad, 1)]);
            fputcsv($file, ['Peso Transportado (kg)', number_format($estadisticas->total_peso_transportado, 2)]);
            fputcsv($file, ['Items Transportados', number_format($estadisticas->total_items_transportados, 0)]);
            fputcsv($file, ['Total Incidentes', $estadisticas->total_incidentes]);
            fputcsv($file, []); // Línea vacía
            
            // Envíos por mes
            if (count($enviosPorMes) > 0) {
                fputcsv($file, ['ENVÍOS POR MES']);
                fputcsv($file, ['Mes', 'Total Envíos', 'Entregados']);
                foreach ($enviosPorMes as $mes) {
                    fputcsv($file, [
                        $mes['mes'],
                        $mes['total'],
                        $mes['entregados']
                    ]);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar mis incidentes a PDF
     */
    public function misIncidentesPdf(Request $request)
    {
        $transportistaId = auth()->id();
        $filtros = $this->aplicarFiltrosFecha($request);

        // Ajustar fechas para incluir todo el día
        $fechaInicio = Carbon::parse($filtros['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::parse($filtros['fecha_fin'])->endOfDay();

        $misEnviosIds = DB::table('envio_asignaciones as ea')
            ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->where('v.transportista_id', $transportistaId)
            ->pluck('ea.envio_id');

        $incidentes = DB::table('incidentes as i')
            ->leftJoin('envios as e', 'i.envio_id', '=', 'e.id')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->whereIn('i.envio_id', $misEnviosIds)
            ->whereBetween('i.fecha_reporte', [$fechaInicio, $fechaFin])
            ->select('i.*', 'e.codigo as envio_codigo', 'a.nombre as almacen_nombre')
            ->orderBy('i.fecha_reporte', 'desc')
            ->get();

        $transportista = auth()->user();
        
        // Calcular estadísticas
        $estadisticas = [
            'total' => $incidentes->count(),
            'pendientes' => $incidentes->where('estado', 'pendiente')->count(),
            'en_proceso' => $incidentes->where('estado', 'en_proceso')->count(),
            'resueltos' => $incidentes->where('estado', 'resuelto')->count(),
        ];

        $pdf = Pdf::loadView('reportes.pdf.mis-incidentes', compact(
            'incidentes', 'filtros', 'transportista', 'estadisticas'
        ));
        
        $pdf->setPaper('a4', 'portrait');
        
        // Descarga directa sin previsualización
        $filename = 'mis-incidentes-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    public function misIncidentesCsv(Request $request)
    {
        $transportistaId = auth()->id();
        $filtros = $this->aplicarFiltrosFecha($request);

        // Ajustar fechas para incluir todo el día
        $fechaInicio = Carbon::parse($filtros['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::parse($filtros['fecha_fin'])->endOfDay();

        $misEnviosIds = DB::table('envio_asignaciones as ea')
            ->join('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->where('v.transportista_id', $transportistaId)
            ->pluck('ea.envio_id');

        $incidentes = DB::table('incidentes as i')
            ->leftJoin('envios as e', 'i.envio_id', '=', 'e.id')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->whereIn('i.envio_id', $misEnviosIds)
            ->whereBetween('i.fecha_reporte', [$fechaInicio, $fechaFin])
            ->select('i.*', 'e.codigo as envio_codigo', 'a.nombre as almacen_nombre')
            ->orderBy('i.fecha_reporte', 'desc')
            ->get();

        $transportista = auth()->user();

        $filename = 'mis-incidentes-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ];

        $callback = function() use ($incidentes, $filtros, $transportista) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Encabezado del reporte
            fputcsv($file, ['MIS INCIDENTES REPORTADOS']);
            fputcsv($file, ['Transportista: ' . $transportista->name]);
            fputcsv($file, ['Período: ' . \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y')]);
            fputcsv($file, ['Generado: ' . now()->format('d/m/Y H:i:s')]);
            fputcsv($file, []); // Línea vacía
            
            // Encabezados de columnas
            fputcsv($file, [
                'Fecha Reporte',
                'Tipo Incidente',
                'Envío',
                'Almacén',
                'Descripción',
                'Estado',
                'Fecha Resolución'
            ]);
            
            // Datos de incidentes
            foreach ($incidentes as $inc) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($inc->fecha_reporte)->format('d/m/Y H:i'),
                    ucfirst(str_replace('_', ' ', $inc->tipo_incidente)),
                    $inc->envio_codigo ?? 'N/A',
                    $inc->almacen_nombre ?? 'N/A',
                    $inc->descripcion ?? '',
                    ucfirst(str_replace('_', ' ', $inc->estado)),
                    $inc->fecha_resolucion ? \Carbon\Carbon::parse($inc->fecha_resolucion)->format('d/m/Y H:i') : 'Pendiente'
                ]);
            }
            
            // Línea vacía
            fputcsv($file, []);
            
            // Resumen
            fputcsv($file, ['RESUMEN']);
            fputcsv($file, ['Total Incidentes', $incidentes->count()]);
            fputcsv($file, ['Pendientes', $incidentes->where('estado', 'pendiente')->count()]);
            fputcsv($file, ['En Proceso', $incidentes->where('estado', 'en_proceso')->count()]);
            fputcsv($file, ['Resueltos', $incidentes->where('estado', 'resuelto')->count()]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ========================================================================
    // DOCUMENTO: RESOLUCIÓN DE INCIDENTE
    // ========================================================================
    
    public function resolucionIncidente($incidenteId)
    {
        $incidente = DB::table('incidentes')->where('id', $incidenteId)->first();
        
        if (!$incidente) {
            abort(404, 'Incidente no encontrado');
        }
        
        // Obtener envío
        $envio = DB::table('envios')->where('id', $incidente->envio_id)->first();
        
        // Obtener almacén
        $almacen = DB::table('almacenes')->where('id', $envio->almacen_destino_id)->first();
        
        // Obtener transportista y vehículo (transportista a través del vehículo)
        $asignacion = DB::table('envio_asignaciones')->where('envio_id', $envio->id)->first();
        $vehiculo = $asignacion ? DB::table('vehiculos')->where('id', $asignacion->vehiculo_id)->first() : null;
        $transportista = $vehiculo ? DB::table('users')->where('id', $vehiculo->transportista_id)->first() : null;
        $vehiculo = $asignacion ? DB::table('vehiculos')->where('id', $asignacion->vehiculo_id)->first() : null;
        
        return view('reportes.resolucion-incidente-vista', compact(
            'incidente', 'envio', 'almacen', 'transportista', 'vehiculo'
        ));
    }
    
    public function resolucionIncidentePdf($incidenteId)
    {
        $incidente = DB::table('incidentes')->where('id', $incidenteId)->first();
        
        if (!$incidente) {
            abort(404, 'Incidente no encontrado');
        }
        
        // Obtener envío
        $envio = DB::table('envios')->where('id', $incidente->envio_id)->first();
        
        // Obtener almacén
        $almacen = DB::table('almacenes')->where('id', $envio->almacen_destino_id)->first();
        
        // Obtener transportista y vehículo (transportista a través del vehículo)
        $asignacion = DB::table('envio_asignaciones')->where('envio_id', $envio->id)->first();
        $vehiculo = $asignacion ? DB::table('vehiculos')->where('id', $asignacion->vehiculo_id)->first() : null;
        $transportista = $vehiculo ? DB::table('users')->where('id', $vehiculo->transportista_id)->first() : null;
        
        $pdf = Pdf::loadView('reportes.pdf.resolucion-incidente', compact(
            'incidente', 'envio', 'almacen', 'transportista', 'vehiculo'
        ));
        
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('resolucion-incidente-' . str_pad($incidenteId, 5, '0', STR_PAD_LEFT) . '.pdf');
    }

    // ========================================================================
    // REPORTE DE TRAZABILIDAD COMPLETA DEL ENVÍO
    // ========================================================================

    /**
     * Vista HTML del reporte de trazabilidad
     */
    public function trazabilidad($id)
    {
        $envio = \App\Models\Envio::with([
            'productos', 
            'almacenDestino', 
            'asignacion.transportista', 
            'asignacion.vehiculo',
            'almacenDestino.usuarioAlmacen', // usuario se obtiene a través de almacenDestino
            'historial'
        ])->findOrFail($id);

        $planta = \App\Models\Almacen::where('es_planta', true)->first();
        
        // Obtener incidentes del envío
        $incidentes = DB::table('incidentes')->where('envio_id', $id)->orderBy('created_at')->get();

        // Obtener fechas detalladas
        $fechaCreacion = $envio->fecha_creacion ?? $envio->created_at;
        $fechaAsignacion = $envio->fecha_asignacion ?? ($envio->asignacion->fecha_asignacion ?? null);
        $fechaAceptacion = $envio->asignacion->fecha_aceptacion ?? null;
        $fechaInicioTransito = $envio->fecha_inicio_transito;
        $fechaEntrega = $envio->fecha_entrega;

        // Obtener firma del transportista
        // Prioridad: 1) firma_transportista del envío (si es base64), 2) Node.js API, 3) null
        $firmaTransportista = null;
        
        // Primero verificar si hay una firma base64 guardada directamente en el envío
        if ($envio->firma_transportista) {
            $firma = $envio->firma_transportista;
            
            // Si empieza con "data:image", es base64 completo
            if (strpos($firma, 'data:image') === 0) {
                // Extraer solo la parte base64
                $firma = preg_replace('#^data:image/[^;]+;base64,#', '', $firma);
            }
            
            // Verificar si parece ser base64 válido (solo caracteres base64 y longitud razonable)
            if (preg_match('/^[A-Za-z0-9+\/]+=*$/', $firma) && strlen($firma) > 100) {
                $firmaTransportista = $firma;
                \Log::info("Firma base64 encontrada en envío para trazabilidad (vista)", [
                    'envio_id' => $id,
                    'envio_codigo' => $envio->codigo,
                    'firma_length' => strlen($firma)
                ]);
            }
        }
        
        // Si no hay firma en el envío, buscar en Node.js
        if (!$firmaTransportista) {
            try {
                $nodeApiUrl = env('NODE_API_URL', 'http://bomberos.dasalas.shop/api');
                
                // Intentar primero con el ID del envío
                $response = \Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
                    'envio_id' => $id,
                    'tipo' => 'salida'
                ]);
                
                if ($response->successful()) {
                    $checklists = $response->json();
                    $checklistSalida = collect($checklists['checklists'] ?? [])->first();
                    $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                }
                
                // Si no se encontró con el ID, intentar con el código del envío
                if (!$firmaTransportista && $envio->codigo) {
                    $response = \Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
                        'envio_codigo' => $envio->codigo,
                        'tipo' => 'salida'
                    ]);
                    
                    if ($response->successful()) {
                        $checklists = $response->json();
                        $checklistSalida = collect($checklists['checklists'] ?? [])->first();
                        $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                    }
                }
                
                // Si aún no se encontró, intentar buscar todos los checklists y filtrar
                if (!$firmaTransportista) {
                    $response = \Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists");
                    
                    if ($response->successful()) {
                        $checklists = $response->json();
                        $allChecklists = $checklists['checklists'] ?? [];
                        
                        // Buscar por ID o código
                        $checklistSalida = collect($allChecklists)->first(function($checklist) use ($id, $envio) {
                            return ($checklist['envio_id'] == $id || $checklist['envio_codigo'] == $envio->codigo) 
                                && ($checklist['tipo'] == 'salida' || $checklist['tipo'] == 'checklist_salida');
                        });
                        
                        $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                    }
                }
                
                \Log::info("Firma obtenida para trazabilidad (vista)", [
                    'envio_id' => $id,
                    'envio_codigo' => $envio->codigo,
                    'tiene_firma' => !empty($firmaTransportista),
                    'fuente' => $firmaTransportista ? 'nodejs' : 'ninguna'
                ]);
            } catch (\Exception $e) {
                \Log::warning("Error obteniendo firma para trazabilidad: " . $e->getMessage(), [
                    'envio_id' => $id,
                    'envio_codigo' => $envio->codigo ?? null
                ]);
            }
        }
        
        // Obtener nombre del transportista para mostrar como fallback
        $transportistaNombre = $envio->asignacion && $envio->asignacion->transportista 
            ? $envio->asignacion->transportista->name 
            : ($envio->transportista_nombre ?? 'N/A');

        // Calcular tiempo total
        $tiempoTotal = null;
        if ($envio->fecha_entrega && $envio->fecha_creacion) {
            $inicio = \Carbon\Carbon::parse($envio->fecha_creacion);
            $fin = \Carbon\Carbon::parse($envio->fecha_entrega);
            $diff = $inicio->diff($fin);
            $tiempoTotal = '';
            if ($diff->d > 0) $tiempoTotal .= $diff->d . 'd ';
            if ($diff->h > 0) $tiempoTotal .= $diff->h . 'h ';
            if ($diff->i > 0) $tiempoTotal .= $diff->i . 'm';
        }

        // Calcular tiempo en tránsito
        $tiempoTransito = null;
        if ($envio->fecha_entrega && $envio->fecha_inicio_transito) {
            $inicio = \Carbon\Carbon::parse($envio->fecha_inicio_transito);
            $fin = \Carbon\Carbon::parse($envio->fecha_entrega);
            $diff = $inicio->diff($fin);
            $tiempoTransito = '';
            if ($diff->d > 0) $tiempoTransito .= $diff->d . 'd ';
            if ($diff->h > 0) $tiempoTransito .= $diff->h . 'h ';
            if ($diff->i > 0) $tiempoTransito .= $diff->i . 'm';
        }

        return view('reportes.trazabilidad-vista', compact(
            'envio', 'planta', 'incidentes', 'tiempoTotal', 'tiempoTransito',
            'fechaCreacion', 'fechaAsignacion', 'fechaAceptacion', 'fechaInicioTransito', 'fechaEntrega',
            'firmaTransportista', 'transportistaNombre'
        ));
    }

    /**
     * Exportar reporte de trazabilidad a PDF
     */
    public function trazabilidadPdf($id)
    {
        $envio = \App\Models\Envio::with([
            'productos', 
            'almacenDestino', 
            'asignacion.transportista', 
            'asignacion.vehiculo',
            'almacenDestino.usuarioAlmacen', // usuario se obtiene a través de almacenDestino
            'historial'
        ])->findOrFail($id);

        $planta = \App\Models\Almacen::where('es_planta', true)->first();
        
        // Obtener incidentes del envío
        $incidentes = DB::table('incidentes')->where('envio_id', $id)->orderBy('created_at')->get();

        // Obtener fechas detalladas
        $fechaCreacion = $envio->fecha_creacion ?? $envio->created_at;
        $fechaAsignacion = $envio->fecha_asignacion ?? ($envio->asignacion->fecha_asignacion ?? null);
        $fechaAceptacion = $envio->asignacion->fecha_aceptacion ?? null;
        $fechaInicioTransito = $envio->fecha_inicio_transito;
        $fechaEntrega = $envio->fecha_entrega;

        // Obtener firma del transportista
        // Prioridad: 1) firma_transportista del envío (si es base64), 2) Node.js API, 3) null
        $firmaTransportista = null;
        
        // Primero verificar si hay una firma base64 guardada directamente en el envío
        if ($envio->firma_transportista) {
            $firma = $envio->firma_transportista;
            
            // Si empieza con "data:image", es base64 completo
            if (strpos($firma, 'data:image') === 0) {
                // Extraer solo la parte base64
                $firma = preg_replace('#^data:image/[^;]+;base64,#', '', $firma);
            }
            
            // Verificar si parece ser base64 válido (solo caracteres base64 y longitud razonable)
            if (preg_match('/^[A-Za-z0-9+\/]+=*$/', $firma) && strlen($firma) > 100) {
                $firmaTransportista = $firma;
                \Log::info("Firma base64 encontrada en envío para trazabilidad", [
                    'envio_id' => $id,
                    'envio_codigo' => $envio->codigo,
                    'firma_length' => strlen($firma)
                ]);
            }
        }
        
        // Si no hay firma en el envío, buscar en Node.js
        if (!$firmaTransportista) {
            try {
                $nodeApiUrl = env('NODE_API_URL', 'http://bomberos.dasalas.shop/api');
                
                // Intentar primero con el ID del envío
                $response = \Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
                    'envio_id' => $id,
                    'tipo' => 'salida'
                ]);
                
                if ($response->successful()) {
                    $checklists = $response->json();
                    $checklistSalida = collect($checklists['checklists'] ?? [])->first();
                    $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                }
                
                // Si no se encontró con el ID, intentar con el código del envío
                if (!$firmaTransportista && $envio->codigo) {
                    $response = \Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
                        'envio_codigo' => $envio->codigo,
                        'tipo' => 'salida'
                    ]);
                    
                    if ($response->successful()) {
                        $checklists = $response->json();
                        $checklistSalida = collect($checklists['checklists'] ?? [])->first();
                        $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                    }
                }
                
                // Si aún no se encontró, intentar buscar todos los checklists y filtrar
                if (!$firmaTransportista) {
                    $response = \Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists");
                    
                    if ($response->successful()) {
                        $checklists = $response->json();
                        $allChecklists = $checklists['checklists'] ?? [];
                        
                        // Buscar por ID o código
                        $checklistSalida = collect($allChecklists)->first(function($checklist) use ($id, $envio) {
                            return ($checklist['envio_id'] == $id || $checklist['envio_codigo'] == $envio->codigo) 
                                && ($checklist['tipo'] == 'salida' || $checklist['tipo'] == 'checklist_salida');
                        });
                        
                        $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                    }
                }
                
                \Log::info("Firma obtenida para trazabilidad (PDF)", [
                    'envio_id' => $id,
                    'envio_codigo' => $envio->codigo,
                    'tiene_firma' => !empty($firmaTransportista),
                    'fuente' => $firmaTransportista ? 'nodejs' : 'ninguna'
                ]);
            } catch (\Exception $e) {
                \Log::warning("Error obteniendo firma para trazabilidad: " . $e->getMessage(), [
                    'envio_id' => $id,
                    'envio_codigo' => $envio->codigo ?? null
                ]);
            }
        }
        
        // Obtener nombre del transportista para mostrar como fallback
        $transportistaNombre = $envio->asignacion && $envio->asignacion->transportista 
            ? $envio->asignacion->transportista->name 
            : ($envio->transportista_nombre ?? 'N/A');

        // Convertir firma base64 a archivo temporal si existe (para mejor compatibilidad con DomPDF)
        $firmaPath = null;
        if ($firmaTransportista) {
            try {
                // $firmaTransportista puede venir en dos formatos:
                // 1. Base64 limpio (sin prefijo) - cuando viene del envío
                // 2. Base64 con prefijo data:image - cuando viene de Node.js
                // 3. Base64 limpio - cuando viene de Node.js sin prefijo
                
                $firmaBase64Limpia = $firmaTransportista;
                
                // Si tiene prefijo data:image, extraer solo la parte base64
                if (strpos($firmaTransportista, 'data:image') === 0) {
                    $firmaBase64Limpia = preg_replace('#^data:image/[^;]+;base64,#', '', $firmaTransportista);
                }
                
                // Decodificar base64
                $firmaBinaria = base64_decode($firmaBase64Limpia, true);
                
                if ($firmaBinaria !== false && strlen($firmaBinaria) > 0) {
                    // Crear archivo temporal
                    $firmaPath = storage_path('app/temp/firma_' . $envio->id . '_' . time() . '.png');
                    
                    // Asegurar que el directorio existe
                    $tempDir = storage_path('app/temp');
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }
                    
                    // Guardar imagen
                    file_put_contents($firmaPath, $firmaBinaria);
                    
                    \Log::info("Firma convertida a archivo temporal", [
                        'envio_id' => $envio->id,
                        'firma_path' => $firmaPath,
                        'tamaño' => filesize($firmaPath)
                    ]);
                } else {
                    \Log::warning("Firma base64 no pudo ser decodificada", [
                        'envio_id' => $envio->id,
                        'firma_length' => strlen($firmaBase64Limpia)
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("Error convirtiendo firma a archivo temporal: " . $e->getMessage(), [
                    'envio_id' => $envio->id,
                    'error' => $e->getTraceAsString()
                ]);
                // Si falla, usar base64 directamente
                $firmaPath = null;
            }
        }

        // Calcular tiempo total
        $tiempoTotal = null;
        if ($envio->fecha_entrega && $envio->fecha_creacion) {
            $inicio = \Carbon\Carbon::parse($envio->fecha_creacion);
            $fin = \Carbon\Carbon::parse($envio->fecha_entrega);
            $diff = $inicio->diff($fin);
            $tiempoTotal = '';
            if ($diff->d > 0) $tiempoTotal .= $diff->d . 'd ';
            if ($diff->h > 0) $tiempoTotal .= $diff->h . 'h ';
            if ($diff->i > 0) $tiempoTotal .= $diff->i . 'm';
        }

        // Calcular tiempo en tránsito
        $tiempoTransito = null;
        if ($envio->fecha_entrega && $envio->fecha_inicio_transito) {
            $inicio = \Carbon\Carbon::parse($envio->fecha_inicio_transito);
            $fin = \Carbon\Carbon::parse($envio->fecha_entrega);
            $diff = $inicio->diff($fin);
            $tiempoTransito = '';
            if ($diff->d > 0) $tiempoTransito .= $diff->d . 'd ';
            if ($diff->h > 0) $tiempoTransito .= $diff->h . 'h ';
            if ($diff->i > 0) $tiempoTransito .= $diff->i . 'm';
        }

        $pdf = Pdf::loadView('reportes.pdf.trazabilidad', compact(
            'envio', 'planta', 'incidentes', 'tiempoTotal', 'tiempoTransito',
            'fechaCreacion', 'fechaAsignacion', 'fechaAceptacion', 'fechaInicioTransito', 'fechaEntrega',
            'firmaTransportista', 'firmaPath', 'transportistaNombre'
        ));
        
        $pdf->setPaper('a4', 'portrait');
        
        // Limpiar archivo temporal después de generar el PDF
        $response = $pdf->download('trazabilidad-' . $envio->codigo . '.pdf');
        
        // Eliminar archivo temporal si existe
        if ($firmaPath && file_exists($firmaPath)) {
            @unlink($firmaPath);
        }
        
        return $response;
    }
}

