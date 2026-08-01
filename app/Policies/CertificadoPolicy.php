<?php

namespace App\Policies;

use App\Models\Certificado;
use App\Models\User;

class CertificadoPolicy
{
    // Cualquier colaborador puede dar de alta su propio certificado
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller', 'Colaborador']);
    }

    // Ve el certificado su dueño, o quien gestiona/consulta (admin, controller, comercial)
    public function view(User $user, Certificado $certificado): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller', 'Comercial'])
            || $user->id === $certificado->colaborador_id;
    }

    // Edita el certificado su dueño, o admin/controller
    public function update(User $user, Certificado $certificado): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller'])
            || $user->id === $certificado->colaborador_id;
    }

    // Solo admin/controller pueden eliminar, ni siquiera el colaborador dueño del certificado
    public function delete(User $user, Certificado $certificado): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller']);
    }

    // Calendarizar el examen usa las mismas reglas que editar el certificado
    public function proponerExamen(User $user, Certificado $certificado): bool
    {
        return $this->update($user, $certificado);
    }

    // Solo admin/controller deciden sobre los exámenes propuestos
    public function aprobarExamen(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Controller']);
    }
}
