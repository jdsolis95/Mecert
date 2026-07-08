<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Administrador', 'Controller', 'Colaborador', 'Comercial'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $this->call(PermisosModuloSeeder::class);

        $adminEmail = Str::lower(env('ADMIN_EMAIL', 'administrator@datacr.com'));
        $adminCedula = env('ADMIN_CEDULA', '000000000');

        $admin = User::where('email', $adminEmail)
            ->orWhere('cedula', $adminCedula)
            ->first();

        if (! $admin) {
            $adminPassword = env('ADMIN_PASSWORD') ?: Str::password(20, letters: true, numbers: true, symbols: true, spaces: false);

            $admin = User::create([
                'cedula' => $adminCedula,
                'name' => 'Administrator',
                'primer_apellido' => 'Admin',
                'segundo_apellido' => 'Admin',
                'email' => $adminEmail,
                'password' => $adminPassword,
                'esta_activo' => true,
                'must_change_password' => true,
            ]);

            if (! env('ADMIN_PASSWORD') && $this->command) {
                $this->command->warn('Password temporal del administrador inicial:');
                $this->command->line($adminPassword);
            }
        }

        $admin->forceFill([
            'email' => $adminEmail,
            'esta_activo' => true,
        ])->save();

        $admin->syncRoles(['Administrador']);

        // Para resetear acceso usa: php artisan admin:ensure --reset-password
    }
}
