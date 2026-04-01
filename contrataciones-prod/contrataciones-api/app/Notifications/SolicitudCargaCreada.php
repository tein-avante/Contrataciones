<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use App\Models\SolicitudCarga;
use App\Models\Documento;
use Carbon\Carbon;

class SolicitudCargaCreada extends Notification implements ShouldQueue
{
    use Queueable;

    public $solicitud;

    public function __construct(SolicitudCarga $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $signedUrl = URL::temporarySignedRoute(
            'solicitud-carga.show',
            now()->addDays(7),
            ['solicitudCarga' => $this->solicitud->id]
        );

        // --- LÓGICA PARA IDENTIFICAR EL DOCUMENTO Y LA ACCIÓN ---
        $nombreDocumento = "Documento requerido";
        $accion = "subir"; // Por defecto

        // CASO A: Es una actualización de un documento existente
        if ($this->solicitud->documento_id) {
            $doc = Documento::find($this->solicitud->documento_id);
            if ($doc && $doc->tipoDocumento) {
                $nombreDocumento = $doc->tipoDocumento->nombre;
                $accion = "actualizar";
            }
        }
        // CASO B: Es un documento nuevo pero especificamos el tipo
        elseif ($this->solicitud->tipo_documento_id) {
             // Usamos la relación que acabamos de agregar en el Modelo
             if($this->solicitud->tipoDocumento){
                 $nombreDocumento = $this->solicitud->tipoDocumento->nombre;
             }
        }

        return (new MailMessage)
            ->subject('Solicitud de Carga - Ticket: ' . $this->solicitud->ticket)
            ->view('emails.solicitud-carga', [
                'solicitud' => $this->solicitud,
                'url' => $signedUrl,
                'notifiable' => $notifiable,
                // Pasamos las variables calculadas a la vista
                'nombreDocumento' => $nombreDocumento,
                'accion' => $accion
            ]);
    }
}
