<?php

namespace App\Http\Controllers;

use App\Mail\CertificadoExamenDecidido;
use App\Mail\CertificadoExamenPropuesto;
use App\Mail\CertificadoPorVencer;
use App\Models\Certificado;
use App\Models\CertificadoExamen;
use App\Models\TipoCertificacion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
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
            'puedeGenerarReporte' => $user->hasAnyRole(['Administrador', 'Controller']),
            'tiposCertificacionFiltro' => $user->hasAnyRole(['Administrador', 'Controller'])
                ? TipoCertificacion::orderBy('nombre')->get(['id', 'nombre'])
                : [],
            'colaboradoresFiltro' => $user->hasAnyRole(['Administrador', 'Controller'])
                ? $this->colaboradoresParaSelect()
                : [],
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
                'nombre_certificado' => $request->nombre_certificado,
                'emisor' => $request->emisor,
                'codigo_certificado' => $request->codigo_certificado,
                'fecha_emision' => $request->fecha_emision,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                ...$this->resolverDocumento($request),
            ]);

            if ($certificado->documento_path) {
                $certificado->registrarVersion('documento', $certificado->documento_path, $certificado->documento_nombre_original, $request->user()->id);
            }

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
            'versionesArchivo.subidoPor:id,name,primer_apellido',
        ]);

        $user = $request->user();

        return Inertia::render('Certificados/Show', [
            'certificado' => [
                'id' => $certificado->id,
                'colaborador' => trim($certificado->colaborador->name . ' ' . $certificado->colaborador->primer_apellido),
                'tipo_certificado' => $certificado->tipoCertificacion->nombre,
                'nombre_certificado' => $certificado->nombre_certificado,
                'emisor' => $certificado->emisor,
                'codigo_certificado' => $certificado->codigo_certificado,
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
            'versionesDocumento' => $certificado->versionesArchivo->map(fn (\App\Models\ArchivoVersion $version) => [
                'id' => $version->id,
                'nombre_original' => $version->nombre_original,
                'url' => Storage::url($version->path),
                'subido_por' => $version->subidoPor ? trim($version->subidoPor->name . ' ' . $version->subidoPor->primer_apellido) : null,
                'fecha' => $version->created_at->format('d/m/Y H:i'),
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
                'nombre_certificado' => $certificado->nombre_certificado,
                'emisor' => $certificado->emisor,
                'codigo_certificado' => $certificado->codigo_certificado,
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

            $documentoNuevo = $this->resolverDocumento($request, $certificado);

            $certificado->update([
                'colaborador_id' => $colaboradorId,
                'tipo_certificado_id' => $request->tipo_certificado_id,
                'nombre_certificado' => $request->nombre_certificado,
                'emisor' => $request->emisor,
                'codigo_certificado' => $request->codigo_certificado,
                'fecha_emision' => $request->fecha_emision,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                // Si cambió la fecha de vencimiento, se reabre la ventana de avisos.
                'notificado_amarillo_en' => null,
                'notificado_rojo_en' => null,
                ...$documentoNuevo,
            ]);

            if (! empty($documentoNuevo)) {
                $certificado->registrarVersion('documento', $documentoNuevo['documento_path'], $documentoNuevo['documento_nombre_original'], $request->user()->id);
            }

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

        $examen = $certificado->examenes()->create([
            'fecha_propuesta' => $request->fecha_propuesta,
            'lugar_propuesto' => $request->lugar_propuesto,
            'propuesto_por_id' => $request->user()->id,
            'estado' => 'pendiente',
        ]);

        $controllers = User::role('Controller')->pluck('email')->all();
        Mail::to($certificado->colaborador->email)->cc($controllers)->send(new CertificadoExamenPropuesto($examen));

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
            // Reabre la ventana del recordatorio si se (re)aprueba con una nueva fecha de examen.
            'notificado_recordatorio_en' => null,
        ]);

        $controllers = User::role('Controller')->pluck('email')->all();
        Mail::to($examen->certificado->colaborador->email)->cc($controllers)->send(new CertificadoExamenDecidido($examen));

        return back()->with('mensaje', $request->accion === 'aprobar' ? 'Examen aprobado.' : 'Examen rechazado.');
    }

    public function reportePdf(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $estadoFiltro = $request->input('estado');
        $tipoCertificadoId = $request->input('tipo_certificado_id');
        $colaboradorId = $request->input('colaborador_id');

        $hoy = Carbon::today();
        $umbralAmarillo = $hoy->copy()->addMonths(config('certificados.meses_alerta', 3));
        $etiquetaEstado = ['verde' => 'Vigente', 'amarillo' => 'Por vencer', 'rojo' => 'Vencido'];

        $certificados = Certificado::query()
            ->with(['colaborador:id,name,primer_apellido,segundo_apellido,cedula', 'tipoCertificacion:id,nombre'])
            ->when($desde, fn ($q) => $q->whereDate('fecha_emision', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_emision', '<=', $hasta))
            ->when($tipoCertificadoId, fn ($q) => $q->where('tipo_certificado_id', $tipoCertificadoId))
            ->when($colaboradorId, fn ($q) => $q->where('colaborador_id', $colaboradorId))
            ->when($estadoFiltro === 'rojo', fn ($q) => $q->whereDate('fecha_vencimiento', '<=', $hoy))
            ->when($estadoFiltro === 'amarillo', fn ($q) => $q->whereDate('fecha_vencimiento', '>', $hoy)->whereDate('fecha_vencimiento', '<=', $umbralAmarillo))
            ->when($estadoFiltro === 'verde', fn ($q) => $q->whereDate('fecha_vencimiento', '>', $umbralAmarillo))
            ->orderBy('fecha_vencimiento')
            ->get();

        $filas = $certificados->map(fn (Certificado $certificado) => [
            'cedula' => $certificado->colaborador->cedula,
            'colaborador' => trim($certificado->colaborador->name . ' ' . $certificado->colaborador->primer_apellido . ' ' . $certificado->colaborador->segundo_apellido),
            'tipo_certificado' => $certificado->tipoCertificacion->nombre,
            'fecha_emision' => $certificado->fecha_emision->format('d/m/Y'),
            'fecha_vencimiento' => $certificado->fecha_vencimiento->format('d/m/Y'),
            'estado' => $etiquetaEstado[$certificado->estado()],
        ]);

        $totales = [
            'total' => $certificados->count(),
            'vigentes' => $certificados->filter(fn (Certificado $c) => $c->estado() === 'verde')->count(),
            'por_vencer' => $certificados->filter(fn (Certificado $c) => $c->estado() === 'amarillo')->count(),
            'vencidos' => $certificados->filter(fn (Certificado $c) => $c->estado() === 'rojo')->count(),
        ];

        $partesFiltro = [];
        if ($desde) $partesFiltro[] = 'Desde: ' . Carbon::parse($desde)->format('d/m/Y');
        if ($hasta) $partesFiltro[] = 'Hasta: ' . Carbon::parse($hasta)->format('d/m/Y');
        if ($estadoFiltro && isset($etiquetaEstado[$estadoFiltro])) $partesFiltro[] = 'Estado: ' . $etiquetaEstado[$estadoFiltro];
        if ($tipoCertificadoId && $tipo = TipoCertificacion::find($tipoCertificadoId)) $partesFiltro[] = 'Tipo: ' . $tipo->nombre;
        if ($colaboradorId && $colaborador = User::find($colaboradorId)) $partesFiltro[] = 'Colaborador: ' . trim($colaborador->name . ' ' . $colaborador->primer_apellido);

        $pdf = Pdf::loadView('reportes.certificados', [
            'titulo' => 'Reporte de Certificados',
            'filtrosTexto' => $partesFiltro ? implode(' | ', $partesFiltro) : 'Sin filtros aplicados',
            'generadoPor' => trim($request->user()->name . ' ' . $request->user()->primer_apellido),
            'filas' => $filas,
            'totales' => $totales,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('reporte-certificados.pdf');
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

    // No borra el documento anterior: cada carga queda como una versión nueva en el historial
    // (ver Certificado::registrarVersion), para que el repositorio quede versionado y no se pierda el archivo previo.
    private function resolverDocumento(Request $request, ?Certificado $existente = null): array
    {
        if ($request->hasFile('documento_adjunto')) {
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
            'nombre_certificado' => $certificado->nombre_certificado,
            'codigo_certificado' => $certificado->codigo_certificado,
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
            'nombre_certificado' => ['required', 'string', 'max:150'],
            'emisor' => ['required', 'string', 'max:150'],
            'codigo_certificado' => ['required', 'string', 'max:100'],
            'fecha_emision' => ['required', 'date', 'before:fecha_vencimiento'],
            'fecha_vencimiento' => ['required', 'date', 'after:fecha_emision'],
            'documento_adjunto' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }
}
