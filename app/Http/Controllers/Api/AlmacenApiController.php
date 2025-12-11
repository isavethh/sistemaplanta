<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use Illuminate\Http\Request;

class AlmacenApiController extends Controller
{
    /**
     * Listar todos los almacenes activos
     * GET /api/almacenes
     */
    public function index()
    {
        try {
            $almacenes = Almacen::where('activo', true)
                ->select(
                    'id', 
                    'nombre', 
                    'direccion_completa as direccion', 
                    'latitud', 
                    'longitud', 
                    'activo',
                    'es_planta'
                )
                ->orderBy('es_planta', 'desc') // Plantas primero
                ->orderBy('nombre')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $almacenes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en AlmacenApiController::index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener almacenes: ' . $e->getMessage()
            ], 500);
        }
    }
}

