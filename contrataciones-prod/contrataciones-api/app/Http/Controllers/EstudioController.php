<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Estudio;
use Illuminate\Http\Request;

class EstudioController extends Controller
{
    public function store(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'nivel' => 'required|string|max:255',
            'institucion' => 'required|string|max:255',
            'lugar' => 'nullable|string|max:255',
            'fecha_inicio' => 'nullable|date',
            'fecha_culminacion' => 'nullable|date',
            'grado_titulo' => 'nullable|string|max:255',
        ]);

        $estudio = $empleado->estudios()->create($validated);

        return response()->json($estudio, 201);
    }

    public function update(Request $request, Estudio $estudio)
    {
        $validated = $request->validate([
            'nivel' => 'required|string|max:255',
            'institucion' => 'required|string|max:255',
            'lugar' => 'nullable|string|max:255',
            'fecha_inicio' => 'nullable|date',
            'fecha_culminacion' => 'nullable|date',
            'grado_titulo' => 'nullable|string|max:255',
        ]);

        $estudio->update($validated);

        return response()->json($estudio);
    }

    public function destroy(Estudio $estudio)
    {
        $estudio->delete();
        return response()->json(null, 204);
    }
}
