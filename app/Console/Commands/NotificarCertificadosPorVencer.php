<?php

namespace App\Console\Commands;

use App\Mail\CertificadoPorVencer;
use App\Models\Certificado;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotificarCertificadosPorVencer extends Command
{
    protected $signature = 'certificados:notificar-vencimientos';

    protected $description = 'Envía un correo único al colaborador (con copia a Controller) cuando un certificado entra en amarillo o en rojo';

    public function handle(): void
    {
        $controllers = User::role('Controller')->pluck('email')->all();

        $enviados = 0;

        Certificado::with('colaborador')->chunk(100, function ($certificados) use ($controllers, &$enviados) {
            foreach ($certificados as $certificado) {
                $estado = $certificado->estado();

                if ($estado === 'amarillo' && ! $certificado->notificado_amarillo_en) {
                    Mail::to($certificado->colaborador->email)->cc($controllers)->send(new CertificadoPorVencer($certificado, 'amarillo'));
                    $certificado->update(['notificado_amarillo_en' => now()]);
                    $enviados++;
                } elseif ($estado === 'rojo' && ! $certificado->notificado_rojo_en) {
                    Mail::to($certificado->colaborador->email)->cc($controllers)->send(new CertificadoPorVencer($certificado, 'rojo'));
                    $certificado->update(['notificado_rojo_en' => now()]);
                    $enviados++;
                }
            }
        });

        $this->info("Notificaciones enviadas: {$enviados}");
    }
}
