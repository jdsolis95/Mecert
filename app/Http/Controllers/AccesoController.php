<?php

namespace App\Http\Controllers;

use App\Models\Acceso;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccesoController extends Controller
{
    // Columnas ordenables desde los encabezados de la tabla (clave del query param 'orden' => columna SQL real).
    private const COLUMNAS_ORDEN = [
        'usuario' => 'users.primer_apellido',
        'fecha_ingreso' => 'accesos.fecha_ingreso',
        'fecha_salida' => 'accesos.fecha_salida',
    ];

    public function index(Request $request)
    {
        $usuarioId = $request->input('usuario_id');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $orden = $request->input('orden', 'fecha_ingreso');
        $direccion = $request->input('direccion') === 'asc' ? 'asc' : 'desc';
        $columnaOrden = self::COLUMNAS_ORDEN[$orden] ?? self::COLUMNAS_ORDEN['fecha_ingreso'];

        $accesos = Acceso::query()
            ->leftJoin('users', 'accesos.usuario_id', '=', 'users.id')
            ->select('accesos.*')
            ->with('usuario:id,name,primer_apellido')
            ->when($usuarioId, fn ($query) => $query->where('usuario_id', $usuarioId))
            ->when($desde, fn ($query) => $query->whereDate('accesos.fecha_ingreso', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('accesos.fecha_ingreso', '<=', $hasta))
            ->orderBy($columnaOrden, $direccion)
            ->paginate(50)
            ->withQueryString()
            ->through(fn (Acceso $acceso) => [
                'id' => $acceso->id,
                'usuario' => trim($acceso->usuario->name . ' ' . $acceso->usuario->primer_apellido),
                'fecha_ingreso' => $acceso->fecha_ingreso->format('d/m/Y H:i:s'),
                'fecha_salida' => $acceso->fecha_salida?->format('d/m/Y H:i:s'),
            ]);

        return Inertia::render('BitacoraAccesos/Index', [
            'accesos' => $accesos,
            'filtros' => [
                'usuario_id' => $usuarioId ?? '',
                'desde' => $desde ?? '',
                'hasta' => $hasta ?? '',
                'orden' => $orden,
                'direccion' => $direccion,
            ],
            'usuarios' => User::orderBy('primer_apellido')
                ->get(['id', 'name', 'primer_apellido'])
                ->map(fn (User $usuario) => [
                    'id' => $usuario->id,
                    'nombre' => trim($usuario->name . ' ' . $usuario->primer_apellido),
                ]),
            'puedeGenerarReporte' => $request->user()->hasAnyRole(['Administrador', 'Controller']),
        ]);
    }

    public function reportePdf(Request $request)
    {
        $usuarioId = $request->input('usuario_id');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $accesos = Acceso::query()
            ->with('usuario:id,name,primer_apellido,segundo_apellido,cedula')
            ->when($usuarioId, fn ($q) => $q->where('usuario_id', $usuarioId))
            ->when($desde, fn ($q) => $q->whereDate('fecha_ingreso', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_ingreso', '<=', $hasta))
            ->orderByDesc('fecha_ingreso')
            ->get();

        $filas = $accesos->map(fn (Acceso $acceso) => [
            'usuario' => trim($acceso->usuario->name . ' ' . $acceso->usuario->primer_apellido . ' ' . $acceso->usuario->segundo_apellido),
            'cedula' => $acceso->usuario->cedula,
            'fecha_ingreso' => $acceso->fecha_ingreso->format('d/m/Y H:i:s'),
            'fecha_salida' => $acceso->fecha_salida?->format('d/m/Y H:i:s') ?? 'Sesión activa',
            'ip' => $acceso->ip_ingreso,
        ]);

        $totales = [
            'total' => $accesos->count(),
            'cerradas' => $accesos->whereNotNull('fecha_salida')->count(),
            'activas' => $accesos->whereNull('fecha_salida')->count(),
        ];

        $partesFiltro = [];
        if ($desde) $partesFiltro[] = 'Desde: ' . Carbon::parse($desde)->format('d/m/Y');
        if ($hasta) $partesFiltro[] = 'Hasta: ' . Carbon::parse($hasta)->format('d/m/Y');
        if ($usuarioId && $usuario = User::find($usuarioId)) $partesFiltro[] = 'Usuario: ' . trim($usuario->name . ' ' . $usuario->primer_apellido);

        $pdf = Pdf::loadView('reportes.bitacora-accesos', [
            'titulo' => 'Reporte de Bitácora de Accesos',
            'filtrosTexto' => $partesFiltro ? implode(' | ', $partesFiltro) : 'Sin filtros aplicados',
            'generadoPor' => trim($request->user()->name . ' ' . $request->user()->primer_apellido),
            'filas' => $filas,
            'totales' => $totales,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('reporte-bitacora-accesos.pdf');
    }
}
