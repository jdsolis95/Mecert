<?php

namespace App\Http\Controllers;

use App\Models\Etiqueta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EtiquetaController extends Controller
{
    // Lista las etiquetas con conteo de mentorias que las usan y busqueda por nombre
    public function index(Request $request)
    {
        $q = $request->input('q');

        $etiquetas = Etiqueta::withCount('mentorias')
            ->when($q, fn ($query) => $query->where('nombre', 'like', "%{$q}%"))
            ->orderBy('nombre')
            ->get()
            ->map(fn (Etiqueta $etiqueta) => [
                'id' => $etiqueta->id,
                'nombre' => $etiqueta->nombre,
                'activo' => $etiqueta->activo,
                'en_uso' => $etiqueta->mentorias_count > 0,
            ]);

        return Inertia::render('Etiquetas/Index', [
            'etiquetas' => $etiquetas,
            'filtros' => [
                'q' => $q ?? '',
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Etiquetas/Form', [
            'etiqueta' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->reglas());

        Etiqueta::create($data + ['activo' => true]);

        return redirect()->route('etiquetas.index')
            ->with('mensaje', 'Etiqueta creada correctamente.');
    }

    public function edit(Etiqueta $etiqueta)
    {
        return Inertia::render('Etiquetas/Form', [
            'etiqueta' => $etiqueta->only('id', 'nombre', 'activo'),
        ]);
    }

    public function update(Request $request, Etiqueta $etiqueta)
    {
        $data = $request->validate($this->reglas($etiqueta));

        $etiqueta->update($data);

        return redirect()->route('etiquetas.index')
            ->with('mensaje', 'Etiqueta actualizada correctamente.');
    }

    // Elimina la etiqueta solo si ninguna mentoria la esta usando actualmente
    public function destroy(Etiqueta $etiqueta)
    {
        abort_if(
            $etiqueta->mentorias()->exists(),
            422,
            'No se puede eliminar una etiqueta en uso; deshabilítela en su lugar.'
        );

        $etiqueta->delete();

        return redirect()->route('etiquetas.index')
            ->with('mensaje', 'Etiqueta eliminada correctamente.');
    }

    // Alterna activo/inactivo para ocultarla de nuevos formularios sin romper el historial
    public function alternar(Etiqueta $etiqueta)
    {
        $etiqueta->update(['activo' => ! $etiqueta->activo]);

        return back()->with('mensaje', $etiqueta->activo ? 'Etiqueta habilitada.' : 'Etiqueta deshabilitada.');
    }

    private function reglas(?Etiqueta $etiqueta = null): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:50',
                Rule::unique('etiquetas', 'nombre')->ignore($etiqueta?->id),
            ],
        ];
    }
}
