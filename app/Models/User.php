<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cedula',          //** */
        'name',         
        'primer_apellido',  //** */
        'segundo_apellido', //** */
        'email',
        'password',
        'must_change_password',
        'esta_activo', //** */
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'esta_activo' => 'boolean',
        ];
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function mentorias(): HasMany
    {
        return $this->hasMany(Mentoria::class, 'autor_id');
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class, 'colaborador_id');
    }

    // Compara contra la contraseña actual y las últimas N del historial, para no permitir repetirlas
    public function passwordMatchesCurrentOrRecent(string $plainPassword, int $recentHistoryCount = 2): bool
    {
        if (Hash::check($plainPassword, $this->password)) {
            return true;
        }

        $recentHistory = $this->passwordHistories()
            ->latest('id')
            ->limit($recentHistoryCount)
            ->pluck('password');

        foreach ($recentHistory as $historicalPassword) {
            if (Hash::check($plainPassword, $historicalPassword)) {
                return true;
            }
        }

        return false;
    }

    // Guarda la contraseña vigente en el historial y solo conserva las últimas 2
    public function archiveCurrentPassword(): void
    {
        $this->passwordHistories()->create([
            'password' => $this->password,
        ]);

        $allIds = $this->passwordHistories()
            ->latest('id')
            ->pluck('id');

        $idsToDelete = $allIds->slice(2);

        if ($idsToDelete->isNotEmpty()) {
            $this->passwordHistories()->whereIn('id', $idsToDelete)->delete();
        }
    }
}
