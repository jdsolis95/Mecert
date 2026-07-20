<?php

namespace App\Http\Controllers;

use App\Models\Acceso;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccesoController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = $request->input('usuario_id');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $accesos = Acceso::query()
            ->with('usuario:id,name,primer_apellido')
            ->when($usuarioId, fn ($query) => $query->where('usuario_id', $usuarioId))
            ->when($desde, fn ($query) => $query->whereDate('fecha_ingreso', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('fecha_ingreso', '<=', $hasta))
            ->latest('fecha_ingreso')
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
            ],
            'usuarios' => User::orderBy('primer_apellido')
                ->get(['id', 'name', 'primer_apellido'])
                ->map(fn (User $usuario) => [
                    'id' => $usuario->id,
                    'nombre' => trim($usuario->name . ' ' . $usuario->primer_apellido),
                ]),
        ]);
    }
}
