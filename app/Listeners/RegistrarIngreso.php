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
            // Si quedaron accesos abiertos de una sesion anterior abandonada (el usuario cerro
            // el navegador sin usar "Salir" y nunca volvio a activar el chequeo de inactividad),
            // los cerramos aqui: un login nuevo implica que la sesion previa ya no sigue activa.
            Acceso::abierto($event->user->id)->get()->each->cerrar();

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
