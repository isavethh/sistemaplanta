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
        try {
            // Verificar si tiene productos asociados
            $tieneProductos = \DB::table('envio_productos')->where('tipo_empaque_id', $tiposempaque->id)->exists();
            
            if ($tieneProductos) {
                return redirect()->route('tiposempaque.index')
                    ->with('error', 'No se puede eliminar porque hay productos con este tipo de empaque.');
            }
            
            $tiposempaque->delete();
            return redirect()->route('tiposempaque.index')->with('success', 'Tipo de empaque eliminado');
        } catch (\Exception $e) {
            return redirect()->route('tiposempaque.index')
                ->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}