<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolicitudCargaController;

// Ruta raíz de prueba (Opcional)
Route::get('/', function () {
    return response()->json(['message' => 'API de Gerencia de Contrataciones en funcionamiento.']);
});

// --- RUTAS PÚBLICAS PARA EL EMPLEADO (VISTAS HTML) ---
// Estas rutas SE QUEDAN AQUÍ porque el empleado accede desde el navegador
// a través del correo y necesita ver una interfaz HTML (Blade), no datos JSON.

Route::get('/solicitud-carga/{solicitudCarga}', [SolicitudCargaController::class, 'showUploadForm'])
    ->name('solicitud-carga.show')
    ->middleware('signed');

Route::post('/solicitud-carga/{solicitudCarga}', [SolicitudCargaController::class, 'processUpload'])
    ->name('solicitud-carga.store')
    ->middleware('signed');
