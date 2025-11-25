<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ruta;

class RutaTiempoRealController extends Controller
{
    public function index()
    {
        $rutas = Ruta::all();
        return view('rutas.index', compact('rutas'));
    }

    public function create()
    {
        return view('rutas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        Ruta::create($request->only(['nombre','descripcion','polyline']));

        return redirect()->route('rutas.index')->with('success', 'Ruta creada');
    }

    public function edit(Ruta $ruta)
    {
        return view('rutas.edit', compact('ruta'));
    }

    public function update(Request $request, Ruta $ruta)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $ruta->update($request->only(['nombre','descripcion','polyline']));

        return redirect()->route('rutas.index')->with('success', 'Ruta actualizada');
    }

    public function destroy(Ruta $ruta)
    {
        $ruta->delete();
        return redirect()->route('rutas.index')->with('success', 'Ruta eliminada');
    }
}