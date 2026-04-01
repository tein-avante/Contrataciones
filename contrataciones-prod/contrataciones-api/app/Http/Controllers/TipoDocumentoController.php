<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use Illuminate\Http\Request;

class TipoDocumentoController extends Controller
{
    // Listar todos
    public function index()
    {
        return response()->json(TipoDocumento::orderBy('nombre')->get());
    }

    // Crear uno nuevo
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_documento,nombre',
            'periodo_alerta' => 'required|integer|min:1',
        ]);

        $tipo = TipoDocumento::create($data);

        return response()->json($tipo, 201);
    }

    // Actualizar uno existente
    public function update(Request $request, TipoDocumento $tipoDocumento)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_documento,nombre,' . $tipoDocumento->id,
            'periodo_alerta' => 'required|integer|min:1',
        ]);

        $tipoDocumento->update($data);

        return response()->json($tipoDocumento);
    }

    // Eliminar
    public function destroy(TipoDocumento $tipoDocumento)
    {
        // Opcional: Validar si hay documentos usando este tipo antes de borrar
        $tipoDocumento->delete();
        return response()->json(null, 204);
    }
}
