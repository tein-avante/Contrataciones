<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Idioma;
use Illuminate\Http\Request;
use App\Services\SistemaService;

class IdiomaController extends Controller
{
    public function store(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'idioma' => 'required|string|max:255',
            'habla' => 'required|string|in:Bien,Regular,Poco',
            'lee' => 'required|string|in:Bien,Regular,Poco',
            'escribe' => 'required|string|in:Bien,Regular,Poco',
        ]);

        $idioma = $empleado->idiomas()->create($validated);

        SistemaService::incrementarOperaciones();

        return response()->json($idioma, 201);
    }

    public function update(Request $request, Idioma $idioma)
    {
        $validated = $request->validate([
            'idioma' => 'required|string|max:255',
            'habla' => 'required|string|in:Bien,Regular,Poco',
            'lee' => 'required|string|in:Bien,Regular,Poco',
            'escribe' => 'required|string|in:Bien,Regular,Poco',
        ]);

        $idioma->update($validated);

        SistemaService::incrementarOperaciones();

        return response()->json($idioma);
    }

    public function destroy(Idioma $idioma)
    {
        $idioma->delete();

        SistemaService::incrementarOperaciones();

        return response()->json(null, 204);
    }
}
