<?php

namespace App\Http\Controllers;

use App\Mail\CertificadoPorVencer;
use App\Models\Certificado;
use App\Models\CertificadoExamen;
use App\Models\TipoCertificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CertificadoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $soloPropios = $user->hasRole('Colaborador') && ! $user->hasAnyRole(['Administrador', 'Controller']);

        $certificados = Certificado::query()
            ->with(['colaborador:id,name,primer_apellido,segundo_apellido', 'tipoCertificacion:id,nombre'])
            ->when($soloPropios, fn ($query) => $query->paraColaborador($user->id))
            ->when($request->filled('q'), fn ($query) => $query->buscar($request->string('q')->toString()))
            ->latest()
            ->get()
            ->when($request->filled('estado'), fn ($coleccion) => $coleccion->filter(
                fn (Certificado $certificado) => $certificado->estado() === $request->string('estado')->toString()
            ))
            ->values()
            ->map(fn (Certificado $certificado) => $this->mapaListado($certificado, $user));

        $puedeAprobarExamenes = $user->hasAnyRole(['Administrador', 'Controller']);

        return Inertia::render('Certificados/Index', [
            'certificados' => $certificados,
            'filtros' => [
                'q' => $request->input('q', ''),
                'estado' => $request->input('estado', ''),
            ],
            'puedeCrear' => Gate::allows('create', Certificado::class),
            'puedeAprobarExamenes' => $puedeAprobarExamenes,
            'examenesPendientesCount' => $puedeAprobarExamenes
                ? CertificadoExamen::where('estado', 'pendiente')->count()
                : 0,
            'puedeAdministrarCatalogos' => $user->hasRole('Administrador'),
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Certificado::class);

        $user = $request->user();
        $esGestor = $user->hasAnyRole(['Administrador', 'Controller']);

        return Inertia::render('Certificados/Create', [
            'colaboradores' => $esGestor ? $this->colaboradoresParaSelect() : [],
            'colaboradorFijo' => $esGestor ? null : [
                'id' => $user->id,
                'nombre' => trim($user->name . ' ' . $user->primer_apellido),
            ],
            'tiposCertificacion' => TipoCertificacion::activos()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Certificado::class);

        $colaboradorId = $this->resolverColaboradorId($request);
        $request->merge(['colaborador_id' => $colaboradorId]);

        $request->validate($this->certificadoRules($request, $colaboradorId));

        DB::transaction(function () use ($request, $colaboradorId) {
            $certificado = Certificado::create([
                'colaborador_id' => $colaboradorId,
                'tipo_certificado_id' => $request->tipo_certificado_id,
                'fecha_emision' => $request->fecha_emision,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                ...$this->resolverDocumento($request),
            ]);

            $certificado->registrarAuditoria('creado', $request->user()->id, null, $certificado->snapshotAuditoria());
        });

        return redirect()->route('certificados.index')->with('mensaje', 'Certificado creado correctamente.');
    }

    public function show(Request $request, Certificado $certificado)
    {
        Gate::authorize('view', $certificado);

        $certificado->load([
            'colaborador:id,name,primer_apellido,segundo_apellido',
            'tipoCertificacion:id,nombre',
            'historiales.editadoPor:id,name,primer_apellido',
            'examenes.propuestoPor:id,name,primer_apellido',
            'examenes.decididoPor:id,name,primer_apellido',
        ]);

        $user = $request->user();

        return Inertia::render('Certificados/Show', [
            'certificado' => [
                'id' => $certificado->id,
                'colaborador' => trim($certificado->colaborador->name . ' ' . $certificado->colaborador->primer_apellido),
                'tipo_certificado' => $certificado->tipoCertificacion->nombre,
                'fecha_emision' => $certificado->fecha_emision->format('d/m/Y'),
                'fecha_vencimiento' => $certificado->fecha_vencimiento->format('d/m/Y'),
                'estado' => $certificado->estado(),
                'dias_restantes' => $certificado->diasRestantes(),
                'documento_nombre_original' => $certificado->documento_nombre_original,
                'documento_url' => $certificado->documento_path ? Storage::url($certificado->documento_path) : null,
            ],
            'historiales' => $certificado->historiales->map(fn (\App\Models\CertificadoHistorial $historial) => [
                'id' => $historial->id,
                'editado_por' => trim($historial->editadoPor->name . ' ' . $historial->editadoPor->primer_apellido),
                'datos_anteriores' => $historial->datos_anteriores,
                'fecha' => $historial->created_at->format('d/m/Y H:i'),
            ]),
            'examenes' => $certificado->examenes->map(fn (CertificadoExamen $examen) => [
                'id' => $examen->id,
                'estado' => $examen->estado,
                'fecha_propuesta' => $examen->fecha_propuesta->format('d/m/Y'),
                'lugar_propuesto' => $examen->lugar_propuesto,
                'propuesto_por' => trim($examen->propuestoPor->name . ' ' . $examen->propuestoPor->primer_apellido),
                'fecha_aprobada' => $examen->fecha_aprobada?->format('d/m/Y'),
                'lugar_aprobado' => $examen->lugar_aprobado,
                'comentario' => $examen->comentario,
                'decidido_por' => $examen->decididoPor ? trim($examen->decididoPor->name . ' ' . $examen->decididoPor->primer_apellido) : null,
            ]),
            'puedeEditar' => $user->can('update', $certificado),
        ]);
    }

    public function edit(Request $request, Certificado $certificado)
    {
        Gate::authorize('update', $certificado);

        $user = $request->user();
        $esGestor = $user->hasAnyRole(['Administrador', 'Controller']);

        $certificado->load('colaborador:id,name,primer_apellido,segundo_apellido');

        return Inertia::render('Certificados/Edit', [
            'certificado' => [
                'id' => $certificado->id,
                'colaborador_id' => $certificado->colaborador_id,
                'tipo_certificado_id' => $certificado->tipo_certificado_id,
                'fecha_emision' => $certificado->fecha_emision->toDateString(),
                'fecha_vencimiento' => $certificado->fecha_vencimiento->toDateString(),
                'documento_nombre_original' => $certificado->documento_nombre_original,
                'documento_url' => $certificado->documento_path ? Storage::url($certificado->documento_path) : null,
            ],
            'colaboradores' => $esGestor ? $this->colaboradoresParaSelect() : [],
            'colaboradorFijo' => $esGestor ? null : [
                'id' => $certificado->colaborador->id,
                'nombre' => trim($certificado->colaborador->name . ' ' . $certificado->colaborador->primer_apellido),
            ],
            // Tipos activos + el tipo actual aunque haya sido deshabilitado despues, para no perderlo del select
            'tiposCertificacion' => TipoCertificacion::activos()
                ->orWhere('id', $certificado->tipo_certificado_id)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'activo']),
        ]);
    }

    public function update(Request $request, Certificado $certificado)
    {
        Gate::authorize('update', $certificado);

        $colaboradorId = $request->user()->hasAnyRole(['Administrador', 'Controller'])
            ? $this->resolverColaboradorId($request)
            : $certificado->colaborador_id;
        $request->merge(['colaborador_id' => $colaboradorId]);

        $request->validate($this->certificadoRules($request, $colaboradorId, $certificado));

        DB::transaction(function () use ($request, $certificado, $colaboradorId) {
            $datosAnteriores = $certificado->snapshotAuditoria();

            $certificado->registrarHistorial($request->user()->id);

            $certificado->update([
                'colaborador_id' => $colaboradorId,
                'tipo_certificado_id' => $request->tipo_certificado_id,
                'fecha_emision' => $request->fecha_emision,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                // Si cambió la fecha de vencimiento, se reabre la ventana de avisos.
                'notificado_amarillo_en' => null,
                'notificado_rojo_en' => null,
                ...$this->resolverDocumento($request, $certificado),
            ]);

            $certificado->registrarAuditoria('modificado', $request->user()->id, $datosAnteriores, $certificado->snapshotAuditoria());
        });

        return redirect()->route('certificados.index')->with('mensaje', 'Certificado actualizado correctamente.');
    }

    public function destroy(Request $request, Certificado $certificado)
    {
        Gate::authorize('delete', $certificado);

        $datosAnteriores = $certificado->snapshotAuditoria();

        $certificado->update(['eliminado_por_id' => $request->user()->id]);
        $certificado->registrarAuditoria('eliminado', $request->user()->id, $datosAnteriores, null);
        $certificado->delete();

        return redirect()->route('certificados.index')->with('mensaje', 'Certificado eliminado correctamente.');
    }

    public function proponerExamen(Request $request, Certificado $certificado)
    {
        Gate::authorize('proponerExamen', $certificado);

        $request->validate([
            'fecha_propuesta' => ['required', 'date', 'after_or_equal:today'],
            'lugar_propuesto' => ['nullable', 'string', 'max:150'],
        ]);

        $certificado->examenes()->create([
            'fecha_propuesta' => $request->fecha_propuesta,
            'lugar_propuesto' => $request->lugar_propuesto,
            'propuesto_por_id' => $request->user()->id,
            'estado' => 'pendiente',
        ]);

        return back()->with('mensaje', 'Examen de renovación calendarizado, queda pendiente de aprobación.');
    }

    public function examenesPendientes()
    {
        Gate::authorize('aprobarExamen', Certificado::class);

        $examenes = CertificadoExamen::query()
            ->with(['certificado.colaborador:id,name,primer_apellido,segundo_apellido', 'certificado.tipoCertificacion:id,nombre', 'propuestoPor:id,name,primer_apellido'])
            ->where('estado', 'pendiente')
            ->latest()
            ->get()
            ->map(fn (CertificadoExamen $examen) => [
                'id' => $examen->id,
                'certificado' => [
                    'id' => $examen->certificado->id,
                    'tipo_certificado' => $examen->certificado->tipoCertificacion->nombre,
                    'colaborador' => trim($examen->certificado->colaborador->name . ' ' . $examen->certificado->colaborador->primer_apellido),
                ],
                'fecha_propuesta' => $examen->fecha_propuesta->format('d/m/Y'),
                'lugar_propuesto' => $examen->lugar_propuesto,
                'propuesto_por' => trim($examen->propuestoPor->name . ' ' . $examen->propuestoPor->primer_apellido),
                'creado' => $examen->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Certificados/Examenes', [
            'examenes' => $examenes,
        ]);
    }

    public function decidirExamen(Request $request, CertificadoExamen $examen)
    {
        Gate::authorize('aprobarExamen', Certificado::class);

        $request->validate([
            'accion' => ['required', Rule::in(['aprobar', 'rechazar'])],
            'fecha_aprobada' => [Rule::requiredIf($request->accion === 'aprobar'), 'nullable', 'date'],
            'lugar_aprobado' => [Rule::requiredIf($request->accion === 'aprobar'), 'nullable', 'string', 'max:150'],
            'comentario' => ['nullable', 'string', 'max:500'],
        ]);

        $examen->update([
            'estado' => $request->accion === 'aprobar' ? 'aprobado' : 'rechazado',
            'fecha_aprobada' => $request->accion === 'aprobar' ? $request->fecha_aprobada : null,
            'lugar_aprobado' => $request->accion === 'aprobar' ? $request->lugar_aprobado : null,
            'comentario' => $request->comentario,
            'decidido_por_id' => $request->user()->id,
            'decidido_at' => now(),
        ]);

        return back()->with('mensaje', $request->accion === 'aprobar' ? 'Examen aprobado.' : 'Examen rechazado.');
    }

    private function resolverColaboradorId(Request $request): int
    {
        $user = $request->user();

        if (! $user->hasAnyRole(['Administrador', 'Controller'])) {
            return $user->id;
        }

        return (int) $request->input('colaborador_id');
    }

    private function colaboradoresParaSelect()
    {
        return User::where('esta_activo', true)
            ->orderBy('primer_apellido')
            ->get(['id', 'name', 'primer_apellido', 'segundo_apellido'])
            ->map(fn (User $usuario) => [
                'id' => $usuario->id,
                'nombre' => trim($usuario->name . ' ' . $usuario->primer_apellido . ' ' . $usuario->segundo_apellido),
            ]);
    }

    private function resolverDocumento(Request $request, ?Certificado $existente = null): array
    {
        if ($request->hasFile('documento_adjunto')) {
            if ($existente?->documento_path) {
                Storage::disk('public')->delete($existente->documento_path);
            }

            $archivo = $request->file('documento_adjunto');

            return [
                'documento_path' => $archivo->store('certificados', 'public'),
                'documento_nombre_original' => $archivo->getClientOriginalName(),
            ];
        }

        return [];
    }

    private function mapaListado(Certificado $certificado, User $user): array
    {
        return [
            'id' => $certificado->id,
            'colaborador' => trim($certificado->colaborador->name . ' ' . $certificado->colaborador->primer_apellido),
            'tipo_certificado' => $certificado->tipoCertificacion->nombre,
            'fecha_emision' => $certificado->fecha_emision->format('d/m/Y'),
            'fecha_vencimiento' => $certificado->fecha_vencimiento->format('d/m/Y'),
            'estado' => $certificado->estado(),
            'dias_restantes' => $certificado->diasRestantes(),
            'documento_url' => $certificado->documento_path ? Storage::url($certificado->documento_path) : null,
            'puede_ver' => $user->can('view', $certificado),
            'puede_editar' => $user->can('update', $certificado),
            'puede_eliminar' => $user->can('delete', $certificado),
            'puede_proponer_examen' => $user->can('proponerExamen', $certificado),
        ];
    }

    private function certificadoRules(Request $request, int $colaboradorId, ?Certificado $certificado = null): array
    {
        return [
            'colaborador_id' => ['required', 'exists:users,id'],
            'tipo_certificado_id' => [
                'required', 'exists:tipos_certificacion,id',
                Rule::unique('certificados', 'tipo_certificado_id')
                    ->where(fn ($query) => $query->where('colaborador_id', $colaboradorId)->whereNull('deleted_at'))
                    ->ignore($certificado?->id),
            ],
            'fecha_emision' => ['required', 'date', 'before:fecha_vencimiento'],
            'fecha_vencimiento' => ['required', 'date', 'after:fecha_emision'],
            'documento_adjunto' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }
}
