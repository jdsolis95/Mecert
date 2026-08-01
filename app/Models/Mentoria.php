<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\Versionable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mentoria extends Model
{
    use HasFactory, SoftDeletes, Auditable, Versionable;

    protected $fillable = [
        'titulo',
        'descripcion',
        'autor_id',
        'multimedia_tipo',
        'multimedia_path',
        'multimedia_nombre_original',
        'multimedia_url',
        'eliminado_por_id',
        'fecha_vencimiento',
        'notificado_amarillo_en',
        'notificado_rojo_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'notificado_amarillo_en' => 'datetime',
            'notificado_rojo_en' => 'datetime',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function eliminadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eliminado_por_id');
    }

    public function etiquetas(): BelongsToMany
    {
        return $this->belongsToMany(Etiqueta::class, 'mentoria_etiqueta');
    }

    public function enlaces(): HasMany
    {
        return $this->hasMany(MentoriaEnlace::class);
    }

    public function historiales(): HasMany
    {
        return $this->hasMany(MentoriaHistorial::class)->latest();
    }

    public function scopeBuscar(Builder $query, string $texto): Builder
    {
        return $query->where(function (Builder $q) use ($texto) {
            $q->where('titulo', 'like', "%{$texto}%")
                ->orWhere('descripcion', 'like', "%{$texto}%")
                ->orWhereHas('etiquetas', fn (Builder $eq) => $eq->where('nombre', 'like', "%{$texto}%"))
                ->orWhereHas('autor', function (Builder $aq) use ($texto) {
                    $aq->where('name', 'like', "%{$texto}%")
                        ->orWhere('primer_apellido', 'like', "%{$texto}%")
                        ->orWhereRaw("CONCAT(name, ' ', primer_apellido) LIKE ?", ["%{$texto}%"]);
                });
        });
    }

    public function scopeConEtiquetas(Builder $query, array $etiquetaIds): Builder
    {
        return $query->whereHas('etiquetas', fn (Builder $eq) => $eq->whereIn('etiquetas.id', $etiquetaIds));
    }

    // Semáforo de vigencia: null si la mentoría no tiene control de vigencia (fecha_vencimiento vacía),
    // rojo si ya venció, amarillo dentro de la ventana de aviso, verde fuera de ella.
    public function estado(): ?string
    {
        if (! $this->fecha_vencimiento) {
            return null;
        }

        $hoy = Carbon::today();
        $vencimiento = Carbon::parse($this->fecha_vencimiento);

        if ($vencimiento->lte($hoy)) {
            return 'rojo';
        }

        $umbral = $hoy->copy()->addMonths(config('mentorias.meses_alerta', 3));

        return $vencimiento->lte($umbral) ? 'amarillo' : 'verde';
    }

    // Null si no tiene vigencia definida, negativo si ya venció
    public function diasRestantes(): ?int
    {
        if (! $this->fecha_vencimiento) {
            return null;
        }

        return Carbon::today()->diffInDays(Carbon::parse($this->fecha_vencimiento), false);
    }

    // Estado actual auditable (para las bitácoras de creación/edición/eliminación).
    public function snapshotAuditoria(): array
    {
        return [
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'etiquetas' => $this->etiquetas()->pluck('nombre')->all(),
            'multimedia_tipo' => $this->multimedia_tipo,
            'multimedia_path' => $this->multimedia_path,
            'multimedia_nombre_original' => $this->multimedia_nombre_original,
            'multimedia_url' => $this->multimedia_url,
            'enlaces' => $this->enlaces()->get(['url', 'texto'])->toArray(),
            'fecha_vencimiento' => $this->fecha_vencimiento?->toDateString(),
        ];
    }

    // Guarda el estado previo a una edición para conservar el historial de cambios.
    public function registrarHistorial(int $editadoPorId): void
    {
        $this->historiales()->create([
            'editado_por_id' => $editadoPorId,
            'datos_anteriores' => [
                'titulo' => $this->getOriginal('titulo'),
                'descripcion' => $this->getOriginal('descripcion'),
                'etiquetas' => $this->etiquetas()->pluck('nombre')->all(),
                'multimedia_tipo' => $this->getOriginal('multimedia_tipo'),
                'multimedia_path' => $this->getOriginal('multimedia_path'),
                'multimedia_nombre_original' => $this->getOriginal('multimedia_nombre_original'),
                'multimedia_url' => $this->getOriginal('multimedia_url'),
                'enlaces' => $this->enlaces()->get(['url', 'texto'])->toArray(),
                'fecha_vencimiento' => $this->getOriginal('fecha_vencimiento'),
            ],
        ]);
    }
}
