<?php

namespace App\Services;

use App\Models\Envio;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class DocumentoEntregaService
{
    private AlmacenIntegrationService $almacenService;

    public function __construct(AlmacenIntegrationService $almacenService)
    {
        $this->almacenService = $almacenService;
    }

    /**
     * Generar y enviar todos los documentos de entrega al almacén
     * 
     * @param Envio $envio
     * @return bool
     */
    public function generarYEnviarDocumentos(Envio $envio): bool
    {
        try {
            Log::info('📄 [DocumentoEntregaService] Iniciando generación de documentos para envío', [
                'envio_id' => $envio->id,
                'codigo' => $envio->codigo,
            ]);

            // Cargar relaciones necesarias
            $envio->load([
                'productos',
                'almacenDestino',
                'asignacion.transportista',
                'asignacion.vehiculo',
            ]);

            // 1. Generar PDF de Nota de Entrega
            $notaEntregaPdf = $this->generarNotaEntregaPdf($envio);
            if (!$notaEntregaPdf) {
                Log::error('❌ [DocumentoEntregaService] Error generando nota de entrega');
                return false;
            }

            // 2. Generar PDF de Trazabilidad Completa
            $trazabilidadPdf = $this->generarTrazabilidadPdf($envio);
            if (!$trazabilidadPdf) {
                Log::error('❌ [DocumentoEntregaService] Error generando trazabilidad completa');
                return false;
            }

            // 3. Obtener PDF de Propuesta de Vehículos
            $propuestaVehiculosPdf = $this->obtenerPropuestaVehiculosPdf($envio);
            if (!$propuestaVehiculosPdf) {
                Log::warning('⚠️ [DocumentoEntregaService] No se pudo obtener propuesta de vehículos');
            }

            // 4. Preparar documentos para envío
            $documentos = [
                'propuesta_vehiculos' => $propuestaVehiculosPdf,
                'nota_entrega' => $notaEntregaPdf,
                'trazabilidad_completa' => $trazabilidadPdf,
            ];

            // 5. Enviar documentos al almacén
            $enviadoAlmacen = $this->almacenService->notifyEntrega($envio, $documentos);

            // 6. Enviar documentos a Trazabilidad
            $enviadoTrazabilidad = $this->enviarDocumentosATrazabilidad($envio, $documentos);

            if ($enviadoAlmacen) {
                Log::info('✅ [DocumentoEntregaService] Documentos enviados exitosamente al almacén', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                ]);
            } else {
                Log::error('❌ [DocumentoEntregaService] Error enviando documentos al almacén');
            }

            if ($enviadoTrazabilidad) {
                Log::info('✅ [DocumentoEntregaService] Documentos enviados exitosamente a Trazabilidad', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                ]);
            } else {
                Log::warning('⚠️ [DocumentoEntregaService] Error enviando documentos a Trazabilidad (no crítico)');
            }

            // Retornar true si al menos uno fue exitoso
            return $enviadoAlmacen || $enviadoTrazabilidad;
        } catch (\Exception $e) {
            Log::error('❌ [DocumentoEntregaService] Error en generarYEnviarDocumentos', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Generar PDF de Nota de Entrega
     * 
     * @param Envio $envio
     * @return string|null Base64 del PDF
     */
    private function generarNotaEntregaPdf(Envio $envio): ?string
    {
        try {
            $productos = $envio->productos;
            $planta = \App\Models\Almacen::where('es_planta', true)->first();

            // Obtener checklist y firma desde Node.js
            $checklistSalida = null;
            $evidenciasChecklist = [];
            $firmaTransportista = null;
            
            try {
                $nodeApiUrl = env('NODE_API_URL', 'http://bomberos.dasalas.shop/api');
                
                // Intentar primero con el ID del envío
                $response = Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
                    'envio_id' => $envio->id,
                    'tipo' => 'salida'
                ]);
                
                if ($response->successful()) {
                    $checklists = $response->json();
                    $checklistSalida = collect($checklists['checklists'] ?? [])->first();
                    $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                }
                
                // Si no se encontró con el ID, intentar con el código del envío
                if (!$firmaTransportista && $envio->codigo) {
                    $response = Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
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
                    $response = Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists");
                    
                    if ($response->successful()) {
                        $checklists = $response->json();
                        $allChecklists = $checklists['checklists'] ?? [];
                        
                        // Buscar por ID o código
                        $checklistSalida = collect($allChecklists)->first(function($checklist) use ($envio) {
                            return ($checklist['envio_id'] == $envio->id || $checklist['envio_codigo'] == $envio->codigo) 
                                && ($checklist['tipo'] == 'salida' || $checklist['tipo'] == 'checklist_salida');
                        });
                        
                        $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                    }
                }
                
                Log::info("Firma obtenida para nota de entrega", [
                    'envio_id' => $envio->id,
                    'envio_codigo' => $envio->codigo,
                    'tiene_firma' => !empty($firmaTransportista)
                ]);
                    
                    // Obtener evidencias si hay items no marcados
                    if ($checklistSalida && isset($checklistSalida['datos'])) {
                        $datosChecklist = is_string($checklistSalida['datos']) 
                            ? json_decode($checklistSalida['datos'], true) 
                            : $checklistSalida['datos'];
                        
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
                        
                        if (!empty($itemsNoMarcados)) {
                            $evidenciasResponse = Http::timeout(5)->get(
                                "{$nodeApiUrl}/api/rutas-entrega/evidencias",
                                ['envio_id' => $envio->id, 'tipo' => 'checklist_salida']
                            );
                            
                            if ($evidenciasResponse->successful()) {
                                $evidenciasData = $evidenciasResponse->json();
                                $evidenciasChecklist = $evidenciasData['evidencias'] ?? [];
                            }
                        }
                        
                        $checklistSalida['items_no_marcados'] = $itemsNoMarcados;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Error obteniendo checklist para nota de entrega: " . $e->getMessage());
            }

            $pdf = Pdf::loadView('reportes.pdf.nota-entrega', compact(
                'envio', 'productos', 'planta', 'checklistSalida', 'evidenciasChecklist', 'firmaTransportista'
            ));
            $pdf->setPaper('a4', 'portrait');
            
            $pdfContent = $pdf->output();
            return base64_encode($pdfContent);
        } catch (\Exception $e) {
            Log::error('Error generando nota de entrega PDF', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Generar PDF de Trazabilidad Completa
     * 
     * @param Envio $envio
     * @return string|null Base64 del PDF
     */
    private function generarTrazabilidadPdf(Envio $envio): ?string
    {
        try {
            $planta = \App\Models\Almacen::where('es_planta', true)->first();
            $incidentes = DB::table('incidentes')->where('envio_id', $envio->id)->orderBy('created_at')->get();

            // Obtener fechas detalladas
            $fechaCreacion = $envio->fecha_creacion ?? $envio->created_at;
            $fechaAsignacion = $envio->fecha_asignacion ?? ($envio->asignacion->fecha_asignacion ?? null);
            $fechaAceptacion = $envio->asignacion->fecha_aceptacion ?? null;
            $fechaInicioTransito = $envio->fecha_inicio_transito;
            $fechaEntrega = $envio->fecha_entrega;

            // Obtener firma del checklist desde Node.js
            $firmaTransportista = null;
            try {
                $nodeApiUrl = env('NODE_API_URL', 'http://bomberos.dasalas.shop/api');
                
                // Intentar primero con el ID del envío
                $response = Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
                    'envio_id' => $envio->id,
                    'tipo' => 'salida'
                ]);
                
                if ($response->successful()) {
                    $checklists = $response->json();
                    $checklistSalida = collect($checklists['checklists'] ?? [])->first();
                    $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                }
                
                // Si no se encontró con el ID, intentar con el código del envío
                if (!$firmaTransportista && $envio->codigo) {
                    $response = Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists", [
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
                    $response = Http::timeout(5)->get("{$nodeApiUrl}/rutas-entrega/checklists");
                    
                    if ($response->successful()) {
                        $checklists = $response->json();
                        $allChecklists = $checklists['checklists'] ?? [];
                        
                        // Buscar por ID o código
                        $checklistSalida = collect($allChecklists)->first(function($checklist) use ($envio) {
                            return ($checklist['envio_id'] == $envio->id || $checklist['envio_codigo'] == $envio->codigo) 
                                && ($checklist['tipo'] == 'salida' || $checklist['tipo'] == 'checklist_salida');
                        });
                        
                        $firmaTransportista = $checklistSalida['firma_base64'] ?? null;
                    }
                }
                
                Log::info("Firma obtenida para trazabilidad en DocumentoEntregaService", [
                    'envio_id' => $envio->id,
                    'envio_codigo' => $envio->codigo,
                    'tiene_firma' => !empty($firmaTransportista)
                ]);
            } catch (\Exception $e) {
                Log::warning("Error obteniendo firma para trazabilidad: " . $e->getMessage(), [
                    'envio_id' => $envio->id,
                    'envio_codigo' => $envio->codigo ?? null
                ]);
            }

            // Calcular tiempos
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
                'firmaTransportista'
            ));
            $pdf->setPaper('a4', 'portrait');
            
            $pdfContent = $pdf->output();
            return base64_encode($pdfContent);
        } catch (\Exception $e) {
            Log::error('Error generando trazabilidad PDF', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtener PDF de Propuesta de Vehículos
     * 
     * @param Envio $envio
     * @return string|null Base64 del PDF
     */
    private function obtenerPropuestaVehiculosPdf(Envio $envio): ?string
    {
        try {
            // Verificar que el envío viene de Trazabilidad
            $vieneDeTrazabilidad = (
                strpos($envio->observaciones ?? '', 'Trazabilidad') !== false ||
                strpos($envio->observaciones ?? '', 'trazabilidad') !== false ||
                strpos($envio->observaciones ?? '', 'TRAZABILIDAD') !== false ||
                $envio->estado === 'pendiente_aprobacion_trazabilidad'
            );
            
            if (!$vieneDeTrazabilidad) {
                Log::info('Envío no viene de Trazabilidad, no se genera propuesta de vehículos', [
                    'envio_id' => $envio->id,
                ]);
                return null;
            }

            // Cargar relaciones necesarias
            $envio->load(['almacenDestino', 'productos.producto', 'productos.tipoEmpaque']);

            // Calcular propuesta de vehículos
            $propuestaService = new \App\Services\PropuestaVehiculosService();
            $propuesta = $propuestaService->calcularPropuestaVehiculos($envio);

            // Generar PDF directamente
            $pdf = Pdf::loadView('envios.pdf.propuesta-vehiculos', compact('propuesta'));
            $pdf->setPaper('a4', 'portrait');
            
            $pdfContent = $pdf->output();
            return base64_encode($pdfContent);
        } catch (\Exception $e) {
            Log::error('Error obteniendo propuesta de vehículos PDF', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Enviar documentos a Trazabilidad
     * 
     * @param Envio $envio
     * @param array $documentos
     * @return bool
     */
    private function enviarDocumentosATrazabilidad(Envio $envio, array $documentos): bool
    {
        try {
            $trazabilidadApiUrl = env('TRAZABILIDAD_API_URL', 'http://localhost:8000/api');
            
            // Buscar el pedido_id de Trazabilidad desde las observaciones del envío
            $pedidoTrazabilidadId = $this->extractPedidoTrazabilidadId($envio);
            
            if (!$pedidoTrazabilidadId) {
                Log::info('Envío no tiene relación con pedido de Trazabilidad, no se envían documentos', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                ]);
                return false;
            }

            // Cargar relaciones necesarias
            $envio->load(['asignacion.transportista']);

            // Preparar datos para Trazabilidad
            $data = [
                'pedido_id' => $pedidoTrazabilidadId,
                'envio_id' => $envio->id,
                'envio_codigo' => $envio->codigo,
                'fecha_entrega' => $envio->fecha_entrega?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                'transportista_nombre' => $envio->asignacion->transportista->name ?? 'N/A',
                'documentos' => [
                    'propuesta_vehiculos' => $documentos['propuesta_vehiculos'] ?? null,
                    'nota_entrega' => $documentos['nota_entrega'] ?? null,
                    'trazabilidad_completa' => $documentos['trazabilidad_completa'] ?? null,
                ],
            ];

            $endpoint = "{$trazabilidadApiUrl}/pedidos/{$pedidoTrazabilidadId}/documentos-entrega";
            
            Log::info('Enviando documentos de entrega a Trazabilidad', [
                'endpoint' => $endpoint,
                'envio_id' => $envio->id,
                'pedido_id' => $pedidoTrazabilidadId,
            ]);

            $response = Http::timeout(30)->post($endpoint, $data);

            if ($response->successful()) {
                Log::info('✅ Documentos enviados exitosamente a Trazabilidad', [
                    'envio_id' => $envio->id,
                    'codigo' => $envio->codigo,
                ]);
                return true;
            } else {
                Log::error('❌ Error enviando documentos a Trazabilidad', [
                    'envio_id' => $envio->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar documentos a Trazabilidad', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Extraer pedido_id de Trazabilidad desde observaciones del envío o desde order_envio_tracking
     * 
     * @param Envio $envio
     * @return string|null pedido_id de Trazabilidad (puede ser string como "P1000001")
     */
    private function extractPedidoTrazabilidadId(Envio $envio): ?string
    {
        // PRIMERO: Intentar buscar en la tabla order_envio_tracking de Trazabilidad vía API
        try {
            $trazabilidadApiUrl = env('TRAZABILIDAD_API_URL', 'http://localhost:8000/api');
            $response = Http::timeout(5)->get("{$trazabilidadApiUrl}/pedidos/by-envio/{$envio->id}");
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['pedido_id'])) {
                    Log::info('Pedido de Trazabilidad encontrado vía API', [
                        'envio_id' => $envio->id,
                        'pedido_id' => $data['pedido_id'],
                    ]);
                    return (string) $data['pedido_id'];
                }
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo obtener pedido desde API de Trazabilidad', [
                'envio_id' => $envio->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        // SEGUNDO: Buscar en observaciones del envío
        $observaciones = $envio->observaciones ?? '';
        
        // Buscar patrones como "Pedido Trazabilidad: P1000001" o "pedido_id_trazabilidad: 123"
        if (preg_match('/pedido.*trazabilidad[:\s]+([A-Z]?\d+)/i', $observaciones, $matches)) {
            return $matches[1];
        }
        
        // Buscar en formato "Trazabilidad Pedido ID: 123"
        if (preg_match('/trazabilidad.*pedido.*id[:\s]+([A-Z]?\d+)/i', $observaciones, $matches)) {
            return $matches[1];
        }
        
        // Buscar en formato "Pedido: P1000001" que viene de Trazabilidad
        if (preg_match('/pedido[:\s]+([A-Z]?\d+)/i', $observaciones, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}

