<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acceso extends Model
{
    protected $fillable = [
        'usuario_id',
        'fecha_ingreso',
        'fecha_salida',
        'ip_ingreso',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'datetime',
            'fecha_salida' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function scopeAbierto(Builder $query, int $usuarioId): Builder
    {
        return $query->where('usuario_id', $usuarioId)
            ->whereNull('fecha_salida')
            ->latest('fecha_ingreso');
    }

    public function cerrar(): void
    {
        $this->update(['fecha_salida' => now()]);
    }
}
