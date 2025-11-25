<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\TipoEmpaque;

class TipoEmpaqueController extends Controller
{
    public function index() {
        $empaques = TipoEmpaque::all();
        return view('tiposempaque.index', compact('empaques'));
    }

    public function create() {
        return view('tiposempaque.create');
    }

    public function store(Request $request) {
        TipoEmpaque::create($request->only(['nombre']));
        return redirect()->route('tiposempaque.index');
    }

    public function edit(TipoEmpaque $tiposempaque) {
        return view('tiposempaque.edit', compact('tiposempaque'));
    }

    public function update(Request $request, TipoEmpaque $tiposempaque) {
        $tiposempaque->update($request->only(['nombre']));
        return redirect()->route('tiposempaque.index');
    }

    public function destroy(TipoEmpaque $tiposempaque) {
        $tiposempaque->delete();
        return redirect()->route('tiposempaque.index');
    }
}