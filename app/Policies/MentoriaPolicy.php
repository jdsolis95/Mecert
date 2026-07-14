<?php

namespace App\Policies;

use App\Models\Mentoria;
use App\Models\User;

class MentoriaPolicy
{
    public function update(User $user, Mentoria $mentoria): bool
    {
        return $user->id === $mentoria->autor_id || $user->hasAnyRole(['Administrador', 'Controller']);
    }

    public function delete(User $user, Mentoria $mentoria): bool
    {
        return $this->update($user, $mentoria);
    }
}
