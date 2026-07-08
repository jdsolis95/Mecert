<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisosModuloSeeder extends Seeder
{
    // Réplica del comportamiento actual (hardcodeado) de AppLayout.vue,
    // para que ningún rol base pierda acceso al pasar el menú a permisos.
    private const PERMISOS_POR_ROL_BASE = [
        'Administrador' => [
            'modulo.certificaciones', 'modulo.mentorias',
            'modulo.reportes', 'modulo.acerca', 'modulo.ayuda',
        ],
        'Controller' => [
            'modulo.certificaciones', 'modulo.mentorias',
            'modulo.reportes', 'modulo.acerca', 'modulo.ayuda',
        ],
        'Colaborador' => [
            'modulo.certificaciones', 'modulo.mentorias',
            'modulo.acerca', 'modulo.ayuda',
        ],
        'Comercial' => [
            'modulo.certificaciones', 'modulo.reportes',
            'modulo.acerca', 'modulo.ayuda',
        ],
    ];

    public function run(): void
    {
        foreach (array_keys(config('modulos.permisos')) as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        foreach (self::PERMISOS_POR_ROL_BASE as $roleName => $permisos) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $role->givePermissionTo($permisos);
            }
        }
    }
}
