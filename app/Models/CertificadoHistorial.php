<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificadoHistorial extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificado_id',
        'editado_por_id',
        'datos_anteriores',
    ];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
        ];
    }

    public function certificado(): BelongsTo
    {
        return $this->belongsTo(Certificado::class);
    }

    public function editadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editado_por_id');
    }
}
