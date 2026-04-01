<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\CursoEvento;
use Illuminate\Http\Request;

class CursoEventoController extends Controller
{
    public function store(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'nombre_curso' => 'required|string|max:255',
            'institucion' => 'nullable|string|max:255',
            'fecha' => 'nullable|date',
            'horas' => 'nullable|integer',
            'certificado' => 'boolean',
        ]);

        $cursoEvento = $empleado->cursosEventos()->create($validated);

        return response()->json($cursoEvento, 201);
    }

    public function update(Request $request, CursoEvento $cursoEvento)
    {
        $validated = $request->validate([
            'nombre_curso' => 'required|string|max:255',
            'institucion' => 'nullable|string|max:255',
            'fecha' => 'nullable|date',
            'horas' => 'nullable|integer',
            'certificado' => 'boolean',
        ]);

        $cursoEvento->update($validated);

        return response()->json($cursoEvento);
    }

    public function destroy(CursoEvento $cursoEvento)
    {
        $cursoEvento->delete();
        return response()->json(null, 204);
    }
}
