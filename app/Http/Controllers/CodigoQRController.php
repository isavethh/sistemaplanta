<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use Illuminate\Http\Request;

class CodigoQRController extends Controller
{
    public function index()
    {
        return view('codigosqr.index');
    }

    public function show($id)
    {
        $envio = Envio::with(['cliente', 'almacen.direccion', 'direccion', 'productos', 'transportista', 'vehiculo', 'categoria'])
            ->findOrFail($id);
        
        return view('codigosqr.show', compact('envio'));
    }

    public function create()
    {
        return view('codigosqr.create');
    }

    public function store(Request $request)
    {
        // Implementar según necesidad
        return redirect()->route('codigosqr.index')->with('success', 'Código QR creado exitosamente');
    }

    public function edit($id)
    {
        return view('codigosqr.edit');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('codigosqr.index')->with('success', 'Código QR actualizado exitosamente');
    }

    public function destroy($id)
    {
        return redirect()->route('codigosqr.index')->with('success', 'Código QR eliminado exitosamente');
    }
}
