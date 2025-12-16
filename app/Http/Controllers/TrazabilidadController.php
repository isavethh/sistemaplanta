<?php

namespace App\Http\Controllers;

use App\Models\PedidoAlmacen;
use App\Models\Envio;
use App\Services\PropuestaEnvioPdfService;
use App\Services\CubicajeInteligenteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrazabilidadController extends Controller
{
    protected $propuestaPdfService;
    protected $cubicajeService;

    public function __construct(
        PropuestaEnvioPdfService $propuestaPdfService,
        CubicajeInteligenteService $cubicajeService
    ) {
        $this->propuestaPdfService = $propuestaPdfService;
        $this->cubicajeService = $cubicajeService;
        $this->middleware('auth');
    }

    /**
     * Listar pedidos pendientes de almacenes
     */
    public function pedidosPendientes()
    {
        $user = Auth::user();
        
        if (!$user->esOperador()) {
            abort(403, 'Solo los operadores de trazabilidad pueden ver pedidos pendientes.');
        }

        $pedidos = PedidoAlmacen::with(['almacen', 'productos', 'propietario'])
            ->where('estado', 'enviado_trazabilidad')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('trazabilidad.pedidos-pendientes', compact('pedidos'));
    }

    /**
     * Aceptar pedido de almacén
     */
    public function aceptarPedido(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->esOperador()) {
            abort(403, 'Solo los operadores pueden aceptar pedidos.');
        }

        $pedido = PedidoAlmacen::with(['productos', 'almacen'])
            ->where('estado', 'enviado_trazabilidad')
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            // Aceptar pedido
            $pedido->aceptarEnTrazabilidad();

            // Generar propuesta PDF automáticamente
            $pdfBase64 = $this->propuestaPdfService->generarPropuestaPdfBase64($pedido);
            
            if ($pdfBase64) {
                // Guardar ruta del PDF en el pedido (si se guardó en storage)
                $pedido->marcarPropuestaEnviada();
                
                Log::info('Propuesta PDF generada y enviada a Trazabilidad', [
                    'pedido_id' => $pedido->id,
                    'codigo' => $pedido->codigo,
                ]);
            }

            DB::commit();

            return redirect()->route('trazabilidad.propuestas-envios')
                ->with('success', 'Pedido aceptado y propuesta generada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error aceptando pedido', [
                'pedido_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al aceptar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar pedido de almacén
     */
    public function rechazarPedido(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->esOperador()) {
            abort(403, 'Solo los operadores pueden rechazar pedidos.');
        }

        $request->validate([
            'motivo' => 'required|string|min:10',
        ]);

        $pedido = PedidoAlmacen::where('estado', 'enviado_trazabilidad')
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $pedido->update([
                'estado' => 'cancelado',
                'observaciones' => ($pedido->observaciones ?? '') . "\n\nRechazado por Trazabilidad: " . $request->motivo,
            ]);

            DB::commit();

            return redirect()->route('trazabilidad.pedidos-pendientes')
                ->with('success', 'Pedido rechazado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rechazando pedido', [
                'pedido_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al rechazar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Ver propuestas de envíos (PlanTrack)
     */
    public function propuestasEnvios()
    {
        $user = Auth::user();
        
        if (!$user->esOperador()) {
            abort(403, 'Solo los operadores pueden ver propuestas.');
        }

        $pedidos = PedidoAlmacen::with(['almacen', 'productos', 'propietario', 'envio'])
            ->whereIn('estado', ['propuesta_enviada', 'propuesta_aceptada'])
            ->orderBy('fecha_propuesta_enviada', 'desc')
            ->get();

        return view('trazabilidad.propuestas-envios', compact('pedidos'));
    }

    /**
     * Ver detalle de propuesta
     */
    public function verPropuesta($id)
    {
        $user = Auth::user();
        
        if (!$user->esOperador()) {
            abort(403, 'Solo los operadores pueden ver propuestas.');
        }

        $pedido = PedidoAlmacen::with(['almacen', 'productos', 'propietario'])
            ->findOrFail($id);

        // Calcular cubicaje
        $cubicaje = $this->cubicajeService->calcularCubicaje($pedido);

        return view('trazabilidad.ver-propuesta', compact('pedido', 'cubicaje'));
    }

    /**
     * Descargar PDF de propuesta
     */
    public function descargarPropuestaPdf($id)
    {
        $user = Auth::user();
        
        // Permitir a operadores y admins descargar el PDF
        if (!$user->esOperador() && !$user->hasRole('admin')) {
            abort(403, 'No tienes permiso para descargar esta propuesta.');
        }

        $pedido = PedidoAlmacen::with(['almacen', 'productos', 'propietario'])
            ->findOrFail($id);

        try {
            // Calcular cubicaje
            $cubicaje = $this->cubicajeService->calcularCubicaje($pedido);

            // Obtener planta
            $planta = \App\Models\Almacen::where('es_planta', true)->first();

            // Generar PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pedidos-almacen.pdf.propuesta-envio', compact(
                'pedido', 'cubicaje', 'planta'
            ));
            $pdf->setPaper('a4', 'portrait');

            // Nombre del archivo
            $filename = 'Propuesta-Envio-' . $pedido->codigo . '-' . now()->format('YmdHis') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error descargando PDF de propuesta', [
                'pedido_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Aprobar propuesta de envío
     */
    public function aprobarPropuesta(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->esOperador()) {
            abort(403, 'Solo los operadores pueden aprobar propuestas.');
        }

        $pedido = PedidoAlmacen::with(['productos', 'almacen'])
            ->where('estado', 'propuesta_enviada')
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            // Aceptar propuesta
            $pedido->aceptarPropuesta();

            // Crear envío en plantacruds (ya aprobado por operador, listo para asignar)
            $envio = $this->crearEnvioDesdePedido($pedido, true); // true = ya aprobado

            // Asociar envío al pedido
            $pedido->update(['envio_id' => $envio->id]);

            DB::commit();

            Log::info('Propuesta aprobada y envío creado', [
                'pedido_id' => $pedido->id,
                'envio_id' => $envio->id,
            ]);

            return redirect()->route('trazabilidad.propuestas-envios')
                ->with('success', 'Propuesta aprobada y envío creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error aprobando propuesta', [
                'pedido_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al aprobar la propuesta: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar propuesta de envío
     */
    public function rechazarPropuesta(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->esOperador()) {
            abort(403, 'Solo los operadores pueden rechazar propuestas.');
        }

        $request->validate([
            'motivo' => 'required|string|min:10',
        ]);

        $pedido = PedidoAlmacen::where('estado', 'propuesta_enviada')
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $pedido->update([
                'estado' => 'aceptado_trazabilidad', // Volver a estado anterior
                'observaciones' => ($pedido->observaciones ?? '') . "\n\nPropuesta rechazada: " . $request->motivo,
            ]);

            DB::commit();

            return redirect()->route('trazabilidad.propuestas-envios')
                ->with('success', 'Propuesta rechazada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rechazando propuesta', [
                'pedido_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al rechazar la propuesta: ' . $e->getMessage());
        }
    }

    /**
     * Crear envío desde pedido aceptado
     * @param PedidoAlmacen $pedido
     * @param bool $yaAprobado Si es true, el envío se crea en estado 'pendiente' (ya aprobado por operador)
     */
    private function crearEnvioDesdePedido(PedidoAlmacen $pedido, bool $yaAprobado = false): Envio
    {
        $pedido->load('productos');

        // Generar código de envío
        $codigo = 'ENV-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Determinar estado inicial
        // Si ya fue aprobado por el operador, va directo a 'pendiente' para asignación
        // Si no, va a 'pendiente_aprobacion_trazabilidad' para aprobación en Planta
        $estadoInicial = $yaAprobado ? 'pendiente' : 'pendiente_aprobacion_trazabilidad';

        // Crear envío
        $envio = Envio::create([
            'codigo' => $codigo,
            'almacen_destino_id' => $pedido->almacen_id,
            'categoria' => 'General',
            'fecha_creacion' => now(),
            'fecha_estimada_entrega' => $pedido->fecha_requerida,
            'hora_estimada' => $pedido->hora_requerida,
            'estado' => $estadoInicial,
            'total_cantidad' => $pedido->productos->sum('cantidad'),
            'total_peso' => $pedido->productos->sum('total_peso'),
            'total_precio' => $pedido->productos->sum('total_precio'),
            'observaciones' => 'Envío creado desde pedido de almacén: ' . $pedido->codigo . "\n" . ($pedido->observaciones ?? ''),
            'pedido_almacen_id' => $pedido->id,
        ]);

        // Crear productos del envío
        foreach ($pedido->productos as $productoPedido) {
            \App\Models\EnvioProducto::create([
                'envio_id' => $envio->id,
                'producto_nombre' => $productoPedido->producto_nombre,
                'cantidad' => $productoPedido->cantidad,
                'peso_unitario' => $productoPedido->peso_unitario,
                'precio_unitario' => $productoPedido->precio_unitario,
                'total_peso' => $productoPedido->total_peso,
                'total_precio' => $productoPedido->total_precio,
            ]);
        }

        return $envio;
    }

    /**
     * Ver pedidos aceptados
     */
    public function pedidosAceptados()
    {
        $user = Auth::user();
        
        if (!$user->esOperador()) {
            abort(403, 'Solo los operadores pueden ver pedidos aceptados.');
        }

        $pedidos = PedidoAlmacen::with(['almacen', 'productos', 'propietario', 'envio'])
            ->whereIn('estado', ['propuesta_aceptada', 'entregado'])
            ->orderBy('fecha_propuesta_aceptada', 'desc')
            ->get();

        return view('trazabilidad.pedidos-aceptados', compact('pedidos'));
    }
}
