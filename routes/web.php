<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'active.user', 'verified', 'must.change.password'])->name('dashboard');

Route::middleware(['auth', 'active.user', 'must.change.password'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('usuarios', UsuarioController::class)
        ->middleware('role:Administrador');

    Route::post('/usuarios/{usuario}/reset-password', [UsuarioController::class, 'resetPassword'])
        ->middleware('role:Administrador')
        ->name('usuarios.reset-password');

    Route::resource('roles', RoleController::class)
        ->except('show')
        ->middleware('role:Administrador');

    Route::get('/acerca-de', fn() => Inertia::render('Acercade'))->name('acerca-de');

    Route::get('/ayuda', fn() => Inertia::render('Ayuda', [
        'manualUrl' => route('ayuda.manual'),
    ]))->name('ayuda');

    Route::get('/ayuda/manual', function () {
        $manuales = glob(storage_path('app/public/manual/*.pdf'));

        abort_if(empty($manuales), 404, 'Manual no encontrado.');

        return response()->file($manuales[0], [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="manual_mecert.pdf"',
        ]);
    })->name('ayuda.manual');
});

require __DIR__.'/auth.php';
