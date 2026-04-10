<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SistemaService;

class SistemaController extends Controller
{
    /**
     * Devuelve la información del sistema (versión y operaciones).
     * El frontend se encargará de ocultar las operaciones si el usuario no es admin,
     * pero para mayor seguridad enviamos null si no es admin.
     */
    public function info(Request $request)
    {
        $info = SistemaService::obtenerInfo();
        $user = $request->user();

        // Si el usuario no tiene rol admin, ocultamos el contador de operaciones
        if (!$user || $user->role !== 'admin') {
            $info['operaciones'] = null;
        }

        return response()->json($info);
    }
}
