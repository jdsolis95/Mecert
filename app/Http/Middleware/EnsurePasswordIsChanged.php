<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    // Si el usuario tiene pendiente el cambio de contraseña, lo manda a esa pantalla sin importar a dónde iba
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs('password.force.edit')) {
            return redirect()->route('password.force.edit');
        }

        return $next($request);
    }
}