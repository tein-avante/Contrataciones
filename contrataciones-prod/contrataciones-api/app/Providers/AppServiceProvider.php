<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // Permite compartir variables con las vistas (Blade).
use Illuminate\Support\Facades\Auth;  // Permite acceder a la información del usuario autenticado.
use App\Models\Notification;       // El modelo para consultar la tabla de notificaciones.

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Este método se usa para registrar servicios en el contenedor de la aplicación.
     * Para nuestro caso, no necesitamos modificarlo.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Este método se ejecuta después de que todos los demás proveedores de servicios
     * han sido registrados. Es el lugar ideal para nuestra lógica de notificaciones globales.
     */
    public function boot(): void
    {
        // Limitar la longitud de los strings para evitar errores en índices de MySQL antiguos
        Schema::defaultStringLength(191);

        // ==================================================================
        // COMPARTIR NOTIFICACIONES GLOBALES CON TODAS LAS VISTAS
        // ==================================================================

        // View::composer('*', ...) le dice a Laravel: "Antes de renderizar CUALQUIER
        // vista (indicado por el '*'), ejecuta la siguiente función".
        // Esto nos asegura que la variable '$unreadNotifications' estará siempre
        // disponible en todas las páginas, especialmente en la barra de navegación.

        View::composer('*', function ($view) {

            // 1. VERIFICAR SI HAY UN USUARIO AUTENTICADO
            // Usamos Auth::check(), que devuelve 'true' si el usuario ha iniciado sesión
            // y 'false' si es un visitante.
            if (Auth::check()) {

                // 2. SI HAY SESIÓN, BUSCAR LAS NOTIFICACIONES DE ESE USUARIO
                // Construimos una consulta a la base de datos usando el modelo Notificacion.
                // La consulta busca solo los registros donde:
                //   - La columna 'user_id' coincide con el ID del usuario actual (Auth::id()).
                //   - La columna 'read_at' es nula (lo que significa que la notificación no ha sido leída).
                $unreadNotifications = Notification::where('user_id', Auth::id())
                                                    ->whereNull('read_at')
                                                    ->latest() // Ordena los resultados por los más recientes primero.
                                                    ->get();   // Ejecuta la consulta y obtiene los resultados.
            } else {

                // 3. SI NO HAY SESIÓN, DEVOLVER UNA COLECCIÓN VACÍA
                // En páginas públicas como el login, no hay usuario. Para evitar errores en la vista
                // al intentar contar las notificaciones, creamos una "colección" vacía.
                $unreadNotifications = collect();
            }

            // 4. COMPARTIR LA VARIABLE CON LA VISTA
            // El método 'with()' hace que la variable '$unreadNotifications' esté disponible
            // en el archivo de Blade que se va a renderizar.
            $view->with('unreadNotifications', $unreadNotifications);
        });
    }
}
