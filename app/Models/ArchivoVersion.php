<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ArchivoVersion extends Model
{
    protected $fillable = [
        'versionable_type',
        'versionable_id',
        'campo',
        'path',
        'nombre_original',
        'subido_por_id',
    ];

    public function versionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por_id');
    }
}
