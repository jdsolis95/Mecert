<?php

namespace App\Policies;

use App\Models\Certificado;
use App\Models\User;

class CertificadoPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller', 'Colaborador']);
    }

    public function view(User $user, Certificado $certificado): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller', 'Comercial'])
            || $user->id === $certificado->colaborador_id;
    }

    public function update(User $user, Certificado $certificado): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller'])
            || $user->id === $certificado->colaborador_id;
    }

    public function delete(User $user, Certificado $certificado): bool
    {
        return $this->update($user, $certificado);
    }

    public function proponerExamen(User $user, Certificado $certificado): bool
    {
        return $this->update($user, $certificado);
    }

    public function aprobarExamen(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller']);
    }
}
