<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Throwable;

class NotificationController extends Controller
{
    /**
     * Devuelve las 15 notificaciones más recientes del usuario autenticado,
     * tanto leídas como no leídas.
     */
    public function index(Request $request)
    {
        try {
            $notifications = $request->user()->notifications()
                                    ->latest() // Las más recientes primero
                                    ->get();

            return response()->json($notifications);

        } catch (Throwable $e) {
            // Modo de depuración por si algo falla
            return response()->json([
                'message' => 'Ha ocurrido un error al obtener las notificaciones.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Marca una notificación específica como leída.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        try {
            // Solo actualizamos si no ha sido leída antes
            if (is_null($notification->read_at)) {
                $notification->update(['read_at' => now()]);
            }

            return response()->json(['message' => 'Notificación marcada como leída.']);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error al marcar la notificación como leída.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marca TODAS las notificaciones de un empleado como leídas.
     */
    public function markAllAsReadForEmpleado(Request $request, $empleadoId)
    {
        try {
            Notification::where('empleado_id', $empleadoId)
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);

            return response()->json(['message' => 'Todas las notificaciones del empleado marcadas como leídas.']);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error al marcar las notificaciones como leídas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
