<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlmacenApiController extends Controller
{
    /**
     * Listar todos los almacenes activos
     * GET /api/almacenes
     * 
     * Ayuda: Permite a sistemas externos (Node.js, app móvil) obtener lista de almacenes
     * para mostrar en mapas, selección de destinos, etc.
     */
    public function index()
    {
        try {
            $almacenes = Almacen::where('activo', true)
                ->with('usuarioAlmacen:id,name,email')
                ->select(
                    'id', 
                    'nombre', 
                    'direccion_completa as direccion', 
                    'latitud', 
                    'longitud', 
                    'activo',
                    'es_planta',
                    'usuario_almacen_id'
                )
                ->orderBy('es_planta', 'desc') // Plantas primero
                ->orderBy('nombre')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $almacenes,
                'total' => $almacenes->count()
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

    /**
     * Mostrar detalles de un almacén específico
     * GET /api/almacenes/{id}
     * 
     * Ayuda: Obtener información completa de un almacén incluyendo inventario,
     * envíos recientes, estadísticas. Útil para dashboards y reportes.
     */
    public function show($id)
    {
        try {
            $almacen = Almacen::with([
                'usuarioAlmacen:id,name,email,telefono',
                'inventario.producto:id,nombre'
            ])
            ->findOrFail($id);

            // Estadísticas del almacén
            $estadisticas = [
                'total_envios_recibidos' => \App\Models\Envio::where('almacen_destino_id', $id)
                    ->where('estado', 'entregado')
                    ->count(),
                'envios_pendientes' => \App\Models\Envio::where('almacen_destino_id', $id)
                    ->whereIn('estado', ['pendiente', 'asignado', 'en_transito'])
                    ->count(),
                'total_productos_inventario' => $almacen->inventario->sum('cantidad'),
            ];

            return response()->json([
                'success' => true,
                'data' => $almacen,
                'estadisticas' => $estadisticas
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Almacén no encontrado'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error en AlmacenApiController::show', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener almacén: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo almacén
     * POST /api/almacenes
     * 
     * Ayuda: Permite crear almacenes desde sistemas externos (integración con otros sistemas),
     * sincronización automática, o creación masiva desde archivos CSV.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'direccion_completa' => 'required|string',
                'latitud' => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180',
                'es_planta' => 'boolean',
                'activo' => 'boolean',
                'usuario_almacen_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $almacen = Almacen::create([
                'nombre' => $request->nombre,
                'direccion_completa' => $request->direccion_completa,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'es_planta' => $request->es_planta ?? false,
                'activo' => $request->activo ?? true,
                'usuario_almacen_id' => $request->usuario_almacen_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Almacén creado correctamente',
                'data' => $almacen
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error en AlmacenApiController::store', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear almacén: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar almacén
     * PUT/PATCH /api/almacenes/{id}
     * 
     * Ayuda: Actualizar información de almacenes desde sistemas externos,
     * corregir coordenadas GPS, cambiar direcciones, activar/desactivar.
     */
    public function update(Request $request, $id)
    {
        try {
            $almacen = Almacen::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:255',
                'direccion_completa' => 'sometimes|string',
                'latitud' => 'sometimes|numeric|between:-90,90',
                'longitud' => 'sometimes|numeric|between:-180,180',
                'es_planta' => 'boolean',
                'activo' => 'boolean',
                'usuario_almacen_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $almacen->update($request->only([
                'nombre',
                'direccion_completa',
                'latitud',
                'longitud',
                'es_planta',
                'activo',
                'usuario_almacen_id'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Almacén actualizado correctamente',
                'data' => $almacen->fresh()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Almacén no encontrado'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error en AlmacenApiController::update', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar almacén: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar/Eliminar almacén
     * DELETE /api/almacenes/{id}
     * 
     * Ayuda: Desactivar almacenes sin eliminarlos físicamente (soft delete),
     * útil para mantener historial. Previene asignar envíos a almacenes inactivos.
     */
    public function destroy($id)
    {
        try {
            $almacen = Almacen::findOrFail($id);

            // Verificar si tiene envíos pendientes
            $enviosPendientes = \App\Models\Envio::where('almacen_destino_id', $id)
                ->whereIn('estado', ['pendiente', 'asignado', 'en_transito'])
                ->count();

            if ($enviosPendientes > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede desactivar el almacén. Tiene {$enviosPendientes} envío(s) pendiente(s).",
                    'envios_pendientes' => $enviosPendientes
                ], 422);
            }

            // Desactivar en lugar de eliminar
            $almacen->update(['activo' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Almacén desactivado correctamente'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Almacén no encontrado'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error en AlmacenApiController::destroy', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar almacén: ' . $e->getMessage()
            ], 500);
        }
    }
}

