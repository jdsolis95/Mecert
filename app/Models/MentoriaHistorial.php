<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoriaHistorial extends Model
{
    protected $table = 'mentoria_historiales';

    protected $fillable = ['mentoria_id', 'editado_por_id', 'datos_anteriores'];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
        ];
    }

    public function mentoria(): BelongsTo
    {
        return $this->belongsTo(Mentoria::class);
    }

    public function editadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editado_por_id');
    }
}
