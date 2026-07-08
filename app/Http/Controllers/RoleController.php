<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // Lista todos los roles con su cantidad de usuarios y módulos habilitados
    public function index()
    {
        $roles = Role::withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'nombre' => $role->name,
                'es_rol_base' => in_array($role->name, config('modulos.roles_base'), true),
                'cantidad_usuarios' => $role->users_count,
                'modulos' => $role->permissions->pluck('name')
                    ->map(fn ($permiso) => config("modulos.permisos.$permiso", $permiso))
                    ->values(),
            ]);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    // Formulario para crear un nuevo rol
    public function create()
    {
        return Inertia::render('Roles/Form', [
            'rol' => null,
            'modulos' => config('modulos.permisos'),
            'permisosAsignados' => [],
        ]);
    }

    // Almacena un nuevo rol con sus permisos por módulo
    public function store(Request $request)
    {
        $data = $this->rolRules($request);

        $role = Role::create(['name' => $data['nombre'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permisos']);

        return redirect()->route('roles.index')
            ->with('mensaje', 'Rol creado correctamente.');
    }

    // Formulario para editar un rol existente
    public function edit(Role $role)
    {
        return Inertia::render('Roles/Form', [
            'rol' => [
                'id' => $role->id,
                'nombre' => $role->name,
                'es_rol_base' => in_array($role->name, config('modulos.roles_base'), true),
            ],
            'modulos' => config('modulos.permisos'),
            'permisosAsignados' => $role->permissions->pluck('name'),
        ]);
    }

    // Actualiza el nombre (si no es rol base) y los permisos del rol
    public function update(Request $request, Role $role)
    {
        $esRolBase = in_array($role->name, config('modulos.roles_base'), true);

        $data = $this->rolRules($request, $role, $esRolBase);

        if (! $esRolBase) {
            $role->update(['name' => $data['nombre']]);
        }

        $role->syncPermissions($data['permisos']);

        return redirect()->route('roles.index')
            ->with('mensaje', 'Rol actualizado correctamente.');
    }

    // Elimina un rol, siempre que no sea uno de los roles base ni tenga usuarios asignados
    public function destroy(Role $role)
    {
        abort_if(
            in_array($role->name, config('modulos.roles_base'), true),
            403,
            'No se pueden eliminar los roles base del sistema.'
        );

        abort_if(
            $role->users()->exists(),
            422,
            'No se puede eliminar un rol que tiene usuarios asignados.'
        );

        $role->delete();

        return redirect()->route('roles.index')
            ->with('mensaje', 'Rol eliminado correctamente.');
    }

    private function rolRules(Request $request, ?Role $role = null, bool $esRolBase = false): array
    {
        $rules = [
            'permisos' => ['array'],
            'permisos.*' => [Rule::exists('permissions', 'name')],
        ];

        if (! $esRolBase) {
            $rules['nombre'] = [
                'required', 'string', 'max:50',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ];
        }

        $validated = $request->validate($rules);

        return [
            'nombre' => $validated['nombre'] ?? $role?->name,
            'permisos' => $validated['permisos'] ?? [],
        ];
    }
}
