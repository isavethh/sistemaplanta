<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = User::where('role', 'cliente')->get();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
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
            'role' => 'cliente',
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado');
    }

    public function edit(User $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, User $cliente)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $cliente->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = ['name' => $request->name, 'email' => $request->email];
        if ($request->password) { $data['password'] = Hash::make($request->password); }

        $cliente->update($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado');
    }

    public function destroy(User $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado');
    }
}