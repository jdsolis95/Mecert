<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AyudaController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\MentoriaController;
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

    Route::resource('mentorias', MentoriaController::class)
        ->middleware('permission:modulo.mentorias');

    Route::resource('certificados', CertificadoController::class)
        ->middleware('permission:modulo.certificaciones');

    Route::post('/certificados/{certificado}/examenes', [CertificadoController::class, 'proponerExamen'])
        ->middleware('permission:modulo.certificaciones')
        ->name('certificados.examenes.store');

    Route::get('/certificados-examenes', [CertificadoController::class, 'examenesPendientes'])
        ->middleware('role:Administrador|Controller')
        ->name('certificados.examenes.index');

    Route::patch('/certificados-examenes/{examen}', [CertificadoController::class, 'decidirExamen'])
        ->middleware('role:Administrador|Controller')
        ->name('certificados.examenes.decidir');

    Route::get('/bitacoras', [AuditoriaController::class, 'index'])
        ->middleware('permission:modulo.bitacoras')
        ->name('bitacoras.index');

    Route::get('/bitacora-accesos', [AccesoController::class, 'index'])
        ->middleware('permission:modulo.bitacoras')
        ->name('bitacora-accesos.index');

    Route::get('/acerca-de', fn() => Inertia::render('Acercade'))->name('acerca-de');

    Route::get('/ayuda', [AyudaController::class, 'index'])->name('ayuda');
    Route::get('/ayuda/manual', [AyudaController::class, 'manual'])->name('ayuda.manual');
    Route::post('/ayuda/manual', [AyudaController::class, 'subirManual'])
        ->middleware('role:Administrador')
        ->name('ayuda.manual.subir');
});

require __DIR__.'/auth.php';
