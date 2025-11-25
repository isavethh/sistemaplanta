<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\UnidadMedida;

class UnidadMedidaController extends Controller
{
    public function index() {
        $unidades = UnidadMedida::all();
        return view('unidadesmedida.index', compact('unidades'));
    }

    public function create() {
        return view('unidadesmedida.create');
    }

    public function store(Request $request) {
        UnidadMedida::create($request->only(['nombre', 'abreviatura']));
        return redirect()->route('unidadesmedida.index');
    }

    public function edit(UnidadMedida $unidadesmedida) {
        return view('unidadesmedida.edit', compact('unidadesmedida'));
    }

    public function update(Request $request, UnidadMedida $unidadesmedida) {
        $unidadesmedida->update($request->only(['nombre', 'abreviatura']));
        return redirect()->route('unidadesmedida.index');
    }

    public function destroy(UnidadMedida $unidadesmedida) {
        $unidadesmedida->delete();
        return redirect()->route('unidadesmedida.index');
    }
}