<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\ReferenciaPersonal;
use Illuminate\Http\Request;

class ReferenciaPersonalController extends Controller
{
    public function store(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'profesion' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
        ]);

        $referencia = $empleado->referenciasPersonales()->create($validated);

        return response()->json($referencia, 201);
    }

    public function update(Request $request, ReferenciaPersonal $referenciaPersonal)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'profesion' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
        ]);

        $referenciaPersonal->update($validated);

        return response()->json($referenciaPersonal);
    }

    public function destroy(ReferenciaPersonal $referenciaPersonal)
    {
        $referenciaPersonal->delete();
        return response()->json(null, 204);
    }
}
