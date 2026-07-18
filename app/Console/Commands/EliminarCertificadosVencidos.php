<?php

namespace App\Console\Commands;

use App\Models\Certificado;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class EliminarCertificadosVencidos extends Command
{
    protected $signature = 'certificados:eliminar-vencidos';

    protected $description = 'Elimina (soft-delete) los certificados vencidos hace más de los días de gracia definidos';

    public function handle(): void
    {
        $administrador = User::role('Administrador')->first();

        if (! $administrador) {
            $this->warn('No hay un usuario Administrador para atribuir la eliminación automática, se omite el proceso.');

            return;
        }

        $limite = Carbon::today()->subDays(config('certificados.dias_gracia_eliminacion', 30));

        $certificados = Certificado::where('fecha_vencimiento', '<', $limite)->get();

        foreach ($certificados as $certificado) {
            $datosAnteriores = $certificado->snapshotAuditoria();

            $certificado->update(['eliminado_por_id' => $administrador->id]);
            $certificado->registrarAuditoria('eliminado', $administrador->id, $datosAnteriores, null);
            $certificado->delete();
        }

        $this->info("Certificados eliminados automáticamente: {$certificados->count()}");
    }
}
