<?php

namespace App\Traits;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    public function auditorias(): MorphMany
    {
        return $this->morphMany(Auditoria::class, 'auditable')->latest();
    }

    // Deja constancia en la bitácora de auditoría del modelo (creado/modificado/eliminado)
    public function registrarAuditoria(string $accion, int $usuarioId, ?array $datosAnteriores = null, ?array $datosNuevos = null): void
    {
        $this->auditorias()->create([
            'accion' => $accion,
            'usuario_id' => $usuarioId,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
        ]);
    }
}
