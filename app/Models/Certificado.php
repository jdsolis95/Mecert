<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificado extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'colaborador_id',
        'tipo_certificado',
        'fecha_emision',
        'fecha_vencimiento',
        'documento_path',
        'documento_nombre_original',
        'eliminado_por_id',
        'notificado_amarillo_en',
        'notificado_rojo_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'notificado_amarillo_en' => 'datetime',
            'notificado_rojo_en' => 'datetime',
        ];
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'colaborador_id');
    }

    public function eliminadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eliminado_por_id');
    }

    public function examenes(): HasMany
    {
        return $this->hasMany(CertificadoExamen::class)->latest();
    }

    public function historiales(): HasMany
    {
        return $this->hasMany(CertificadoHistorial::class)->latest();
    }

    public function scopeBuscar(Builder $query, string $texto): Builder
    {
        return $query->where(function (Builder $q) use ($texto) {
            $q->where('tipo_certificado', 'like', "%{$texto}%")
                ->orWhereHas('colaborador', function (Builder $cq) use ($texto) {
                    $cq->where('name', 'like', "%{$texto}%")
                        ->orWhere('primer_apellido', 'like', "%{$texto}%")
                        ->orWhereRaw("CONCAT(name, ' ', primer_apellido) LIKE ?", ["%{$texto}%"]);
                });
        });
    }

    public function scopeParaColaborador(Builder $query, int $colaboradorId): Builder
    {
        return $query->where('colaborador_id', $colaboradorId);
    }

    // Semáforo de vigencia: rojo si ya venció, amarillo dentro de la ventana de aviso, verde fuera de ella.
    public function estado(): string
    {
        $hoy = Carbon::today();
        $vencimiento = Carbon::parse($this->fecha_vencimiento);

        if ($vencimiento->lte($hoy)) {
            return 'rojo';
        }

        $umbral = $hoy->copy()->addMonths(config('certificados.meses_alerta', 3));

        return $vencimiento->lte($umbral) ? 'amarillo' : 'verde';
    }

    public function diasRestantes(): int
    {
        return Carbon::today()->diffInDays(Carbon::parse($this->fecha_vencimiento), false);
    }

    public function snapshotAuditoria(): array
    {
        return [
            'colaborador_id' => $this->colaborador_id,
            'tipo_certificado' => $this->tipo_certificado,
            'fecha_emision' => $this->fecha_emision?->toDateString(),
            'fecha_vencimiento' => $this->fecha_vencimiento?->toDateString(),
            'documento_path' => $this->documento_path,
            'documento_nombre_original' => $this->documento_nombre_original,
        ];
    }

    // Guarda el estado previo a una edición para conservar el historial de cambios.
    public function registrarHistorial(int $editadoPorId): void
    {
        $this->historiales()->create([
            'editado_por_id' => $editadoPorId,
            'datos_anteriores' => [
                'tipo_certificado' => $this->getOriginal('tipo_certificado'),
                'fecha_emision' => $this->getOriginal('fecha_emision'),
                'fecha_vencimiento' => $this->getOriginal('fecha_vencimiento'),
                'documento_path' => $this->getOriginal('documento_path'),
                'documento_nombre_original' => $this->getOriginal('documento_nombre_original'),
            ],
        ]);
    }
}
