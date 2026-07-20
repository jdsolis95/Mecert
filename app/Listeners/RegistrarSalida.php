<?php

namespace App\Listeners;

use App\Models\Acceso;
use Illuminate\Auth\Events\Logout;
use Throwable;

class RegistrarSalida
{
    // Cierra la fila de acceso abierta mas reciente; nunca debe interrumpir el logout real si algo falla aqui.
    public function handle(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        try {
            Acceso::abierto($event->user->id)->first()?->cerrar();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
