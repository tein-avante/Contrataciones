<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Autentica al usuario y genera un token de acceso seguro (Sanctum).
     *
     * IMPLEMENTACIÓN DE SEGURIDAD: SESIÓN ÚNICA.
     * Este método implementa una política de seguridad estricta donde cada usuario
     * solo puede mantener una sesión activa a la vez. Antes de generar un nuevo token,
     * se revocan (eliminan) todos los tokens previos asociados al usuario.
     * Esto previene el uso compartido de credenciales y cierra sesiones olvidadas
     * en otros dispositivos automáticamente.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // --- ▼▼▼ LÓGICA DE SESIÓN ÚNICA ▼▼▼ ---
        // Eliminación proactiva de tokens para garantizar unicidad de sesión.
        $user->tokens()->delete();
        // --------------------------------------

        // Generación del nuevo token único para la sesión actual
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Cierra la sesión del usuario revocando el token actual.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoca específicamente el token que se usó para esta petición
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }
}
