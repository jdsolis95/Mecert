<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AyudaController extends Controller
{
    private const RUTA_MANUAL = 'manual/manual_mecert.pdf';

    public function index(Request $request)
    {
        return Inertia::render('Ayuda', [
            'manualUrl' => route('ayuda.manual'),
            'manualExiste' => Storage::disk('public')->exists(self::RUTA_MANUAL),
            'puedeAdministrar' => $request->user()->hasRole('Administrador'),
        ]);
    }

    public function manual()
    {
        abort_unless(Storage::disk('public')->exists(self::RUTA_MANUAL), 404, 'Manual no encontrado.');

        return response()->file(Storage::disk('public')->path(self::RUTA_MANUAL), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="manual_mecert.pdf"',
        ]);
    }

    public function subirManual(Request $request)
    {
        $request->validate([
            'manual' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $request->file('manual')->storeAs('manual', 'manual_mecert.pdf', 'public');

        return redirect()->route('ayuda')->with('mensaje', 'Manual actualizado correctamente.');
    }
}
