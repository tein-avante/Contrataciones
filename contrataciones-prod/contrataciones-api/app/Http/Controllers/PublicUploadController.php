<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudCarga;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification;

class PublicUploadController extends Controller
{
    /**
     * Muestra el formulario de subida o un mensaje si el enlace ya fue usado.
     */
    public function show(SolicitudCarga $solicitudCarga)
    {
        if ($solicitudCarga->token_used_at) {
            return view('public.upload-used', [
                'ticket' => $solicitudCarga->ticket,
            ]);
        }
        return view('public.upload-form', [
            'solicitud' => $solicitudCarga,
        ]);
    }

    /**
     * Procesa el archivo subido por el empleado.
     */
    public function store(Request $request, SolicitudCarga $solicitudCarga)
    {
        if ($solicitudCarga->token_used_at) {
            abort(403, 'Este enlace ya ha sido utilizado.');
        }

        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,png,jpeg|max:2048',
        ]);

        $path = $request->file('archivo')->store('documentos', 'public');

        $documentoAsociado = null; // Variable para guardar la referencia al documento

        // --- LÓGICA DE ACTUALIZACIÓN O CREACIÓN  ---
        if ($solicitudCarga->documento_id) {
            // Caso A: La solicitud era para ACTUALIZAR un documento
            $documento = Documento::find($solicitudCarga->documento_id);
            if ($documento) {
                Storage::disk('public')->delete($documento->archivo);
                $documento->update([
                    'archivo' => $path,
                    'estado' => 'En Revisión'
                ]);
                $documentoAsociado = $documento;
            }
        }

        else if ($solicitudCarga->tipo_documento_id) {
            // Caso B: La solicitud era para un DOCUMENTO NUEVO
            $documento = $solicitudCarga->empleado->documentos()->create([
                'tipo_documento_id' => $solicitudCarga->tipo_documento_id,
                'fecha_vencimiento' => null, // El empleado no define esto
                'archivo' => $path,
                'estado' => 'En Revisión', // El estado correcto
            ]);

            // Actualizamos la solicitud para enlazarla al nuevo documento
            $solicitudCarga->documento_id = $documento->id;
            $documentoAsociado = $documento;
        }

        // Marcamos la solicitud como completada y quemamos el token
        $solicitudCarga->estado = 'Completada';
        $solicitudCarga->token_used_at = now();
        $solicitudCarga->save();

        // Creamos la notificación para el analista
        Notification::create([
            'user_id' => $solicitudCarga->usuario_id,
            'empleado_id' => $solicitudCarga->empleado_id,
            'documento_id' => $documentoAsociado ? $documentoAsociado->id : null,
            'mensaje' => "El empleado '{$solicitudCarga->empleado->nombre}' ha completado la solicitud (Ticket: {$solicitudCarga->ticket}).",
            'fecha_aviso' => now(),
        ]);

        // Mostramos la página de éxito
        return view('public.upload-success', [
            'ticket' => $solicitudCarga->ticket,
        ]);
    }
}
