<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Documento;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class VerifyExpirations extends Command
{
    protected $signature = 'contrataciones:verify-expirations';
    protected $description = 'Verifica los documentos próximos a vender y notifica a todos los encargados (Gerencia de Contrataciones).';

    /**
     * Ejecuta el proceso de verificación diaria de vencimientos.
     *
     * LÓGICA DEL ALGORITMO:
     * 1. Selecciona documentos activos con fecha de vencimiento futura.
     * 2. Para cada documento, calcula los días restantes (Diff in Days).
     * 3. Compara los días restantes con el 'periodo_alerta' configurado
     *    dinámicamente en el Tipo de Documento.
     * 4. Si está en rango de alerta, verifica si ya se envió una notificación HOY
     *    (para evitar duplicidad/spam).
     * 5. Si es necesario, genera notificaciones individuales para todos los
     *    usuarios con rol 'admin' o 'analyst'.
     */
    public function handle()
    {
        $this->info('Iniciando la verificación de vencimientos...');

        // Filtro inicial: Documentos con fecha definida y no vencidos
        $documentos = Documento::with('empleado', 'tipoDocumento')
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>=', Carbon::today())
            ->get();

        $notificacionesCreadas = 0;

        foreach ($documentos as $documento) {
            if ($documento->tipoDocumento?->periodo_alerta) {

                // Cálculo de días restantes para el vencimiento
                $diasParaVencer = Carbon::today()->diffInDays(Carbon::parse($documento->fecha_vencimiento), false);

                // Evaluación de la condición de alerta
                if ($diasParaVencer >= 0 && $diasParaVencer <= $documento->tipoDocumento->periodo_alerta) {

                    // Control de frecuencia: Evitar múltiples alertas el mismo día
                    $existeAlertaHoy = Notification::where('documento_id', $documento->id)
                                                ->whereDate('created_at', Carbon::today())
                                                ->exists();

                    if (!$existeAlertaHoy) {

                        // Obtención de destinatarios autorizados
                        $encargados = User::whereIn('role', ['admin', 'analyst'])->get();

                        if ($encargados->isEmpty()) {
                            $this->warn("-> Documento por vencer encontrado, pero no hay usuarios para notificar.");
                        }

                        // Generación de alertas personalizadas
                        foreach ($encargados as $usuario) {
                            Notification::create([
                                'user_id'      => $usuario->id,
                                'empleado_id'  => $documento->empleado_id,
                                'documento_id' => $documento->id,
                                'mensaje'      => "El documento '{$documento->tipoDocumento->nombre}' de {$documento->empleado->nombre} está próximo a vencer.",
                                'fecha_aviso'  => now(),
                            ]);
                            $notificacionesCreadas++;
                        }

                        if ($encargados->count() > 0) {
                            $this->info("-> Alerta enviada a {$encargados->count()} usuarios sobre: {$documento->empleado->nombre}");
                        }
                    }
                }
            }
        }

        $this->info("Verificación completada. Se generaron {$notificacionesCreadas} notificaciones en total.");
        
        // --- LIMPIEZA DE NOTIFICACIONES ANTIGUAS ---
        $this->info('Limpiando notificaciones leídas hace más de 24 horas...');
        $eliminadas = Notification::whereNotNull('read_at')
                        ->where('read_at', '<=', Carbon::now()->subDay())
                        ->delete();
        $this->info("Limpieza completada. Se eliminaron {$eliminadas} notificaciones antiguas.");

        return 0;
    }
}
