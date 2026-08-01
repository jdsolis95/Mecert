<?php

namespace App\Console\Commands;

use App\Mail\CertificadoExamenRecordatorio;
use App\Models\CertificadoExamen;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotificarExamenesProximos extends Command
{
    protected $signature = 'certificados:notificar-examenes-proximos';

    protected $description = 'Envía un recordatorio al colaborador 5 días antes de la fecha de un examen de certificación aprobado';

    // Busca los exámenes aprobados que caen exactamente 5 días adelante y manda el recordatorio una sola vez
    public function handle(): void
    {
        $fechaObjetivo = Carbon::today()->addDays(5);

        $examenes = CertificadoExamen::with(['certificado.colaborador', 'certificado.tipoCertificacion'])
            ->where('estado', 'aprobado')
            ->whereDate('fecha_aprobada', $fechaObjetivo)
            ->whereNull('notificado_recordatorio_en')
            ->get();

        foreach ($examenes as $examen) {
            Mail::to($examen->certificado->colaborador->email)->send(new CertificadoExamenRecordatorio($examen));
            $examen->update(['notificado_recordatorio_en' => now()]);
        }

        $this->info("Recordatorios enviados: {$examenes->count()}");
    }
}
