<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioApiController extends Controller
{
    /**
     * Listar todos los usuarios
     * GET /api/usuarios
     * 
     * Ayuda: Obtener lista de usuarios para selección en formularios,
     * sincronización con otros sistemas, o gestión de usuarios desde app móvil.
     */
    public function index(Request $request)
    {
        try {
            $query = User::with('roles:id,name')
                ->select('id', 'name', 'email', 'telefono', 'licencia', 'created_at');

            // Filtro por rol
            if ($request->has('rol')) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('name', $request->rol);
                });
            }

            // Filtro por búsqueda
            if ($request->has('buscar')) {
                $buscar = $request->buscar;
                $query->where(function($q) use ($buscar) {
                    $q->where('name', 'like', "%{$buscar}%")
                      ->orWhere('email', 'like', "%{$buscar}%");
                });
            }

            $usuarios = $query->orderBy('name')->get();

            // Formatear respuesta
            $usuarios->transform(function($user) {
                return [
                    'id' => $user->id,
                    'nombre' => $user->name,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'licencia' => $user->licencia,
                    'roles' => $user->roles->pluck('name'),
                    'fecha_registro' => $user->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $usuarios,
                'total' => $usuarios->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en UsuarioApiController::index', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalles de un usuario específico
     * GET /api/usuarios/{id}
     * 
     * Ayuda: Obtener información completa de un usuario incluyendo roles,
     * permisos, vehículos asignados, estadísticas de envíos. Útil para perfiles.
     */
    public function show($id)
    {
        try {
            $usuario = User::with([
                'roles:id,name'
            ])->findOrFail($id);

            // Obtener vehículos del transportista
            $vehiculos = \App\Models\Vehiculo::where('transportista_id', $id)
                ->select('id', 'placa', 'marca', 'modelo', 'transportista_id')
                ->get();

            // Estadísticas del usuario
            $estadisticas = [];
            if ($usuario->hasRole('transportista')) {
                $vehiculosIds = $vehiculos->pluck('id');
                $estadisticas = [
                    'total_envios' => \App\Models\EnvioAsignacion::whereIn('vehiculo_id', $vehiculosIds)
                        ->count(),
                    'envios_entregados' => \App\Models\EnvioAsignacion::whereIn('vehiculo_id', $vehiculosIds)
                        ->whereHas('envio', function($q) {
                            $q->where('estado', 'entregado');
                        })
                        ->count(),
                    'vehiculos_asignados' => $usuario->vehiculos->count(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->name,
                    'email' => $usuario->email,
                    'telefono' => $usuario->telefono,
                    'licencia' => $usuario->licencia,
                    'roles' => $usuario->roles->pluck('name'),
                    'vehiculos' => $vehiculos,
                    'estadisticas' => $estadisticas,
                    'fecha_registro' => $usuario->created_at->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error en UsuarioApiController::show', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo usuario
     * POST /api/usuarios
     * 
     * Ayuda: Crear usuarios desde sistemas externos, sincronización automática,
     * importación masiva, o registro desde app móvil.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'telefono' => 'nullable|string|max:20',
                'licencia' => 'nullable|string|max:50',
                'roles' => 'required|array|min:1',
                'roles.*' => 'exists:roles,name',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $usuario = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'licencia' => $request->licencia,
            ]);

            // Asignar roles
            $usuario->assignRole($request->roles);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->name,
                    'email' => $usuario->email,
                    'roles' => $usuario->roles->pluck('name'),
                ]
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error en UsuarioApiController::store', [
                'error' => $e->getMessage(),
                'request' => $request->except(['password'])
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar usuario
     * PUT/PATCH /api/usuarios/{id}
     * 
     * Ayuda: Actualizar información de usuarios desde sistemas externos,
     * cambiar roles, actualizar datos de contacto, resetear contraseñas.
     */
    public function update(Request $request, $id)
    {
        try {
            $usuario = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8',
                'telefono' => 'nullable|string|max:20',
                'licencia' => 'nullable|string|max:50',
                'roles' => 'sometimes|array',
                'roles.*' => 'exists:roles,name',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['name', 'email', 'telefono', 'licencia']);
            
            if ($request->has('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $usuario->update($data);

            // Actualizar roles si se proporcionan
            if ($request->has('roles')) {
                $usuario->syncRoles($request->roles);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->name,
                    'email' => $usuario->email,
                    'roles' => $usuario->roles->pluck('name'),
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error en UsuarioApiController::update', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar/Eliminar usuario
     * DELETE /api/usuarios/{id}
     * 
     * Ayuda: Desactivar usuarios sin eliminarlos físicamente,
     * útil para mantener historial y prevenir pérdida de datos.
     */
    public function destroy($id)
    {
        try {
            $usuario = User::findOrFail($id);

            // Verificar si es el último admin
            if ($usuario->hasRole('admin')) {
                $totalAdmins = User::role('admin')->count();
                if ($totalAdmins <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar el último administrador del sistema'
                    ], 422);
                }
            }

            // Verificar si tiene envíos asignados activos
            if ($usuario->hasRole('transportista')) {
                $vehiculosIds = \App\Models\Vehiculo::where('transportista_id', $id)->pluck('id');
                $enviosActivos = \App\Models\EnvioAsignacion::whereIn('vehiculo_id', $vehiculosIds)
                    ->whereHas('envio', function($q) {
                        $q->whereIn('estado', ['asignado', 'en_transito']);
                    })
                    ->count();

                if ($enviosActivos > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "No se puede desactivar el usuario. Tiene {$enviosActivos} envío(s) activo(s).",
                        'envios_activos' => $enviosActivos
                    ], 422);
                }
            }

            // Desactivar en lugar de eliminar (si tienes campo activo)
            // O eliminar si no hay restricciones
            $usuario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error en UsuarioApiController::destroy', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ], 500);
        }
    }
}

