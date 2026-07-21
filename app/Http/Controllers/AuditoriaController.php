<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Certificado;
use App\Models\Mentoria;
use App\Models\User;
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
        ]);
    }

    private function nombreModulo(string $auditableType): string
    {
        return class_basename($auditableType);
    }
}
