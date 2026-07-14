<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $accion = $request->input('accion');

        $auditorias = Auditoria::query()
            ->with('usuario:id,name,primer_apellido')
            ->when($accion, fn ($query) => $query->where('accion', $accion))
            ->latest()
            ->paginate(20)
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
            ],
        ]);
    }

    private function nombreModulo(string $auditableType): string
    {
        return class_basename($auditableType);
    }
}
