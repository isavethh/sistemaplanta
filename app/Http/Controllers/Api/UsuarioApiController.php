<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsuarioApiController extends Controller
{
    /**
     * Listar todos los usuarios
     * GET /api/usuarios
     */
    public function index()
    {
        $usuarios = User::select('id', 'name as nombre', 'email', 'role as rol_nombre')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $usuarios
        ]);
    }
}

