<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listar todos los usuarios
     */
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        // Solo los 3 roles permitidos: admin, almacen, transportista
        $roles = Role::whereIn('name', ['admin', 'almacen', 'transportista'])->get();
        return view('users.create', compact('roles'));
    }

    /**
     * Crear nuevo usuario con rol
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'telefono' => 'nullable|string|max:20',
            'licencia' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'licencia' => $request->licencia,
        ]);

        // Asignar rol usando Spatie
        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente con rol: ' . $request->role);
    }

    /**
     * Mostrar detalles del usuario
     */
    public function show(User $user)
    {
        $user->load('roles');
        return view('users.show', compact('user'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(User $user)
    {
        // Solo los 3 roles permitidos: admin, almacen, transportista
        $roles = Role::whereIn('name', ['admin', 'almacen', 'transportista'])->get();
        $user->load('roles');
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Actualizar usuario y rol
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'telefono' => 'nullable|string|max:20',
            'licencia' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'licencia' => $request->licencia,
        ];

        // Actualizar contraseña solo si se proporciona
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sincronizar rol usando Spatie
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Eliminar usuario
     */
    public function destroy(User $user)
    {
        // Verificar si es el último admin
        if ($user->hasRole('admin')) {
            $totalAdmins = User::role('admin')->count();
            if ($totalAdmins <= 1) {
                return redirect()->route('users.index')
                    ->with('error', 'No se puede eliminar el último administrador del sistema');
            }
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado correctamente');
    }
}
