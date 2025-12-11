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
        $almacenes = Almacen::where('activo', true)
            ->select('id', 'nombre', 'direccion_completa as direccion', 'latitud', 'longitud', 'activo')
            ->orderBy('nombre')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $almacenes
        ]);
    }
}

