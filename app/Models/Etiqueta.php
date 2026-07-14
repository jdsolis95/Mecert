<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Etiqueta extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    public function mentorias(): BelongsToMany
    {
        return $this->belongsToMany(Mentoria::class, 'mentoria_etiqueta');
    }

    // Crea las etiquetas que no existan y devuelve los ids listos para sync().
    public static function idsParaNombres(array $nombres): array
    {
        return collect($nombres)
            ->map(fn ($nombre) => trim($nombre))
            ->filter()
            ->unique()
            ->map(fn ($nombre) => static::firstOrCreate(['nombre' => $nombre])->id)
            ->all();
    }
}
