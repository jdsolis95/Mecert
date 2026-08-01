<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Certificado;
use App\Models\Mentoria;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditoriaController extends Controller
{
    // Solo Mentoria y Certificado registran auditoria por ahora (via el trait Auditable).
    private const MODULOS = [
        'Mentoria' => Mentoria::class,
        'Certificado' => Certificado::class,
    ];

    // Columnas ordenables desde los encabezados de la tabla (clave del query param 'orden' => columna SQL real).
    private const COLUMNAS_ORDEN = [
        'fecha' => 'auditorias.created_at',
        'modulo' => 'auditorias.auditable_type',
        'accion' => 'auditorias.accion',
        'usuario' => 'users.primer_apellido',
    ];

    // Bitácora de movimientos (creado/modificado/eliminado) con filtros y orden configurable por columna
    public function index(Request $request)
    {
        $accion = $request->input('accion');
        $modulo = $request->input('modulo');
        $usuarioId = $request->input('usuario_id');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $orden = $request->input('orden', 'fecha');
        $direccion = $request->input('direccion') === 'asc' ? 'asc' : 'desc';
        $columnaOrden = self::COLUMNAS_ORDEN[$orden] ?? self::COLUMNAS_ORDEN['fecha'];

        $auditorias = Auditoria::query()
            ->leftJoin('users', 'auditorias.usuario_id', '=', 'users.id')
            ->select('auditorias.*')
            ->with('usuario:id,name,primer_apellido')
            ->when($accion, fn ($query) => $query->where('accion', $accion))
            ->when($modulo && isset(self::MODULOS[$modulo]), fn ($query) => $query->where('auditable_type', self::MODULOS[$modulo]))
            ->when($usuarioId, fn ($query) => $query->where('usuario_id', $usuarioId))
            ->when($desde, fn ($query) => $query->whereDate('auditorias.created_at', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('auditorias.created_at', '<=', $hasta))
            ->orderBy($columnaOrden, $direccion)
            ->paginate(50)
            ->withQueryString()
            ->through(fn (Auditoria $auditoria) => [
                'id' => $auditoria->id,
                'modulo' => $this->nombreModulo($auditoria->auditable_type),
                'accion' => $auditoria->accion,
                'usuario' => $auditoria->usuario
                    ? trim($auditoria->usuario->name . ' ' . $auditoria->usuario->primer_apellido)
                    : 'Usuario eliminado',
                'fecha' => $auditoria->created_at->format('d/m/Y H:i'),
                'datos_anteriores' => $auditoria->datos_anteriores,
                'datos_nuevos' => $auditoria->datos_nuevos,
            ]);

        return Inertia::render('Bitacoras/Index', [
            'auditorias' => $auditorias,
            'filtros' => [
                'accion' => $accion ?? '',
                'modulo' => $modulo ?? '',
                'usuario_id' => $usuarioId ?? '',
                'desde' => $desde ?? '',
                'hasta' => $hasta ?? '',
                'orden' => $orden,
                'direccion' => $direccion,
            ],
            'modulos' => array_keys(self::MODULOS),
            'usuarios' => User::orderBy('primer_apellido')
                ->get(['id', 'name', 'primer_apellido'])
                ->map(fn (User $usuario) => [
                    'id' => $usuario->id,
                    'nombre' => trim($usuario->name . ' ' . $usuario->primer_apellido),
                ]),
            'puedeGenerarReporte' => $request->user()->hasAnyRole(['Administrador', 'Controller']),
        ]);
    }

    // Arma el PDF de la bitácora de movimientos con los mismos filtros del listado
    public function reportePdf(Request $request)
    {
        $accion = $request->input('accion');
        $modulo = $request->input('modulo');
        $usuarioId = $request->input('usuario_id');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $auditorias = Auditoria::query()
            ->with('usuario:id,name,primer_apellido')
            ->when($accion, fn ($q) => $q->where('accion', $accion))
            ->when($modulo && isset(self::MODULOS[$modulo]), fn ($q) => $q->where('auditable_type', self::MODULOS[$modulo]))
            ->when($usuarioId, fn ($q) => $q->where('usuario_id', $usuarioId))
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->orderByDesc('created_at')
            ->get();

        $filas = $auditorias->map(fn (Auditoria $auditoria) => [
            'fecha' => $auditoria->created_at->format('d/m/Y H:i'),
            'modulo' => $this->nombreModulo($auditoria->auditable_type),
            'accion' => ucfirst($auditoria->accion),
            'usuario' => $auditoria->usuario
                ? trim($auditoria->usuario->name . ' ' . $auditoria->usuario->primer_apellido)
                : 'Usuario eliminado',
        ]);

        $totales = [
            'total' => $auditorias->count(),
            'creados' => $auditorias->where('accion', 'creado')->count(),
            'modificados' => $auditorias->where('accion', 'modificado')->count(),
            'eliminados' => $auditorias->where('accion', 'eliminado')->count(),
        ];

        $partesFiltro = [];
        if ($desde) $partesFiltro[] = 'Desde: ' . Carbon::parse($desde)->format('d/m/Y');
        if ($hasta) $partesFiltro[] = 'Hasta: ' . Carbon::parse($hasta)->format('d/m/Y');
        if ($accion) $partesFiltro[] = 'Acción: ' . ucfirst($accion);
        if ($modulo && isset(self::MODULOS[$modulo])) $partesFiltro[] = 'Módulo: ' . $modulo;
        if ($usuarioId && $usuario = User::find($usuarioId)) $partesFiltro[] = 'Usuario: ' . trim($usuario->name . ' ' . $usuario->primer_apellido);

        $pdf = Pdf::loadView('reportes.bitacoras-movimientos', [
            'titulo' => 'Reporte de Bitácora de Movimientos',
            'filtrosTexto' => $partesFiltro ? implode(' | ', $partesFiltro) : 'Sin filtros aplicados',
            'generadoPor' => trim($request->user()->name . ' ' . $request->user()->primer_apellido),
            'filas' => $filas,
            'totales' => $totales,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('reporte-bitacora-movimientos.pdf');
    }

    // El auditable_type guarda el FQCN del modelo; para mostrar solo se necesita el nombre corto
    private function nombreModulo(string $auditableType): string
    {
        return class_basename($auditableType);
    }
}
