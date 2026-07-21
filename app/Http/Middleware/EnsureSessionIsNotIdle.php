<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsNotIdle
{
    private const MINUTOS_INACTIVIDAD = 20;

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario) {
            $ultimaActividad = $request->session()->get('ultima_actividad');

            if ($ultimaActividad && now()->diffInMinutes($ultimaActividad, absolute: true) >= self::MINUTOS_INACTIVIDAD) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('status', 'Tu sesión se cerró por inactividad.');
            }

            $request->session()->put('ultima_actividad', now());
        }

        return $next($request);
    }
}
