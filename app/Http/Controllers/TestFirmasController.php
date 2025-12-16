<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use Illuminate\Http\Request;

class TestFirmasController extends Controller
{
    /**
     * Mostrar todas las firmas guardadas en la base de datos
     */
    public function index()
    {
        // Obtener todos los envíos que tienen firma
        $envios = Envio::with(['asignacion.transportista', 'almacenDestino'])
            ->whereNotNull('firma_transportista')
            ->where('firma_transportista', '!=', '')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Procesar las firmas para determinar si son base64 o texto
        $firmas = [];
        foreach ($envios as $envio) {
            $firma = $envio->firma_transportista;
            $esBase64 = false;
            $firmaBase64 = null;
            
            // Verificar si es base64
            if (strpos($firma, 'data:image') === 0) {
                $esBase64 = true;
                $firmaBase64 = $firma;
            } elseif (preg_match('/^[A-Za-z0-9+\/]+=*$/', $firma) && strlen($firma) > 100) {
                $esBase64 = true;
                $firmaBase64 = 'data:image/png;base64,' . $firma;
            }
            
            $firmas[] = [
                'envio' => $envio,
                'esBase64' => $esBase64,
                'firmaBase64' => $firmaBase64,
                'firmaTexto' => $esBase64 ? null : $firma,
                'longitud' => strlen($firma),
                'transportista' => $envio->asignacion && $envio->asignacion->transportista 
                    ? $envio->asignacion->transportista->name 
                    : 'N/A',
            ];
        }

        return view('test.firmas', compact('firmas'));
    }
}

