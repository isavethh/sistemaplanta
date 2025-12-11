<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatosCompletosSeeder extends Seeder
{
    public function run()
    {
        // Obtener IDs necesarios
        $envios = DB::table('envios')->pluck('id')->toArray();
        $transportistas = DB::table('users')->where('role', 'transportista')->pluck('id')->toArray();
        
        if (empty($envios) || empty($transportistas)) {
            echo "❌ No hay envíos o transportistas. Por favor ejecuta primero los seeders base.\n";
            return;
        }

        // Crear incidentes variados
        $tiposIncidentes = ['accidente', 'retraso', 'daño_mercancia', 'problema_mecanico', 'documentacion', 'clima_adverso'];
        $estados = ['pendiente', 'en_proceso', 'resuelto'];
        
        echo "🚨 Creando incidentes de prueba...\n";
        
        for ($i = 0; $i < 20; $i++) {
            $fechaReporte = Carbon::now()->subDays(rand(1, 30));
            $estado = $estados[array_rand($estados)];
            $envioId = $envios[array_rand($envios)];
            $transportistaId = $transportistas[array_rand($transportistas)];
            
            // Obtener asignación del envío
            $asignacion = DB::table('envio_asignaciones')
                ->where('envio_id', $envioId)
                ->first();
            
            if (!$asignacion) continue;
            
            $tipoIncidente = $tiposIncidentes[array_rand($tiposIncidentes)];
            
            $incidenteId = DB::table('incidentes')->insertGetId([
                'envio_id' => $envioId,
                'tipo_incidente' => $tipoIncidente,
                'descripcion' => $this->generarDescripcion($tipoIncidente),
                'estado' => $estado,
                'fecha_reporte' => $fechaReporte,
                'fecha_resolucion' => $estado === 'resuelto' ? $fechaReporte->copy()->addDays(rand(1, 5)) : null,
                'notas_resolucion' => $estado === 'resuelto' ? $this->generarNotasResolucion() : null,
                'foto_url' => null,
                'created_at' => $fechaReporte,
                'updated_at' => now(),
            ]);
            
            echo "  ✅ Incidente #{$incidenteId} creado para envío #{$envioId}\n";
        }

        // Actualizar algunos envíos con diferentes estados
        echo "\n📦 Actualizando estados de envíos...\n";
        
        $enviosParaActualizar = DB::table('envios')->inRandomOrder()->limit(10)->get();
        
        foreach ($enviosParaActualizar as $envio) {
            $nuevoEstado = ['pendiente', 'asignado', 'en_transito', 'entregado'][rand(0, 3)];
            
            $datos = ['estado' => $nuevoEstado];
            
            if ($nuevoEstado === 'asignado') {
                $datos['fecha_asignacion'] = Carbon::now()->subDays(rand(1, 10));
            } elseif ($nuevoEstado === 'en_transito') {
                $datos['fecha_asignacion'] = Carbon::now()->subDays(rand(5, 15));
                $datos['fecha_inicio_transito'] = Carbon::now()->subDays(rand(1, 5));
            } elseif ($nuevoEstado === 'entregado') {
                $datos['fecha_asignacion'] = Carbon::now()->subDays(rand(10, 30));
                $datos['fecha_inicio_transito'] = Carbon::now()->subDays(rand(5, 20));
                $datos['fecha_entrega'] = Carbon::now()->subDays(rand(1, 5));
            }
            
            DB::table('envios')->where('id', $envio->id)->update($datos);
            echo "  ✅ Envío #{$envio->codigo} actualizado a: {$nuevoEstado}\n";
        }

        echo "\n✅ Seeder completado exitosamente!\n";
        echo "📊 Total de incidentes creados: 20\n";
        echo "📦 Envíos actualizados: 10\n";
    }

    private function generarDescripcion($tipo)
    {
        $descripciones = [
            'accidente' => [
                'Colisión menor en la vía principal, sin heridos. Daños menores en el vehículo.',
                'Accidente de tránsito con vehículo particular. Esperando a la policía.',
                'Volcadura del vehículo en curva cerrada. Carga asegurada, revisando daños.',
            ],
            'retraso' => [
                'Tráfico intenso en la carretera principal. Estimamos 2 horas de retraso.',
                'Cierre temporal de vía por manifestación. Buscando ruta alterna.',
                'Congestión vehicular debido a accidente en la zona. Demora considerable.',
            ],
            'daño_mercancia' => [
                'Cajas externas con abolladuras detectadas durante revisión de rutina.',
                'Posible filtración de humedad en 3 paquetes. Se requiere inspección.',
                'Daño menor en embalaje durante carga. Contenido aparentemente intacto.',
            ],
            'problema_mecanico' => [
                'Falla en el sistema de refrigeración del vehículo. En espera de mecánico.',
                'Pinchazo de llanta en la autopista. Realizando cambio de neumático.',
                'Problema con el motor, vehículo pierde potencia. Solicito asistencia técnica.',
            ],
            'documentacion' => [
                'Documentación de carga incompleta en punto de control. Gestionando corrección.',
                'Discrepancia entre guía de remisión y contenido físico. Requiere verificación.',
                'Falta sello de aduana en documentos de exportación. Retorno a oficina necesario.',
            ],
            'clima_adverso' => [
                'Lluvia intensa dificulta la visibilidad. Detenido en paradero seguro.',
                'Neblina espesa en la zona montañosa. Esperando mejora de condiciones.',
                'Granizada inesperada, vía resbaladiza. Continuando con precaución extrema.',
            ],
        ];
        
        return $descripciones[$tipo][array_rand($descripciones[$tipo])];
    }

    private function generarNotasResolucion()
    {
        $notas = [
            'Incidente resuelto satisfactoriamente. Se procedió con la entrega sin mayores contratiempos.',
            'Problema solucionado en campo. Cliente notificado y conforme con la resolución.',
            'Se realizaron las reparaciones necesarias. Vehículo en condiciones óptimas para continuar.',
            'Documentación corregida y validada. Envío autorizado para continuar su ruta.',
            'Condiciones climáticas mejoraron. Se reanudó la ruta sin incidencias adicionales.',
            'Daños evaluados y reportados al seguro. Cliente recibió compensación correspondiente.',
            'Carga inspeccionada por supervisor. Se autorizó continuación del transporte.',
            'Ruta alterna implementada exitosamente. Tiempo de retraso minimizado.',
        ];
        
        return $notas[array_rand($notas)];
    }
}

