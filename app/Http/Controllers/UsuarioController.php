<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordReset;

class UsuarioController extends Controller
{
    //lista de usuarios
    public function index()
    {
        $usuarios = User::with('roles')  // trae el rol de cada usuario
            ->orderBy('primer_apellido')
            ->get()
            ->map(function ($usuario) {
                return [
                    'id'               => $usuario->id,
                    'cedula'           => $usuario->cedula,
                    'nombre_completo'  => $usuario->name . ' ' . $usuario->primer_apellido . ' ' . $usuario->segundo_apellido,
                    'email'            => $usuario->email,
                    'rol'              => $usuario->getRoleNames()->first() ?? 'Sin rol',
                    'esta_activo'      => $usuario->esta_activo,
                ];
            });

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
        ]);
    }

    // Desplegar formulario para crear un nuevo usuario
    public function create()
    {
        $roles = Role::all()->pluck('name'); // lista de roles para el <select>
        return Inertia::render('Usuarios/Create', [
            'roles' => $roles,
        ]);
    }

    // Almacena el nuevo usuario en la base de datos
    public function store(Request $request)
    {
        $request->validate($this->usuarioRules());

        $usuario = User::create([
            'cedula'           => $request->cedula,
            'name'             => $request->name,
            'primer_apellido'  => $request->primer_apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'email'            => $request->email,
            'password'         => $request->password,
            'must_change_password' => true,
            'esta_activo'      => true,
        ]);

        $usuario->assignRole($request->rol);

        return redirect()->route('usuarios.index')
            ->with('mensaje', 'Usuario creado correctamente.');
    }


    // Vista de solo lectura de un usuario
    public function show(User $usuario)
    {
        return Inertia::render('Usuarios/Show', [
            'usuario' => [
                'id'               => $usuario->id,
                'cedula'           => $usuario->cedula,
                'nombre_completo'  => trim($usuario->name . ' ' . $usuario->primer_apellido . ' ' . $usuario->segundo_apellido),
                'email'            => $usuario->email,
                'rol'              => $usuario->getRoleNames()->first() ?? 'Sin rol',
                'esta_activo'      => $usuario->esta_activo,
            ],
            'esPropio' => auth()->id() === $usuario->id,
        ]);
    }


    // Formulario para editar un usuario existente
    public function edit(User $usuario)
    {
        abort_if(auth()->id() === $usuario->id, 403, 'No puedes editar tu propio usuario desde esta pantalla.');

        $roles = Role::all()->pluck('name');

        return Inertia::render('Usuarios/Edit', [
            'usuario' => [
                'id'               => $usuario->id,
                'cedula'           => $usuario->cedula,
                'name'             => $usuario->name,
                'primer_apellido'  => $usuario->primer_apellido,
                'segundo_apellido' => $usuario->segundo_apellido,
                'email'            => $usuario->email,
                'rol'              => $usuario->getRoleNames()->first(),
                'esta_activo'      => $usuario->esta_activo,
            ],
            'roles' => $roles,
        ]);
    }

    // Actualiza Usuarios Existentes
    public function update(Request $request, User $usuario)
    {
        $request->validate($this->usuarioRules($usuario));

        $usuario->update([
            'cedula'           => $request->cedula,
            'name'             => $request->name,
            'primer_apellido'  => $request->primer_apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'email'            => $request->email,
            'esta_activo'      => $request->esta_activo,
        ]);

    // Reemplaza el rol actual del usuario con el nuevo seleccionado
    $usuario->syncRoles([$request->rol]);
    return redirect()->route('usuarios.index')
        ->with('mensaje', 'Usuario actualizado correctamente.');
    }

    // Deshabilitar un usuario en lugar de eliminarlo
    public function destroy(User $usuario)
    {
        abort_if(auth()->id() === $usuario->id, 403, 'No puedes deshabilitar tu propio usuario.');

        $usuario->update(['esta_activo' => ! $usuario->esta_activo]);

        return redirect()->route('usuarios.index')
            ->with('mensaje', $usuario->esta_activo ? 'Usuario habilitado.' : 'Usuario deshabilitado.');
    }

    // Enviar reset de contraseña al usuario
    public function resetPassword(User $usuario)
    {
        abort_if(auth()->id() === $usuario->id, 403, 'No puedes resetear tu propia contraseña desde aquí.');

        $temporaryPassword = Str::random(10);

        $usuario->update([
            'password' => $temporaryPassword,
            'must_change_password' => true,
        ]);

        Mail::to($usuario->email)->send(new PasswordReset($usuario, $temporaryPassword));

        return redirect()->route('usuarios.index')
            ->with('mensaje', "Contraseña temporal enviada a {$usuario->email}");
    }

    private function usuarioRules(?User $usuario = null): array
    {
        $userId = $usuario?->id;

        return [
            'cedula' => [
                'required',
                'digits:9',
                Rule::unique('users', 'cedula')->ignore($userId),
            ],
            'name' => ['required', 'string', 'max:100', 'regex:/^[^0-9]+$/u'],
            'primer_apellido' => ['required', 'string', 'max:100', 'regex:/^[^0-9]+$/u'],
            'segundo_apellido' => ['required', 'string', 'max:100', 'regex:/^[^0-9]+$/u'],
            'email' => [
                'required',
                'email',
                'regex:/^[A-Za-z0-9._%+-]+@datacr\.com$/i',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => $usuario
                ? ['nullable', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed']
                : ['required', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'],
            'rol' => ['required', Rule::exists('roles', 'name')],
            'esta_activo' => ['sometimes', 'boolean'],
        ];
    }
}
