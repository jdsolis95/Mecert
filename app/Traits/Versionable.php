<?php

namespace App\Traits;

use App\Models\ArchivoVersion;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Versionable
{
    public function versionesArchivo(): MorphMany
    {
        return $this->morphMany(ArchivoVersion::class, 'versionable')->latest();
    }

    // Guarda cada archivo subido como una nueva versión, sin borrar las anteriores del historial.
    public function registrarVersion(string $campo, string $path, ?string $nombreOriginal, int $subidoPorId): void
    {
        $this->versionesArchivo()->create([
            'campo' => $campo,
            'path' => $path,
            'nombre_original' => $nombreOriginal,
            'subido_por_id' => $subidoPorId,
        ]);
    }
}
