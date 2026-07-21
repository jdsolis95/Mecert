<?php

namespace App\Http\Controllers;

use App\Models\TipoCertificacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TipoCertificacionController extends Controller
{
    // Lista los tipos de certificacion con conteo de certificados que los usan y busqueda por nombre
    public function index(Request $request)
    {
        $q = $request->input('q');

        $tipos = TipoCertificacion::withCount('certificados')
            ->when($q, fn ($query) => $query->where('nombre', 'like', "%{$q}%"))
            ->orderBy('nombre')
            ->get()
            ->map(fn (TipoCertificacion $tipo) => [
                'id' => $tipo->id,
                'nombre' => $tipo->nombre,
                'activo' => $tipo->activo,
                'en_uso' => $tipo->certificados_count > 0,
            ]);

        return Inertia::render('TiposCertificacion/Index', [
            'tipos' => $tipos,
            'filtros' => [
                'q' => $q ?? '',
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('TiposCertificacion/Form', [
            'tipo' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->reglas());

        TipoCertificacion::create($data + ['activo' => true]);

        return redirect()->route('tipos-certificacion.index')
            ->with('mensaje', 'Tipo de certificación creado correctamente.');
    }

    public function edit(TipoCertificacion $tipo_certificacion)
    {
        return Inertia::render('TiposCertificacion/Form', [
            'tipo' => $tipo_certificacion->only('id', 'nombre', 'activo'),
        ]);
    }

    public function update(Request $request, TipoCertificacion $tipo_certificacion)
    {
        $data = $request->validate($this->reglas($tipo_certificacion));

        $tipo_certificacion->update($data);

        return redirect()->route('tipos-certificacion.index')
            ->with('mensaje', 'Tipo de certificación actualizado correctamente.');
    }

    // Elimina el tipo solo si ningun certificado lo esta usando actualmente
    public function destroy(TipoCertificacion $tipo_certificacion)
    {
        abort_if(
            $tipo_certificacion->enUso(),
            422,
            'No se puede eliminar un tipo de certificación en uso; deshabilítelo en su lugar.'
        );

        $tipo_certificacion->delete();

        return redirect()->route('tipos-certificacion.index')
            ->with('mensaje', 'Tipo de certificación eliminado correctamente.');
    }

    // Alterna activo/inactivo para ocultarlo de nuevos formularios sin romper el historial
    public function alternar(TipoCertificacion $tipo_certificacion)
    {
        $tipo_certificacion->update(['activo' => ! $tipo_certificacion->activo]);

        return back()->with('mensaje', $tipo_certificacion->activo ? 'Tipo habilitado.' : 'Tipo deshabilitado.');
    }

    private function reglas(?TipoCertificacion $tipo = null): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:150',
                Rule::unique('tipos_certificacion', 'nombre')->ignore($tipo?->id),
            ],
        ];
    }
}
