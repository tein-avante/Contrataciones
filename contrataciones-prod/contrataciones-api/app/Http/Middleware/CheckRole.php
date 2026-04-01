<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Maneja la petición entrante verificando los privilegios del usuario.
     *
     * CONTROL DE ACCESO BASADO EN ROLES (RBAC):
     * Este middleware intercepta la petición HTTP antes de que llegue al controlador.
     * Verifica si el usuario autenticado posee el rol requerido (ej: 'admin') para
     * acceder a la ruta solicitada.
     *
     * Lógica de Seguridad:
     * 1. Verifica si existe un usuario en la petición ($request->user()).
     * 2. Compara el rol del usuario con el rol requerido ($role).
     * 3. Si no cumple, aborta con código 403 (Forbidden), protegiendo la ruta.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  El rol necesario para acceder (ej: 'admin', 'analyst')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Validación estricta de autenticación y autorización
        if (! $request->user() || $request->user()->role !== $role) {
            // Detiene la ejecución inmediatamente si no tiene permisos
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        // Permite que la petición continúe hacia el controlador
        return $next($request);
    }
}
