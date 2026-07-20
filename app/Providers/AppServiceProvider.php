<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Listeners\RegistrarIngreso;
use App\Listeners\RegistrarSalida;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Bitacora de ingresos y salidas al sistema
        Event::listen(Login::class, RegistrarIngreso::class);
        Event::listen(Logout::class, RegistrarSalida::class);

        // Register a small artisan helper to force the must_change_password flag
        Artisan::command('user:force-change {email?} {--all}', function ($email = null) {
            $all = $this->option('all');

            if ($all) {
                $count = User::query()->update(['must_change_password' => true]);
                $this->info("Updated must_change_password = true for {$count} users.");
                return 0;
            }

            if (! $email) {
                $this->error('Provide an email or use --all');
                return 1;
            }

            $user = User::where('email', $email)->first();
            if (! $user) {
                $this->error('User not found: ' . $email);
                return 1;
            }

            $user->must_change_password = true;
            $user->save();

            $this->info('Flag set for ' . $email);
            return 0;
        })->describe('Force must_change_password for a user or all users');
    }
}
