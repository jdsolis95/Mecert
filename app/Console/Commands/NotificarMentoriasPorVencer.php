<?php

namespace App\Console\Commands;

use App\Mail\MentoriaPorVencer;
use App\Models\Mentoria;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotificarMentoriasPorVencer extends Command
{
    protected $signature = 'mentorias:notificar-vencimientos';

    protected $description = 'Envía un correo único al autor (con copia a Controller) cuando una mentoría con vigencia entra en amarillo o en rojo';

    // Recorre las mentorías con vigencia y avisa una sola vez por cada umbral (amarillo/rojo) que cruzan
    public function handle(): void
    {
        $controllers = User::role('Controller')->pluck('email')->all();

        $enviados = 0;

        Mentoria::with('autor')
            ->whereNotNull('fecha_vencimiento')
            ->chunk(100, function ($mentorias) use ($controllers, &$enviados) {
                foreach ($mentorias as $mentoria) {
                    $estado = $mentoria->estado();

                    if ($estado === 'amarillo' && ! $mentoria->notificado_amarillo_en) {
                        Mail::to($mentoria->autor->email)->cc($controllers)->send(new MentoriaPorVencer($mentoria, 'amarillo'));
                        $mentoria->update(['notificado_amarillo_en' => now()]);
                        $enviados++;
                    } elseif ($estado === 'rojo' && ! $mentoria->notificado_rojo_en) {
                        Mail::to($mentoria->autor->email)->cc($controllers)->send(new MentoriaPorVencer($mentoria, 'rojo'));
                        $mentoria->update(['notificado_rojo_en' => now()]);
                        $enviados++;
                    }
                }
            });

        $this->info("Notificaciones enviadas: {$enviados}");
    }
}
