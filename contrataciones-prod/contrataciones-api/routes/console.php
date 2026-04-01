<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <-- 1. IMPORTA LA CLASE SCHEDULE

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// --- ▼▼▼ 2. AÑADE ESTE BLOQUE COMPLETO ▼▼▼ ---
// Aquí definimos la programación de las tareas de la aplicación.
// Le decimos a Laravel que ejecute el comando 'contrataciones:verify-expirations'
// todos los días a medianoche.
Schedule::command('contrataciones:verify-expirations')->daily();
