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
            Role::create(['name' => $nombre_rol]);
        }

        // --- 2. Crear usuario administrador inicial ---
        $admin = User::create([
            'cedula'            => '000000000',
            'name'              => 'Administrator',
            'primer_apellido'   => 'Admin',
            'segundo_apellido'  => 'Admin',
            'email'             => 'Administrator@datacr.com',
            'password'          => bcrypt('Administrator'), // contraseña temporal
            'esta_activo'       => true,
        ]);

        // --- 3. Asignar rol Administrador al usuario creado ---
        $admin->assignRole('Administrador');
    }
}
