<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\DatoFamiliar;
use Illuminate\Http\Request;
use App\Services\SistemaService;

class DatoFamiliarController extends Controller
{
    public function store(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'parentesco' => 'required|string|max:255',
            'edad' => 'nullable|integer',
            'nacionalidad' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
        ]);

        $datoFamiliar = $empleado->datosFamiliares()->create($validated);

        SistemaService::incrementarOperaciones();

        return response()->json($datoFamiliar, 201);
    }

    public function update(Request $request, DatoFamiliar $datoFamiliar)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'parentesco' => 'required|string|max:255',
            'edad' => 'nullable|integer',
            'nacionalidad' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
        ]);

        $datoFamiliar->update($validated);

        SistemaService::incrementarOperaciones();

        return response()->json($datoFamiliar);
    }

    public function destroy(DatoFamiliar $datoFamiliar)
    {
        $datoFamiliar->delete();

        SistemaService::incrementarOperaciones();

        return response()->json(null, 204);
    }
}
