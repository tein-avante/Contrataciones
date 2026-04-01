<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class HandleInertiaRequests extends Middleware
{
    /**
     * El template raíz.
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determina la versión del asset.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define las props que se comparten por defecto.
     */
    // app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => ['user' => $request->user()],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
        'unreadNotifications' => function () {
            $user = Auth::user();
            if (!$user) {
                return [];
            }
            return Notification::where('user_id', $user->id)
                                ->whereNull('read_at')
                                ->latest()
                                ->get();
        },
    ]);
}
}