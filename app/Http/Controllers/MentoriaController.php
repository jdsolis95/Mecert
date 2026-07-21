<?php

namespace App\Http\Controllers;

use App\Models\Etiqueta;
use App\Models\Mentoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MentoriaController extends Controller
{
    public function index(Request $request)
    {
        $etiquetaIds = collect($request->input('etiquetas', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $mentorias = Mentoria::query()
            ->with(['autor:id,name,primer_apellido', 'etiquetas:id,nombre'])
            ->when($request->filled('q'), fn ($query) => $query->buscar($request->string('q')->toString()))
            ->when(count($etiquetaIds) > 0, fn ($query) => $query->conEtiquetas($etiquetaIds))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Mentoria $mentoria) => $this->mapaListado($mentoria));

        return Inertia::render('Mentorias/Index', [
            'mentorias' => $mentorias,
            'etiquetasDisponibles' => Etiqueta::orderBy('nombre')->get(['id', 'nombre']),
            'filtros' => [
                'q' => $request->input('q', ''),
                'etiquetas' => $etiquetaIds,
            ],
            'puedeAdministrarCatalogos' => $request->user()->hasRole('Administrador'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Mentorias/Create', [
            'etiquetasDisponibles' => Etiqueta::activas()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->mentoriaRules($request), $this->mentoriaMessages($request));

        DB::transaction(function () use ($request) {
            $mentoria = Mentoria::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'autor_id' => $request->user()->id,
                ...$this->resolverMultimedia($request),
            ]);

            $mentoria->etiquetas()->sync($request->input('etiquetas', []));

            $this->sincronizarEnlaces($mentoria, $request->input('enlaces', []));

            $mentoria->registrarAuditoria('creado', $request->user()->id, null, $mentoria->snapshotAuditoria());
        });

        return redirect()->route('mentorias.index')->with('mensaje', 'Publicación creada correctamente.');
    }

    public function show(Request $request, Mentoria $mentoria)
    {
        $mentoria->load(['autor:id,name,primer_apellido', 'etiquetas:id,nombre', 'enlaces']);

        return Inertia::render('Mentorias/Show', [
            'mentoria' => [
                'id' => $mentoria->id,
                'titulo' => $mentoria->titulo,
                'descripcion' => $mentoria->descripcion,
                'autor' => $this->nombreAutor($mentoria),
                'fecha' => $mentoria->created_at->format('d/m/Y'),
                'etiquetas' => $mentoria->etiquetas->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre])->all(),
                'multimedia' => $this->mapaMultimedia($mentoria),
                'enlaces' => $mentoria->enlaces->map(fn ($e) => ['url' => $e->url, 'texto' => $e->texto])->all(),
            ],
            'puedeEditar' => $request->user()->can('update', $mentoria),
        ]);
    }

    public function edit(Mentoria $mentoria)
    {
        Gate::authorize('update', $mentoria);

        $mentoria->load(['etiquetas:id,nombre', 'enlaces']);

        return Inertia::render('Mentorias/Edit', [
            'mentoria' => [
                'id' => $mentoria->id,
                'titulo' => $mentoria->titulo,
                'descripcion' => $mentoria->descripcion,
                'etiquetas' => $mentoria->etiquetas->pluck('id')->all(),
                'enlaces' => $mentoria->enlaces->map(fn ($e) => ['url' => $e->url, 'texto' => $e->texto])->all(),
                'multimedia_tipo' => $mentoria->multimedia_tipo,
                'multimedia_url' => $mentoria->multimedia_url,
                'multimedia_nombre_original' => $mentoria->multimedia_nombre_original,
                'multimedia_preview_url' => $mentoria->multimedia_path ? Storage::url($mentoria->multimedia_path) : null,
            ],
            // Etiquetas activas + las ya asignadas a esta mentoria aunque hayan sido deshabilitadas despues
            'etiquetasDisponibles' => Etiqueta::activas()
                ->orWhereHas('mentorias', fn ($q) => $q->where('mentorias.id', $mentoria->id))
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function update(Request $request, Mentoria $mentoria)
    {
        Gate::authorize('update', $mentoria);

        $request->validate($this->mentoriaRules($request, $mentoria), $this->mentoriaMessages($request));

        DB::transaction(function () use ($request, $mentoria) {
            $datosAnteriores = $mentoria->snapshotAuditoria();

            $mentoria->registrarHistorial($request->user()->id);

            $mentoria->update([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                ...$this->resolverMultimedia($request, $mentoria),
            ]);

            $mentoria->etiquetas()->sync($request->input('etiquetas', []));

            $mentoria->enlaces()->delete();
            $this->sincronizarEnlaces($mentoria, $request->input('enlaces', []));

            $mentoria->registrarAuditoria('modificado', $request->user()->id, $datosAnteriores, $mentoria->snapshotAuditoria());
        });

        return redirect()->route('mentorias.index')->with('mensaje', 'Publicación actualizada correctamente.');
    }

    public function destroy(Request $request, Mentoria $mentoria)
    {
        Gate::authorize('delete', $mentoria);

        $datosAnteriores = $mentoria->snapshotAuditoria();

        $mentoria->update(['eliminado_por_id' => $request->user()->id]);
        $mentoria->registrarAuditoria('eliminado', $request->user()->id, $datosAnteriores, null);
        $mentoria->delete();

        return redirect()->route('mentorias.index')->with('mensaje', 'Publicación eliminada correctamente.');
    }

    private function sincronizarEnlaces(Mentoria $mentoria, array $enlaces): void
    {
        foreach ($enlaces as $enlace) {
            $mentoria->enlaces()->create([
                'url' => $enlace['url'],
                'texto' => $enlace['texto'] ?? null,
            ]);
        }
    }

    private function nombreAutor(Mentoria $mentoria): string
    {
        return trim($mentoria->autor->name . ' ' . $mentoria->autor->primer_apellido);
    }

    private function mapaListado(Mentoria $mentoria): array
    {
        return [
            'id' => $mentoria->id,
            'titulo' => $mentoria->titulo,
            'descripcion' => Str::limit($mentoria->descripcion, 160),
            'autor' => $this->nombreAutor($mentoria),
            'fecha' => $mentoria->created_at->format('d/m/Y'),
            'etiquetas' => $mentoria->etiquetas->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre])->all(),
            'multimedia' => $this->mapaMultimedia($mentoria),
        ];
    }

    private function mapaMultimedia(Mentoria $mentoria): ?array
    {
        if (! $mentoria->multimedia_tipo) {
            return null;
        }

        return [
            'tipo' => $mentoria->multimedia_tipo,
            'url' => $mentoria->multimedia_path
                ? Storage::url($mentoria->multimedia_path)
                : $mentoria->multimedia_url,
            'nombre_original' => $mentoria->multimedia_nombre_original,
        ];
    }

    // Aplica el archivo de multimedia según el tipo seleccionado y conserva
    // el archivo actual si no se sube uno nuevo (evita perderlo en una edición).
    private function resolverMultimedia(Request $request, ?Mentoria $existente = null): array
    {
        $tipo = $request->input('multimedia_tipo') ?: null;

        if (in_array($tipo, ['imagen', 'documento', 'video'], true)) {
            if ($request->hasFile('multimedia_archivo')) {
                if ($existente?->multimedia_path) {
                    Storage::disk('public')->delete($existente->multimedia_path);
                }

                $archivo = $request->file('multimedia_archivo');

                return [
                    'multimedia_tipo' => $tipo,
                    'multimedia_path' => $archivo->store('mentorias', 'public'),
                    'multimedia_nombre_original' => $archivo->getClientOriginalName(),
                    'multimedia_url' => null,
                ];
            }

            if ($existente && $existente->multimedia_tipo === $tipo && $existente->multimedia_path) {
                return [
                    'multimedia_tipo' => $tipo,
                    'multimedia_path' => $existente->multimedia_path,
                    'multimedia_nombre_original' => $existente->multimedia_nombre_original,
                    'multimedia_url' => null,
                ];
            }

            return [
                'multimedia_tipo' => $tipo,
                'multimedia_path' => null,
                'multimedia_nombre_original' => null,
                'multimedia_url' => null,
            ];
        }

        if ($existente?->multimedia_path) {
            Storage::disk('public')->delete($existente->multimedia_path);
        }

        return [
            'multimedia_tipo' => null,
            'multimedia_path' => null,
            'multimedia_nombre_original' => null,
            'multimedia_url' => null,
        ];
    }

    private function mentoriaRules(Request $request, ?Mentoria $mentoria = null): array
    {
        $tipo = $request->input('multimedia_tipo');
        $tieneArchivoActual = $mentoria && $mentoria->multimedia_tipo === $tipo && $mentoria->multimedia_path;

        return [
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string'],

            'etiquetas' => ['required', 'array', 'min:1'],
            // Valida activa, o ya asignada a esta mentoria (permite conservarla aunque se haya deshabilitado despues)
            'etiquetas.*' => ['required', 'integer', function ($attribute, $value, $fail) use ($mentoria) {
                $activa = Etiqueta::where('id', $value)->where('activo', true)->exists();
                $yaAsignada = $mentoria && $mentoria->etiquetas()->where('etiquetas.id', $value)->exists();

                if (! $activa && ! $yaAsignada) {
                    $fail('La etiqueta seleccionada no está disponible.');
                }
            }],

            'multimedia_tipo' => ['nullable', Rule::in(['imagen', 'documento', 'video'])],

            'multimedia_archivo' => [
                Rule::requiredIf(
                    in_array($tipo, ['imagen', 'documento', 'video'], true)
                    && ! $request->hasFile('multimedia_archivo')
                    && ! $tieneArchivoActual
                ),
                'nullable',
                'file',
                Rule::when($tipo === 'imagen', ['max:5120', 'mimes:jpg,jpeg,png,webp']),
                Rule::when($tipo === 'documento', ['max:5120', 'mimes:pdf']),
                Rule::when($tipo === 'video', ['max:102400', 'mimetypes:video/mp4', 'mimes:mp4']),
            ],

            'enlaces' => ['nullable', 'array'],
            'enlaces.*.url' => ['required', 'url', 'max:2048'],
            'enlaces.*.texto' => ['nullable', 'string', 'max:150'],
        ];
    }

    private function mentoriaMessages(Request $request): array
    {
        return [
            'multimedia_archivo.mimes' => match ($request->input('multimedia_tipo')) {
                'documento' => 'Solo se permiten archivos en formato PDF.',
                'imagen' => 'Solo se permiten imágenes en formato JPG, PNG o WEBP.',
                'video' => 'Solo se permiten videos en formato MP4.',
                default => 'El formato del archivo no es válido.',
            },
            'multimedia_archivo.mimetypes' => 'Solo se permiten videos en formato MP4.',
        ];
    }
}
