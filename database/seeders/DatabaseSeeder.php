<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Crear los 4 roles del sistema ---
        $roles = ['Administrador', 'Controller', 'Colaborador', 'Comercial'];

        foreach ($roles as $nombre_rol) {
            Role::firstOrCreate(['name' => $nombre_rol]);
        }

        // --- 2. Crear usuario administrador inicial ---
        $admin = User::updateOrCreate(
            ['cedula' => env('ADMIN_CEDULA', '000000000')],
            [
                'name'              => 'Administrator',
                'primer_apellido'   => 'Admin',
                'segundo_apellido'  => 'Admin',
                'email'             => env('ADMIN_EMAIL', 'Administrator@datacr.com'),
                'password'          => env('ADMIN_PASSWORD', 'Administrator'),
                'esta_activo'       => true,
                'must_change_password' => true,
            ]
        );

        // --- 3. Asignar rol Administrador al usuario creado ---
        $admin->syncRoles(['Administrador']);

        // Asegurar que los usuarios creados/actualizados por el seeder requieran cambio de contraseña
        \App\Models\User::where('email', env('ADMIN_EMAIL', 'Administrator@datacr.com'))
            ->update(['must_change_password' => true]);
    }
}
