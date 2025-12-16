<?php

namespace App\Services;

use App\Models\PedidoAlmacen;
use App\Services\CubicajeInteligenteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PropuestaEnvioPdfService
{
    protected $cubicajeService;

    public function __construct(CubicajeInteligenteService $cubicajeService)
    {
        $this->cubicajeService = $cubicajeService;
    }

    /**
     * Generar PDF de propuesta de envío con cubicaje inteligente
     */
    public function generarPropuestaPdf(PedidoAlmacen $pedido): ?string
    {
        try {
            $pedido->load(['productos', 'almacen', 'propietario']);

            // Calcular cubicaje
            $cubicaje = $this->cubicajeService->calcularCubicaje($pedido);

            // Obtener planta
            $planta = \App\Models\Almacen::where('es_planta', true)->first();

            // Generar PDF
            $pdf = Pdf::loadView('pedidos-almacen.pdf.propuesta-envio', compact(
                'pedido', 'cubicaje', 'planta'
            ));
            $pdf->setPaper('a4', 'portrait');

            // Guardar en storage
            $filename = 'propuestas/propuesta-' . $pedido->codigo . '-' . now()->format('YmdHis') . '.pdf';
            Storage::disk('public')->put($filename, $pdf->output());

            // Actualizar pedido con ruta del PDF
            $pedido->update([
                'fecha_propuesta_enviada' => now(),
            ]);

            Log::info('PDF de propuesta generado exitosamente', [
                'pedido_id' => $pedido->id,
                'codigo' => $pedido->codigo,
                'filename' => $filename,
            ]);

            return Storage::disk('public')->path($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de propuesta', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Generar PDF y retornar como base64 para envío
     */
    public function generarPropuestaPdfBase64(PedidoAlmacen $pedido): ?string
    {
        try {
            $pedido->load(['productos', 'almacen', 'propietario']);

            // Calcular cubicaje
            $cubicaje = $this->cubicajeService->calcularCubicaje($pedido);

            // Obtener planta
            $planta = \App\Models\Almacen::where('es_planta', true)->first();

            // Generar PDF
            $pdf = Pdf::loadView('pedidos-almacen.pdf.propuesta-envio', compact(
                'pedido', 'cubicaje', 'planta'
            ));
            $pdf->setPaper('a4', 'portrait');

            return base64_encode($pdf->output());
        } catch (\Exception $e) {
            Log::error('Error generando PDF de propuesta (base64)', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}

