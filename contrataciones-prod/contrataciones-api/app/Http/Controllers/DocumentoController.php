<?php
namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\SolicitudCargaCreada;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Services\SistemaService;

class DocumentoController extends Controller
{
    /**
     * Almacena un nuevo documento para un empleado.
     */
    public function store(Request $request, Empleado $empleado)
    {
        try {
            $data = $request->validate([
                'tipo_documento_id' => 'required|exists:tipos_documento,id',
                'fecha_vencimiento' => 'nullable|date',
                'archivo' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048',
            ]);

            $path = $request->file('archivo')->store('documentos', 'public');

            $documento = $empleado->documentos()->create([
                'tipo_documento_id' => $data['tipo_documento_id'],
                'fecha_vencimiento' => $data['fecha_vencimiento'],
                'archivo' => $path,
                'estado' => 'Vigente',
            ]);

            SistemaService::incrementarOperaciones();

            return response()->json($documento, 201);

        } catch (ValidationException $e) {
            // Si el error es de validación, lo devolvemos correctamente
            return response()->json(['message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            // Si es cualquier otro error, devolvemos el mensaje detallado
            return response()->json([
                'message' => 'Error interno al guardar el documento.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Elimina un documento específico.
     */
    public function destroy(Documento $documento)
    {
        Storage::disk('public')->delete($documento->archivo);
        $documento->delete();

        SistemaService::incrementarOperaciones();

        return response()->json(null, 204);
    }

    /**
     * Acepta un documento y cambia su estado a "Vigente".
     */
    // En DocumentoController.php

public function aceptar(Request $request, Documento $documento)
{
    // 1. Validamos que envíen una fecha válida
    $data = $request->validate([
        'fecha_vencimiento' => 'required|date',
    ]);

    // 2. Actualizamos el estado Y la fecha
    $documento->update([
        'estado' => 'Vigente',
        'fecha_vencimiento' => $data['fecha_vencimiento'],
    ]);

    // 3. (Opcional) Si había una solicitud asociada a este documento, asegúrate de que esté cerrada
    // Esto ya lo hace el PublicUploadController, pero no está de más.

    SistemaService::incrementarOperaciones();

    return response()->json(['message' => 'Documento aceptado y vigente.']);
}

    /**
     * Rechaza un documento, cambia su estado y genera una nueva solicitud.
     */
    public function rechazar(Request $request, Documento $documento)
    {
        $data = $request->validate([
            'observacion' => 'required|string|max:1000',
        ]);

        $documento->update(['estado' => 'Rechazado']);
        $empleado = $documento->empleado;

        $nuevaSolicitud = $empleado->solicitudesCarga()->create([
            'usuario_id' => auth()->id(),
            'documento_id' => $documento->id,
            'fecha_expiracion' => now()->addDays(7),
            'observacion' => $data['observacion'],
            'ticket' => 'REQ-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
            'estado' => 'Pendiente',
        ]);

        $empleado->notify(new SolicitudCargaCreada($nuevaSolicitud));

        SistemaService::incrementarOperaciones();

        return response()->json($nuevaSolicitud, 201);
    }
}
