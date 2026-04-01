<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php', // <-- DESCOMENTA ESTA LÍNEA
        api: __DIR__.'/../routes/api.php', // <-- ASEGÚRATE DE QUE ESTA LÍNEA ESTÉ
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // ▼▼▼ AÑADE ESTA LÍNEA ▼▼▼
        apiPrefix: '', // Quitamos el prefijo para evitar duplicidad con el nombre de la carpeta en el servidor
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Excluimos la ruta de login de la protección CSRF ya que es una API
        $middleware->validateCsrfTokens(except: [
            '/login',
            '/register',
        ]);

        // ▼▼▼ AÑADE ESTA LÍNEA ▼▼▼
        // Le decimos que nuestras rutas de API no necesitan cookies, CSRF, etc.
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
