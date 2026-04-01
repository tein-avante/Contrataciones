<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Importación de Controladores ---
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\TipoDocumentoController;
use App\Http\Controllers\SolicitudCargaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DatoFamiliarController;
use App\Http\Controllers\EstudioController;
use App\Http\Controllers\CursoEventoController;
use App\Http\Controllers\IdiomaController;
use App\Http\Controllers\ExperienciaLaboralController;
use App\Http\Controllers\ReferenciaPersonalController;

/*
|--------------------------------------------------------------------------
| Rutas de la API
|--------------------------------------------------------------------------
*/

// --- GRUPO DE RUTAS PÚBLICAS ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/empleados/{empleado}/pdf', [EmpleadoController::class, 'generatePdf']);


// --- GRUPO DE RUTAS PROTEGIDAS ---
Route::middleware('auth:sanctum')->group(function () {

    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { return $request->user(); });

    // CRUD de Empleados
    Route::get('/empleados/export/excel', [EmpleadoController::class, 'exportExcel']);
    Route::apiResource('empleados', EmpleadoController::class);

    // Gestión de Documentos
    Route::get('/tipos-documento', [TipoDocumentoController::class, 'index']);
    Route::post('/empleados/{empleado}/documentos', [DocumentoController::class, 'store']);
    Route::delete('/documentos/{documento}', [DocumentoController::class, 'destroy']);

    // Gestión de Solicitudes de Carga
    Route::post('/empleados/{empleado}/solicitudes', [SolicitudCargaController::class, 'store']);
    Route::post('/documentos/{documento}/aceptar', [DocumentoController::class, 'aceptar']);
    Route::post('/documentos/{documento}/rechazar', [DocumentoController::class, 'rechazar']);

    // Rutas para Oferta de Servicio
    Route::post('/empleados/{empleado}/datos-familiares', [DatoFamiliarController::class, 'store']);
    Route::put('/datos-familiares/{datoFamiliar}', [DatoFamiliarController::class, 'update']);
    Route::delete('/datos-familiares/{datoFamiliar}', [DatoFamiliarController::class, 'destroy']);

    Route::post('/empleados/{empleado}/estudios', [EstudioController::class, 'store']);
    Route::put('/estudios/{estudio}', [EstudioController::class, 'update']);
    Route::delete('/estudios/{estudio}', [EstudioController::class, 'destroy']);

    Route::post('/empleados/{empleado}/cursos-eventos', [CursoEventoController::class, 'store']);
    Route::put('/cursos-eventos/{cursoEvento}', [CursoEventoController::class, 'update']);
    Route::delete('/cursos-eventos/{cursoEvento}', [CursoEventoController::class, 'destroy']);

    Route::post('/empleados/{empleado}/idiomas', [IdiomaController::class, 'store']);
    Route::put('/idiomas/{idioma}', [IdiomaController::class, 'update']);
    Route::delete('/idiomas/{idioma}', [IdiomaController::class, 'destroy']);

    Route::post('/empleados/{empleado}/experiencias-laborales', [ExperienciaLaboralController::class, 'store']);
    Route::put('/experiencias-laborales/{experienciaLaboral}', [ExperienciaLaboralController::class, 'update']);
    Route::delete('/experiencias-laborales/{experienciaLaboral}', [ExperienciaLaboralController::class, 'destroy']);

    Route::post('/empleados/{empleado}/referencias-personales', [ReferenciaPersonalController::class, 'store']);
    Route::put('/referencias-personales/{referenciaPersonal}', [ReferenciaPersonalController::class, 'update']);
    Route::delete('/referencias-personales/{referenciaPersonal}', [ReferenciaPersonalController::class, 'destroy']);

    // --- Gestión de Notificaciones ---
    // Mantenemos '/notificaciones' como decidiste.
    Route::get('/notifications', [NotificationController::class, 'index']);

    // Nueva ruta para marcar una notificación específica como leída.
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    // Nueva ruta para marcar TODAS las notificaciones de un empleado como leídas (Global)
    Route::post('/notifications/empleados/{empleadoId}/read-all', [NotificationController::class, 'markAllAsReadForEmpleado']);

    Route::apiResource('tipos-documento', TipoDocumentoController::class)
    ->parameters([
        'tipos-documento' => 'tipoDocumento' // Mapeamos la URL a tu variable camelCase
    ]);

    Route::middleware('role:admin')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::post('/usuarios', [UserController::class, 'store']);
        Route::put('/usuarios/{user}', [UserController::class, 'update']);
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy']);
    });
});
