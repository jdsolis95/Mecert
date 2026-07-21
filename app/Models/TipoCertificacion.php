<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCertificacion extends Model
{
    protected $table = 'tipos_certificacion';

    protected $fillable = ['nombre', 'activo'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class, 'tipo_certificado_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    // Un tipo solo puede eliminarse (hard delete) si ningun certificado lo referencia.
    public function enUso(): bool
    {
        return $this->certificados()->exists();
    }
}
