<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\ExperienciaLaboral;
use Illuminate\Http\Request;

class ExperienciaLaboralController extends Controller
{
    public function store(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'empresa' => 'required|string|max:255',
            'direccion_telefono' => 'nullable|string|max:255',
            'fecha_ingreso' => 'nullable|date',
            'fecha_retiro' => 'nullable|date',
            'sueldo_inicial' => 'nullable|numeric',
            'sueldo_final' => 'nullable|numeric',
            'cargo_inicial' => 'nullable|string|max:255',
            'cargo_final' => 'nullable|string|max:255',
            'nombre_supervisor' => 'nullable|string|max:255',
            'motivo_retiro' => 'nullable|string|max:255',
        ]);

        $experiencia = $empleado->experienciasLaborales()->create($validated);

        return response()->json($experiencia, 201);
    }

    public function update(Request $request, ExperienciaLaboral $experienciaLaboral)
    {
        $validated = $request->validate([
            'empresa' => 'required|string|max:255',
            'direccion_telefono' => 'nullable|string|max:255',
            'fecha_ingreso' => 'nullable|date',
            'fecha_retiro' => 'nullable|date',
            'sueldo_inicial' => 'nullable|numeric',
            'sueldo_final' => 'nullable|numeric',
            'cargo_inicial' => 'nullable|string|max:255',
            'cargo_final' => 'nullable|string|max:255',
            'nombre_supervisor' => 'nullable|string|max:255',
            'motivo_retiro' => 'nullable|string|max:255',
        ]);

        $experienciaLaboral->update($validated);

        return response()->json($experienciaLaboral);
    }

    public function destroy(ExperienciaLaboral $experienciaLaboral)
    {
        $experienciaLaboral->delete();
        return response()->json(null, 204);
    }
}
