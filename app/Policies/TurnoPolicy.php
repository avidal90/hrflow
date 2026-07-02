<?php

namespace App\Policies;

use App\Models\Turno;
use App\Models\User;

class TurnoPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->hasAnyRole(['company-admin', 'hr', 'department-manager', 'employee']);
    }

    public function view(User $user, Turno $turno): bool
    {
        return $this->belongsToUsersTenant($user, $turno->tenant_id)
            && $this->canViewTurno($user, $turno);
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    public function update(User $user, Turno $turno): bool
    {
        return $this->belongsToUsersTenant($user, $turno->tenant_id)
            && $user->isCompanyAdmin();
    }

    public function delete(User $user, Turno $turno): bool
    {
        return $this->belongsToUsersTenant($user, $turno->tenant_id)
            && $user->isCompanyAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    public function restore(User $user, Turno $turno): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, Turno $turno): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    private function canViewTurno(User $user, Turno $turno): bool
    {
        if ($user->hasAnyRole(['company-admin', 'hr', 'department-manager'])) {
            return true;
        }

        return $turno->turnoAssignments()
            ->where('user_id', $user->getKey())
            ->exists();
    }

    private function belongsToUsersTenant(User $user, int|string|null $tenantId): bool
    {
        return $user->sharesTenantWithModel($tenantId);
    }
}
