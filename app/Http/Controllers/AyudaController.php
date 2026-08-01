<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AyudaController extends Controller
{
    private const RUTA_MANUAL = 'manual/manual_mecert.pdf';

    // Muestra la pantalla de ayuda con el manual actual, si existe
    public function index(Request $request)
    {
        $manualExiste = Storage::disk('public')->exists(self::RUTA_MANUAL);

        return Inertia::render('Ayuda', [
            // Se agrega la fecha de modificacion como query param para que la URL cambie
            // al subir un manual nuevo (evita que el iframe y la cache del navegador sigan
            // mostrando el PDF anterior, ya que antes la URL era siempre la misma).
            'manualUrl' => $manualExiste
                ? route('ayuda.manual') . '?v=' . Storage::disk('public')->lastModified(self::RUTA_MANUAL)
                : route('ayuda.manual'),
            'manualExiste' => $manualExiste,
            'puedeAdministrar' => $request->user()->hasRole('Administrador'),
        ]);
    }

    // Sirve el PDF del manual embebido en el iframe de la pantalla de ayuda
    public function manual()
    {
        abort_unless(Storage::disk('public')->exists(self::RUTA_MANUAL), 404, 'Manual no encontrado.');

        return response()->file(Storage::disk('public')->path(self::RUTA_MANUAL), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="manual_mecert.pdf"',
        ]);
    }

    // Reemplaza el manual PDF publicado (siempre con el mismo nombre de archivo)
    public function subirManual(Request $request)
    {
        $request->validate([
            'manual' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $request->file('manual')->storeAs('manual', 'manual_mecert.pdf', 'public');

        return redirect()->route('ayuda')->with('mensaje', 'Manual actualizado correctamente.');
    }
}
