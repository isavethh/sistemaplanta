<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class IncidenteController extends Controller
{
    /**
     * Mostrar lista de todos los incidentes
     */
    public function index(Request $request)
    {
        // Marcar todos los incidentes pendientes como notificados cuando el admin los ve
        DB::table('incidentes')
            ->where('estado', 'pendiente')
            ->where('notificado_admin', false)
            ->update(['notificado_admin' => true]);
        
        $query = DB::table('incidentes as i')
            ->leftJoin('envios as e', 'i.envio_id', '=', 'e.id')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->select(
                'i.*',
                'e.codigo as envio_codigo',
                'e.estado as envio_estado',
                'a.nombre as almacen_nombre',
                DB::raw('COALESCE(i.solicitar_ayuda, false) as solicitar_ayuda')
            )
            ->orderBy('i.solicitar_ayuda', 'desc') // Priorizar solicitudes de ayuda
            ->orderBy('i.fecha_reporte', 'desc');

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('i.estado', $request->estado);
        }

        // Filtro por tipo de incidente
        if ($request->filled('tipo')) {
            $query->where('i.tipo_incidente', $request->tipo);
        }

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('e.codigo', 'ILIKE', "%{$buscar}%")
                  ->orWhere('a.nombre', 'ILIKE', "%{$buscar}%")
                  ->orWhere('i.descripcion', 'ILIKE', "%{$buscar}%");
            });
        }

        // Filtro por solicitud de ayuda (debe ir ANTES de paginar)
        if ($request->filled('solicitar_ayuda')) {
            $query->where('i.solicitar_ayuda', true);
        }

        $incidentes = $query->paginate(15);
        
        // Estadísticas
        $estadisticas = [
            'total' => DB::table('incidentes')->count(),
            'pendientes' => DB::table('incidentes')->where('estado', 'pendiente')->count(),
            'en_revision' => DB::table('incidentes')->where('estado', 'en_revision')->count(),
            'en_proceso' => DB::table('incidentes')->where('estado', 'en_proceso')->count(),
            'resueltos' => DB::table('incidentes')->where('estado', 'resuelto')->count(),
            'solicitan_ayuda' => DB::table('incidentes')->where('solicitar_ayuda', true)->where('estado', '!=', 'resuelto')->count(),
        ];

        return view('incidentes.index', compact('incidentes', 'estadisticas'));
    }

    /**
     * Mostrar detalle de un incidente
     */
    public function show($id)
    {
        $incidente = DB::table('incidentes as i')
            ->leftJoin('envios as e', 'i.envio_id', '=', 'e.id')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->select(
                'i.id',
                'i.envio_id',
                'i.transportista_id',
                'i.tipo_incidente',
                'i.descripcion',
                'i.foto_url',
                'i.estado',
                'i.fecha_reporte',
                'i.fecha_resolucion',
                'i.notas_resolucion',
                'i.accion',
                'i.created_at',
                'i.updated_at',
                'e.codigo as envio_codigo',
                'a.nombre as almacen_nombre',
                'a.direccion_completa as almacen_direccion',
                DB::raw('COALESCE(i.solicitar_ayuda, false) as solicitar_ayuda')
            )
            ->where('i.id', $id)
            ->first();

        if (!$incidente) {
            return redirect()->route('incidentes.index')->with('error', 'Incidente no encontrado');
        }

        // Obtener productos del envío
        $productos = DB::table('envio_productos')
            ->where('envio_id', $incidente->envio_id)
            ->get();

        // Obtener información del transportista si existe
        $transportista = null;
        if ($incidente->transportista_id) {
            $transportista = DB::table('users')
                ->where('id', $incidente->transportista_id)
                ->select('id', 'name', 'email', 'telefono')
                ->first();
        }

        return view('incidentes.show', compact('incidente', 'productos', 'transportista'));
    }

    /**
     * Cambiar estado del incidente
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_revision,en_proceso,resuelto',
            'notas' => 'nullable|string|max:1000'
        ]);

        $datos = [
            'estado' => $request->estado,
            'updated_at' => now()
        ];

        if ($request->estado === 'resuelto') {
            $datos['fecha_resolucion'] = now();
            $datos['notas_resolucion'] = $request->notas;
        }

        DB::table('incidentes')
            ->where('id', $id)
            ->update($datos);

        $mensaje = match($request->estado) {
            'pendiente' => 'Incidente marcado como pendiente',
            'en_revision' => 'Incidente marcado como en revisión',
            'en_proceso' => 'Incidente marcado como en proceso',
            'resuelto' => 'Incidente marcado como resuelto',
            default => 'Estado actualizado',
        };

        return redirect()->back()->with('success', $mensaje);
    }

    /**
     * Agregar nota al incidente
     */
    public function agregarNota(Request $request, $id)
    {
        $request->validate([
            'nota' => 'required|string|max:1000'
        ]);

        $incidente = DB::table('incidentes')->where('id', $id)->first();
        
        $notasActuales = $incidente->notas_resolucion ?? '';
        $nuevaNota = "[" . now()->format('d/m/Y H:i') . "] " . $request->nota;
        
        $notasActualizadas = $notasActuales ? $notasActuales . "\n" . $nuevaNota : $nuevaNota;

        DB::table('incidentes')
            ->where('id', $id)
            ->update([
                'notas_resolucion' => $notasActualizadas,
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Nota agregada correctamente');
    }
}
