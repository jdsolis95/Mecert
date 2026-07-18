<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    //Despliega la vista del password reset
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     *captura las solicitudes de password resets
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        // hace la logica, si la contraseña es exitosa la escribe en la base de datos, sino, da mensaje de error
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                if ($user->passwordMatchesCurrentOrRecent($password)) {
                    throw ValidationException::withMessages([
                        'password' => 'La contraseña no puede ser igual a la actual ni a las últimas 2 contraseñas.',
                    ]);
                }

                DB::transaction(function () use ($user, $password): void {
                    $user->archiveCurrentPassword();

                    $user->forceFill([
                        'password' => $password,
                        'must_change_password' => false,
                        'remember_token' => Str::random(60),
                    ])->save();
                });

                event(new PasswordReset($user));
            }
        );
        
        //Si la contraseña es correcta se redirige al dashboard, sino se devualve al user al mnesaje de error.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
