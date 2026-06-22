<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $mustChangePassword = (bool) $user?->must_change_password;
        $newPassword = (string) $request->input('password');

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        if ($user->passwordMatchesCurrentOrRecent($newPassword)) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña no puede ser igual a la actual ni a las últimas 2 contraseñas.',
            ]);
        }

        DB::transaction(function () use ($user, $validated): void {
            $user->archiveCurrentPassword();

            $user->update([
                'password' => $validated['password'],
                'must_change_password' => false,
            ]);
        });

        if ($mustChangePassword) {
            return to_route('dashboard');
        }

        return back();
    }
}
