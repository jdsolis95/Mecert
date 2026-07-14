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
