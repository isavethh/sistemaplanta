<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard Estadístico Completo
     */
    public function estadistico()
    {
        // =====================================
        // MÉTRICAS KPI PRINCIPALES
        // =====================================
        $kpis = $this->obtenerKPIs();

        // =====================================
        // GRÁFICO 1: Envíos por Estado (Dona)
        // =====================================
        $enviosPorEstado = DB::table('envios')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->get();

        // =====================================
        // GRÁFICO 2: Tendencia de Envíos (Últimos 6 meses)
        // =====================================
        $tendenciaEnvios = $this->obtenerTendenciaEnvios();

        // =====================================
        // GRÁFICO 3: Top 5 Almacenes con más envíos
        // =====================================
        $topAlmacenes = DB::table('envios as e')
            ->join('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->select('a.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('a.id', 'a.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // =====================================
        // GRÁFICO 4: Top 5 Transportistas
        // =====================================
        $topTransportistas = DB::table('envio_asignaciones as ea')
            ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
            ->join('users as u', 'v.transportista_id', '=', 'u.id')
            ->join('envios as e', 'ea.envio_id', '=', 'e.id')
            ->where('e.estado', 'entregado')
            ->select('u.name', DB::raw('COUNT(*) as entregas'))
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('entregas')
            ->limit(5)
            ->get();

        // =====================================
        // GRÁFICO 5: Incidentes por Tipo
        // =====================================
        $incidentesPorTipo = DB::table('incidentes')
            ->select('tipo_incidente', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_incidente')
            ->orderByDesc('total')
            ->get();

        // =====================================
        // MÉTRICAS DE RENDIMIENTO
        // =====================================
        $rendimiento = $this->calcularRendimiento();

        // =====================================
        // ENVÍOS RECIENTES
        // =====================================
        $enviosRecientes = DB::table('envios as e')
            ->leftJoin('almacenes as a', 'e.almacen_destino_id', '=', 'a.id')
            ->select('e.*', 'a.nombre as almacen_nombre')
            ->orderByDesc('e.created_at')
            ->limit(5)
            ->get();

        // =====================================
        // ACTIVIDAD POR DÍA DE LA SEMANA
        // =====================================
        $actividadSemanal = $this->obtenerActividadSemanal();

        return view('dashboard-estadistico', compact(
            'kpis',
            'enviosPorEstado',
            'tendenciaEnvios',
            'topAlmacenes',
            'topTransportistas',
            'incidentesPorTipo',
            'rendimiento',
            'enviosRecientes',
            'actividadSemanal'
        ));
    }

    /**
     * Obtener KPIs principales
     */
    private function obtenerKPIs(): array
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $mesAnterior = Carbon::now()->subMonth();

        // Envíos del mes actual
        $enviosMesActual = DB::table('envios')
            ->where('fecha_creacion', '>=', $inicioMes)
            ->count();

        // Envíos del mes anterior
        $enviosMesAnterior = DB::table('envios')
            ->whereBetween('fecha_creacion', [$mesAnterior->startOfMonth(), $mesAnterior->endOfMonth()])
            ->count();

        // Calcular crecimiento
        $crecimiento = $enviosMesAnterior > 0 
            ? round((($enviosMesActual - $enviosMesAnterior) / $enviosMesAnterior) * 100, 1)
            : 0;

        return [
            'total_envios' => DB::table('envios')->count(),
            'envios_mes' => $enviosMesActual,
            'crecimiento_mensual' => $crecimiento,
            'envios_hoy' => DB::table('envios')->whereDate('fecha_creacion', $hoy)->count(),
            'en_transito' => DB::table('envios')->where('estado', 'en_transito')->count(),
            'entregados_mes' => DB::table('envios')
                ->where('estado', 'entregado')
                ->where('fecha_creacion', '>=', $inicioMes)
                ->count(),
            'pendientes' => DB::table('envios')->where('estado', 'pendiente')->count(),
            'incidentes_activos' => DB::table('incidentes')
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->count(),
            'total_transportistas' => DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'transportista')
                ->count(),
            'total_almacenes' => DB::table('almacenes')->where('es_planta', false)->count(),
            'peso_total_mes' => DB::table('envios')
                ->where('fecha_creacion', '>=', $inicioMes)
                ->sum('total_peso') ?? 0,
            'valor_total_mes' => DB::table('envios')
                ->where('fecha_creacion', '>=', $inicioMes)
                ->sum('total_precio') ?? 0,
        ];
    }

    /**
     * Obtener tendencia de envíos últimos 6 meses
     */
    private function obtenerTendenciaEnvios(): array
    {
        $datos = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $inicio = $mes->copy()->startOfMonth();
            $fin = $mes->copy()->endOfMonth();

            $total = DB::table('envios')
                ->whereBetween('fecha_creacion', [$inicio, $fin])
                ->count();

            $entregados = DB::table('envios')
                ->where('estado', 'entregado')
                ->whereBetween('fecha_creacion', [$inicio, $fin])
                ->count();

            $datos[] = [
                'mes' => $mes->locale('es')->isoFormat('MMM'),
                'total' => $total,
                'entregados' => $entregados,
            ];
        }

        return $datos;
    }

    /**
     * Calcular métricas de rendimiento
     */
    private function calcularRendimiento(): array
    {
        $totalEnvios = DB::table('envios')->count();
        $entregados = DB::table('envios')->where('estado', 'entregado')->count();
        
        // Tasa de entrega
        $tasaEntrega = $totalEnvios > 0 ? round(($entregados / $totalEnvios) * 100, 1) : 0;

        // Tiempo promedio de entrega (de asignación a entrega)
        $tiempoPromedio = DB::table('envios')
            ->whereNotNull('fecha_asignacion')
            ->whereNotNull('fecha_entrega')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (fecha_entrega::timestamp - fecha_asignacion::timestamp))/3600) as horas')
            ->first();

        $horasPromedio = $tiempoPromedio->horas ?? 0;
        
        // Tasa de incidentes
        $totalIncidentes = DB::table('incidentes')->count();
        $tasaIncidentes = $totalEnvios > 0 ? round(($totalIncidentes / $totalEnvios) * 100, 1) : 0;

        // Tasa de resolución de incidentes
        $incidentesResueltos = DB::table('incidentes')->where('estado', 'resuelto')->count();
        $tasaResolucion = $totalIncidentes > 0 ? round(($incidentesResueltos / $totalIncidentes) * 100, 1) : 0;

        return [
            'tasa_entrega' => $tasaEntrega,
            'tiempo_promedio_horas' => round($horasPromedio, 1),
            'tiempo_promedio_texto' => $horasPromedio < 24 
                ? round($horasPromedio, 1) . ' horas' 
                : round($horasPromedio / 24, 1) . ' días',
            'tasa_incidentes' => $tasaIncidentes,
            'tasa_resolucion' => $tasaResolucion,
        ];
    }

    /**
     * Obtener actividad por día de la semana
     */
    private function obtenerActividadSemanal(): array
    {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $datos = [];

        $resultados = DB::table('envios')
            ->selectRaw("EXTRACT(DOW FROM fecha_creacion::timestamp) as dia, COUNT(*) as total")
            ->groupBy('dia')
            ->orderBy('dia')
            ->pluck('total', 'dia');

        for ($i = 0; $i < 7; $i++) {
            $datos[] = [
                'dia' => $dias[$i],
                'total' => $resultados[$i] ?? 0
            ];
        }

        return $datos;
    }
}

