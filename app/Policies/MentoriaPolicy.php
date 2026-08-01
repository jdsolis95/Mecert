<?php

namespace App\Policies;

use App\Models\Mentoria;
use App\Models\User;

class MentoriaPolicy
{
    // Edita la publicación su autor, o admin/controller
    public function update(User $user, Mentoria $mentoria): bool
    {
        return $user->id === $mentoria->autor_id || $user->hasAnyRole(['Administrador', 'Controller']);
    }

    // Solo admin/controller pueden eliminar, ni siquiera el autor de la publicación
    public function delete(User $user, Mentoria $mentoria): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller']);
    }
}
