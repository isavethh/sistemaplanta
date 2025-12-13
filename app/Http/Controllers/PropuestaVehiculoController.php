<?php

namespace App\Http\Controllers;

use App\Models\PropuestaVehiculo;
use Illuminate\Http\Request;

class PropuestaVehiculoController extends Controller
{
    /**
     * Mostrar lista de propuestas de vehículos
     */
    public function index(Request $request)
    {
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

        $propuestas = $query->paginate(20);

        // Estadísticas
        $stats = [
            'total' => PropuestaVehiculo::count(),
            'aprobadas' => PropuestaVehiculo::where('estado', 'aprobada')->count(),
            'rechazadas' => PropuestaVehiculo::where('estado', 'rechazada')->count(),
            'pendientes' => PropuestaVehiculo::where('estado', 'pendiente')->count(),
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
