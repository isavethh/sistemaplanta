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
        try {
            // Verificar si tiene productos asociados
            $tieneProductos = \DB::table('envio_productos')->where('unidad_medida_id', $unidadesmedida->id)->exists();
            
            if ($tieneProductos) {
                return redirect()->route('unidadesmedida.index')
                    ->with('error', 'No se puede eliminar porque hay productos con esta unidad de medida.');
            }
            
            $unidadesmedida->delete();
            return redirect()->route('unidadesmedida.index')->with('success', 'Unidad de medida eliminada');
        } catch (\Exception $e) {
            return redirect()->route('unidadesmedida.index')
                ->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}