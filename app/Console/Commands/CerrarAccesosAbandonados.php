<?php

namespace App\Console\Commands;

use App\Models\Acceso;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CerrarAccesosAbandonados extends Command
{
    protected $signature = 'accesos:cerrar-abandonados';

    protected $description = 'Cierra accesos que quedaron abiertos porque la sesion se abandono (cerraron el navegador) sin volver a iniciar sesion';

    // Mismo umbral de inactividad que EnsureSessionIsNotIdle, para ser consistentes con esa regla.
    private const MINUTOS_INACTIVIDAD = 20;

    public function handle(): int
    {
        $umbral = now()->subMinutes(self::MINUTOS_INACTIVIDAD);
        $cerrados = 0;

        Acceso::whereNull('fecha_salida')->get()->each(function (Acceso $acceso) use ($umbral, &$cerrados) {
            $ultimaActividad = DB::table('sessions')
                ->where('user_id', $acceso->usuario_id)
                ->max('last_activity');

            $ultimaActividadEn = $ultimaActividad ? Carbon::createFromTimestamp($ultimaActividad) : null;

            if ($ultimaActividadEn && $ultimaActividadEn->gte($umbral)) {
                return;
            }

            $acceso->update([
                'fecha_salida' => $ultimaActividadEn && $ultimaActividadEn->gt($acceso->fecha_ingreso)
                    ? $ultimaActividadEn
                    : $acceso->fecha_ingreso,
            ]);

            $cerrados++;
        });

        $this->info("Accesos cerrados por abandono: {$cerrados}");

        return self::SUCCESS;
    }
}
