<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdministradorController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'administrador')->get();
        return view('administradores.index', compact('admins'));
    }

    public function create()
    {
        return view('administradores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'administrador',
        ]);

        return redirect()->route('administradores.index')->with('success', 'Administrador creado');
    }

    public function edit(User $administrador)
    {
        return view('administradores.edit', compact('administrador'));
    }

    public function update(Request $request, User $administrador)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $administrador->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = ['name' => $request->name, 'email' => $request->email];
        if ($request->password) { $data['password'] = Hash::make($request->password); }

        $administrador->update($data);

        return redirect()->route('administradores.index')->with('success', 'Administrador actualizado');
    }

    public function destroy(User $administrador)
    {
        $administrador->delete();
        return redirect()->route('administradores.index')->with('success', 'Administrador eliminado');
    }
}