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
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'largo_cm' => 'nullable|numeric|min:0',
            'ancho_cm' => 'nullable|numeric|min:0',
            'alto_cm' => 'nullable|numeric|min:0',
            'peso_maximo_kg' => 'nullable|numeric|min:0',
            'icono' => 'nullable|string|max:10',
        ]);
        
        // Calcular volumen si se proporcionan las medidas
        if ($data['largo_cm'] && $data['ancho_cm'] && $data['alto_cm']) {
            $data['volumen_cm3'] = $data['largo_cm'] * $data['ancho_cm'] * $data['alto_cm'];
        }
        
        TipoEmpaque::create($data);
        return redirect()->route('tiposempaque.index')->with('success', 'Tipo de empaque creado exitosamente');
    }

    public function edit(TipoEmpaque $tiposempaque) {
        return view('tiposempaque.edit', compact('tiposempaque'));
    }

    public function update(Request $request, TipoEmpaque $tiposempaque) {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'largo_cm' => 'nullable|numeric|min:0',
            'ancho_cm' => 'nullable|numeric|min:0',
            'alto_cm' => 'nullable|numeric|min:0',
            'peso_maximo_kg' => 'nullable|numeric|min:0',
            'icono' => 'nullable|string|max:10',
        ]);
        
        // Calcular volumen si se proporcionan las medidas
        if ($data['largo_cm'] && $data['ancho_cm'] && $data['alto_cm']) {
            $data['volumen_cm3'] = $data['largo_cm'] * $data['ancho_cm'] * $data['alto_cm'];
        }
        
        $tiposempaque->update($data);
        return redirect()->route('tiposempaque.index')->with('success', 'Tipo de empaque actualizado exitosamente');
    }
    
    /**
     * Calculador de empaques
     */
    public function calculador()
    {
        $empaques = TipoEmpaque::whereNotNull('peso_maximo_kg')
            ->whereNotNull('volumen_cm3')
            ->orderBy('peso_maximo_kg')
            ->get();
            
        return view('tiposempaque.calculador', compact('empaques'));
    }
    
    /**
     * API: Calcular cantidad de empaques necesarios
     */
    public function calcularEmpaques(Request $request)
    {
        $request->validate([
            'tipo_empaque_id' => 'required|exists:tipos_empaque,id',
            'peso_total_kg' => 'required|numeric|min:0',
            'cantidad_items' => 'required|integer|min:1',
        ]);
        
        $empaque = TipoEmpaque::findOrFail($request->tipo_empaque_id);
        
        // Calcular por peso
        $empaquesPorPeso = ceil($request->peso_total_kg / $empaque->peso_maximo_kg);
        
        // Calcular por cantidad (asumiendo distribución uniforme)
        $empaquesPorCantidad = ceil($request->cantidad_items / 10); // 10 items por empaque (ajustable)
        
        // Tomar el mayor
        $empaquesNecesarios = max($empaquesPorPeso, $empaquesPorCantidad);
        
        return response()->json([
            'success' => true,
            'empaques_necesarios' => $empaquesNecesarios,
            'empaque' => $empaque,
            'por_peso' => $empaquesPorPeso,
            'por_cantidad' => $empaquesPorCantidad,
            'peso_por_empaque' => round($request->peso_total_kg / $empaquesNecesarios, 2),
            'items_por_empaque' => round($request->cantidad_items / $empaquesNecesarios, 1),
        ]);
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