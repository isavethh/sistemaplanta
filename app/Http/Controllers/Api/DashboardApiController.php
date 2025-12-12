<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    /**
     * Obtener envíos filtrados por criterio
     * GET /api/dashboard/filtrar?tipo=estado&valor=pendiente
     */
    public function filtrar(Request $request)
    {
        $tipo = $request->get('tipo'); // estado, almacen, transportista, incidente_tipo, dia_semana
        $valor = $request->get('valor');
        $limite = $request->get('limite', 50);

        $query = DB::table('envios as e')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->leftJoin('users as u', 'v.transportista_id', '=', 'u.id')
            ->select(
                'e.id',
                'e.codigo',
                'e.estado',
                'e.fecha_creacion',
                'e.fecha_estimada_entrega',
                'e.total_peso',
                'e.total_precio',
                'e.total_cantidad',
                'a.nombre as almacen_nombre',
                'a.direccion_completa as almacen_direccion',
                'u.name as transportista_nombre',
                'v.placa as vehiculo_placa'
            );

        switch ($tipo) {
            case 'estado':
                $query->where('e.estado', $valor);
                break;

            case 'almacen':
                // Intentar buscar por ID primero, si no funciona buscar por nombre
                if (is_numeric($valor)) {
                    $query->where('a.id', $valor);
                } else {
                    $query->where('a.nombre', 'like', '%' . $valor . '%');
                }
                break;

            case 'transportista':
                $query->where('u.id', $valor)
                    ->where('e.estado', 'entregado');
                break;

            case 'incidente_tipo':
                $query->join('incidentes as i', 'e.id', '=', 'i.envio_id')
                    ->where('i.tipo_incidente', $valor)
                    ->distinct();
                break;

            case 'dia_semana':
                // 0 = Domingo, 1 = Lunes, etc.
                $query->whereRaw("EXTRACT(DOW FROM e.fecha_creacion::timestamp) = ?", [$valor]);
                break;

            case 'mes_tendencia':
                // Filtrar por mes específico (formato: YYYY-MM)
                $mes = Carbon::createFromFormat('Y-m', $valor);
                $query->whereBetween('e.fecha_creacion', [
                    $mes->startOfMonth(),
                    $mes->copy()->endOfMonth()
                ]);
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de filtro no válido'
                ], 400);
        }

        $envios = $query->orderByDesc('e.fecha_creacion')
            ->limit($limite)
            ->get();

        return response()->json([
            'success' => true,
            'tipo' => $tipo,
            'valor' => $valor,
            'total' => $envios->count(),
            'data' => $envios
        ]);
    }

    /**
     * Obtener detalles de un KPI específico
     * GET /api/dashboard/kpi?tipo=envios_hoy
     */
    public function kpiDetalle(Request $request)
    {
        $tipo = $request->get('tipo');
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();

        $query = DB::table('envios as e')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->select(
                'e.id',
                'e.codigo',
                'e.estado',
                'e.fecha_creacion',
                'e.total_peso',
                'e.total_precio',
                'a.nombre as almacen_nombre'
            );

        switch ($tipo) {
            case 'envios_hoy':
                $query->whereDate('e.fecha_creacion', $hoy);
                break;

            case 'envios_mes':
                $query->where('e.fecha_creacion', '>=', $inicioMes);
                break;

            case 'en_transito':
                $query->where('e.estado', 'en_transito');
                break;

            case 'pendientes':
                $query->where('e.estado', 'pendiente');
                break;

            case 'entregados_mes':
                $query->where('e.estado', 'entregado')
                    ->where('e.fecha_creacion', '>=', $inicioMes);
                break;

            case 'incidentes_activos':
                $query->join('incidentes as i', 'e.id', '=', 'i.envio_id')
                    ->whereIn('i.estado', ['pendiente', 'en_proceso'])
                    ->distinct();
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de KPI no válido'
                ], 400);
        }

        $envios = $query->orderByDesc('e.fecha_creacion')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'tipo' => $tipo,
            'total' => $envios->count(),
            'data' => $envios
        ]);
    }
}

