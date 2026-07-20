<?php

namespace App\Listeners;

use App\Models\Acceso;
use Illuminate\Auth\Events\Login;
use Throwable;

class RegistrarIngreso
{
    // Registra la bitacora de acceso; nunca debe interrumpir el login real si algo falla aqui.
    public function handle(Login $event): void
    {
        if (! $event->user) {
            return;
        }

        try {
            Acceso::create([
                'usuario_id' => $event->user->id,
                'fecha_ingreso' => now(),
                'ip_ingreso' => request()?->ip(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
