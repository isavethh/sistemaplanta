<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TransportistaController extends Controller
{
    public function index()
    {
        $transportistas = User::where('role', 'transportista')
            ->orWhere('tipo', 'transportista')
            ->orderBy('name')
            ->get();
        return view('transportistas.index', compact('transportistas'));
    }

    public function create()
    {
        return view('transportistas.create');
    }

    public function store(Request $request)
    {
        // Debug: verificar datos recibidos
        \Log::info('Datos recibidos para crear transportista:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'licencia' => 'required|in:A,B,C',
            'telefono' => 'nullable|string|max:20',
        ]);

        try {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'transportista',
                'tipo' => 'transportista',
                'licencia' => $validated['licencia'],
                'telefono' => $validated['telefono'] ?? null,
                'disponible' => $request->has('disponible') ? 1 : 0,
            ];

            \Log::info('Datos a crear:', $userData);

            $user = User::create($userData);

            \Log::info('Usuario creado con ID: ' . $user->id);

            return redirect()->route('transportistas.index')->with('success', 'Transportista creado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error al crear transportista: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withInput()->withErrors(['error' => 'Error al crear transportista: ' . $e->getMessage()]);
        }
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
            'licencia' => 'required|in:A,B,C',
            'telefono' => 'nullable|string|max:20',
            'disponible' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $request->name, 
            'email' => $request->email,
            'licencia' => $request->licencia,
            'telefono' => $request->telefono,
            'disponible' => $request->has('disponible'),
        ];
        
        if ($request->filled('password')) { 
            $data['password'] = Hash::make($request->password); 
        }

        $transportista->update($data);

        return redirect()->route('transportistas.index')->with('success', 'Transportista actualizado correctamente');
    }

    public function destroy(User $transportista)
    {
        // Verificar que no tenga asignaciones activas antes de eliminar
        $tieneAsignacionesActivas = $transportista->enviosComoTransportista()
            ->whereIn('estado', ['asignado', 'aceptado', 'en_transito'])
            ->exists();

        if ($tieneAsignacionesActivas) {
            return redirect()->route('transportistas.index')
                ->with('error', 'No se puede eliminar el transportista porque tiene envíos activos asignados.');
        }

        $transportista->delete();
        return redirect()->route('transportistas.index')->with('success', 'Transportista eliminado correctamente');
    }
}
