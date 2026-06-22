<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EnsureAdministrator extends Command
{
    protected $signature = 'admin:ensure
        {--email= : Correo del administrador}
        {--cedula= : Cedula del administrador}
        {--password= : Contrasena temporal. Si se omite, se genera una segura}
        {--reset-password : Forzar reset de contrasena del administrador}';

    protected $description = 'Crea o recupera de forma segura el usuario administrador del sistema';

    public function handle(): int
    {
        $email = Str::lower((string) ($this->option('email') ?: env('ADMIN_EMAIL', 'administrator@datacr.com')));
        $cedula = (string) ($this->option('cedula') ?: env('ADMIN_CEDULA', '000000000'));
        $resetPassword = (bool) $this->option('reset-password');
        $providedPassword = $this->option('password');
        $temporaryPassword = $providedPassword ?: Str::password(20, letters: true, numbers: true, symbols: true, spaces: false);
        $passwordWasChanged = false;

        if (! Str::endsWith($email, '@datacr.com')) {
            $this->error('El correo administrador debe pertenecer al dominio @datacr.com.');

            return self::FAILURE;
        }

        $admin = DB::transaction(function () use ($email, $cedula, $resetPassword, $temporaryPassword, &$passwordWasChanged): User {
            Role::firstOrCreate(['name' => 'Administrador']);

            $admin = User::where('email', $email)
                ->orWhere('cedula', $cedula)
                ->first() ?: new User();
            $isNew = ! $admin->exists;

            $admin->forceFill([
                'cedula' => $admin->cedula ?: $cedula,
                'name' => $admin->name ?: 'Administrator',
                'primer_apellido' => $admin->primer_apellido ?: 'Admin',
                'segundo_apellido' => $admin->segundo_apellido ?: 'Admin',
                'email' => $email,
                'esta_activo' => true,
            ]);

            if ($isNew || $resetPassword) {
                if (! $isNew) {
                    $admin->archiveCurrentPassword();
                }

                $admin->forceFill([
                    'password' => $temporaryPassword,
                    'must_change_password' => true,
                    'remember_token' => Str::random(60),
                ]);

                $passwordWasChanged = true;
            }

            $admin->save();
            $admin->syncRoles(['Administrador']);

            return $admin;
        });

        $this->info("Administrador asegurado: {$admin->email}");
        $this->info('Estado: activo, rol Administrador asignado.');

        if ($passwordWasChanged && ! $providedPassword) {
            $this->warn('Contrasena temporal generada:');
            $this->line($temporaryPassword);
            $this->warn('Guardala solo para este ingreso. El usuario debera cambiarla al iniciar sesion.');
        } elseif (! $passwordWasChanged) {
            $this->line('La contrasena existente no fue modificada. Usa --reset-password para regenerarla.');
        }

        return self::SUCCESS;
    }
}
