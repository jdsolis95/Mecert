<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Auditoria extends Model
{
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'accion',
        'usuario_id',
        'datos_anteriores',
        'datos_nuevos',
    ];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
        ];
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
