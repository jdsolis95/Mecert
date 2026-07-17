<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificadoExamen extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificado_id',
        'fecha_propuesta',
        'lugar_propuesto',
        'propuesto_por_id',
        'estado',
        'fecha_aprobada',
        'lugar_aprobado',
        'comentario',
        'decidido_por_id',
        'decidido_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_propuesta' => 'date',
            'fecha_aprobada' => 'date',
            'decidido_at' => 'datetime',
        ];
    }

    public function certificado(): BelongsTo
    {
        return $this->belongsTo(Certificado::class);
    }

    public function propuestoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'propuesto_por_id');
    }

    public function decididoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decidido_por_id');
    }
}
