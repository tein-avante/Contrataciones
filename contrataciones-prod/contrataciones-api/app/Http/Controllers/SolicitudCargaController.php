<?php
namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\SolicitudCarga;
use App\Models\Documento;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SolicitudCargaCreada;

class SolicitudCargaController extends Controller
{
    /**
     * Genera una nueva solicitud de carga y notifica al empleado.
     *
     * Crea un registro de seguimiento (Ticket) y dispara la notificación por correo
     * que contiene el enlace seguro para la carga del documento.
     */
    public function store(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'documento_id' => 'nullable|exists:documentos,id',
            'tipo_documento_id' => 'required_if:documento_id,null|nullable|exists:tipos_documento,id',
            'fecha_expiracion' => 'required|date|after_or_equal:today',
            'observacion' => 'nullable|string|max:1000',
        ]);

        $solicitud = SolicitudCarga::create([
            'usuario_id' => auth()->id(),
            'empleado_id' => $empleado->id,
            'documento_id' => $data['documento_id'] ?? null,
            'tipo_documento_id' => $data['tipo_documento_id'] ?? null,
            'fecha_expiracion' => $data['fecha_expiracion'],
            'observacion' => $data['observacion'] ?? null,
            'ticket' => 'REQ-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
            'estado' => 'Pendiente',
        ]);

        $empleado->notify(new SolicitudCargaCreada($solicitud));

        return response()->json($solicitud, 201);
    }

    /**
     * Muestra la vista pública para que el empleado suba el archivo.
     *
     * SEGURIDAD DE ACCESO PÚBLICO:
     * 1. Validación de Firma (Signed URL): Verifica que la URL no haya sido manipulada
     *    y que no haya expirado (tiempo de vida limitado).
     * 2. Validación de Estado (Token Burning): Verifica que la solicitud no haya sido
     *    completada previamente comprobando el timestamp 'token_used_at'.
     *
     * @param Request $request
     * @param SolicitudCarga $solicitudCarga
     */
    public function showUploadForm(Request $request, SolicitudCarga $solicitudCarga)
    {
        // 1. Verificar Firma Criptográfica de la URL
        if (! $request->hasValidSignature()) {
            abort(401, 'El enlace ha expirado o no es válido.');
        }

        // 2. Verificar si el enlace ya fue consumido
        if ($solicitudCarga->token_used_at || $solicitudCarga->estado === 'Completada') {
            return view('public.upload-used', ['ticket' => $solicitudCarga->ticket]);
        }

        return view('public.upload-form', compact('solicitudCarga'));
    }

    /**
     * Procesa la carga del archivo, actualiza el expediente y notifica a los analistas.
     *
     * FLUJO DE DATOS:
     * 1. Re-validación de seguridad (impide ataques de repetición).
     * 2. Almacenamiento seguro del archivo en disco.
     * 3. Actualización/Creación del Documento en estado 'En Revisión'.
     * 4. Invalidación del enlace (registro de 'token_used_at').
     * 5. Generación de notificaciones asíncronas para el equipo de RRHH.
     */
    public function processUpload(Request $request, SolicitudCarga $solicitudCarga)
    {
        // Validación de seguridad redundante
        if ($solicitudCarga->token_used_at || $solicitudCarga->estado === 'Completada') {
            return view('public.upload-used', ['ticket' => $solicitudCarga->ticket]);
        }

        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('archivo')->store('documentos', 'public');
        $documentoIdFinal = null;

        // Lógica de Negocio: Actualizar o Crear Documento
        if ($solicitudCarga->documento_id) {
            $documento = Documento::find($solicitudCarga->documento_id);
            if ($documento) {
                $documento->update([
                    'archivo' => $path,
                    'estado' => 'En Revisión' // El documento requiere aprobación del analista
                ]);
                $documentoIdFinal = $documento->id;
            }
        } else {
            if ($solicitudCarga->tipo_documento_id) {
                $nuevoDoc = $solicitudCarga->empleado->documentos()->create([
                    'tipo_documento_id' => $solicitudCarga->tipo_documento_id,
                    'archivo' => $path,
                    'estado' => 'En Revisión',
                    'fecha_vencimiento' => null
                ]);

                // Vinculación traza del documento creado con la solicitud
                $solicitudCarga->documento_id = $nuevoDoc->id;
                $documentoIdFinal = $nuevoDoc->id;
            }
        }

        // Cierre del ciclo de solicitud (Token Burning)
        $solicitudCarga->update([
            'estado' => 'Completada',
            'token_used_at' => now(), // Marca de tiempo que invalida el enlace
        ]);

        // Notificación proactiva a todos los usuarios con rol de gestión
        $encargados = User::whereIn('role', ['admin', 'analyst'])->get();

        foreach ($encargados as $usuario) {
            Notification::create([
                'user_id'      => $usuario->id,
                'empleado_id'  => $solicitudCarga->empleado_id,
                'documento_id' => $documentoIdFinal,
                'mensaje'      => "El empleado {$solicitudCarga->empleado->nombre} completó la solicitud {$solicitudCarga->ticket}.",
                'fecha_aviso'  => now(),
            ]);
        }

        return view('public.upload-success', [
            'ticket' => $solicitudCarga->ticket
        ]);
    }
}
