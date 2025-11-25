<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TransportistaController extends Controller
{
    public function index()
    {
        $transportistas = User::where('role', 'transportista')->get();
        return view('transportistas.index', compact('transportistas'));
    }

    public function create()
    {
        return view('transportistas.create');
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
            'role' => 'transportista',
        ]);

        return redirect()->route('transportistas.index')->with('success', 'Transportista creado');
    }

    public function edit(User $transportista)
    {
        return view('transportistas.edit', compact('transportista'));
    }

    public function update(Request $request, User $transportista)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $transportista->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = ['name' => $request->name, 'email' => $request->email];
        if ($request->password) { $data['password'] = Hash::make($request->password); }

        $transportista->update($data);

        return redirect()->route('transportistas.index')->with('success', 'Transportista actualizado');
    }

    public function destroy(User $transportista)
    {
        $transportista->delete();
        return redirect()->route('transportistas.index')->with('success', 'Transportista eliminado');
    }
}