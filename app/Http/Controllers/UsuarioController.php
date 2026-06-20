<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

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
        $request->validate([
            'cedula'           => 'required|unique:users|max:20',
            'name'             => 'required|max:100',
            'primer_apellido'  => 'required|max:100',
            'segundo_apellido' => 'nullable|max:100',
            'email'            => 'required|email|unique:users',
            'password'         => 'required|min:8|confirmed', // confirmed busca password_confirmation
            'rol'              => 'required|exists:roles,name',
        ]);

        $usuario = User::create([
            'cedula'           => $request->cedula,
            'name'             => $request->name,
            'primer_apellido'  => $request->primer_apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'email'            => $request->email,
            'password'         => bcrypt($request->password),
            'must_change_password' => true,
            'esta_activo'      => true,
        ]);

        $usuario->assignRole($request->rol);

        return redirect()->route('usuarios.index')
            ->with('mensaje', 'Usuario creado correctamente.');
    }


 // public function show(string $id)
 // {
//  }
     

    // Formulario para editar un usuario existente
    public function edit(User $usuario)
    {
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
        $request->validate([
            'cedula'           => 'required|max:20|unique:users,cedula,' . $usuario->id,
            'name'             => 'required|max:100',
            'primer_apellido'  => 'required|max:100',
            'segundo_apellido' => 'nullable|max:100',
            'email'            => 'required|email|unique:users,email,' . $usuario->id,
            'rol'              => 'required|exists:roles,name',
        ]);

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
        $usuario->update(['esta_activo' => false]);

        return redirect()->route('usuarios.index')
            ->with('mensaje', 'Usuario deshabilitado.');
    }
}
