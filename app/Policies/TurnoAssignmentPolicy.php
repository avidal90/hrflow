<?php

namespace App\Policies;

use App\Models\TurnoAssignment;
use App\Models\User;

class TurnoAssignmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->hasAnyRole(['company-admin', 'hr', 'department-manager']);
    }

    public function view(User $user, TurnoAssignment $turnoAssignment): bool
    {
        return $this->belongsToUsersTenant($user, $turnoAssignment->tenant_id)
            && (
                $user->hasAnyRole(['company-admin', 'hr', 'department-manager'])
                || $this->isUsersOwnAssignment($user, $turnoAssignment)
            );
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->hasAnyRole(['company-admin', 'hr']);
    }

    public function update(User $user, TurnoAssignment $turnoAssignment): bool
    {
        return $this->belongsToUsersTenant($user, $turnoAssignment->tenant_id)
            && $user->hasAnyRole(['company-admin', 'hr']);
    }

    public function delete(User $user, TurnoAssignment $turnoAssignment): bool
    {
        return $this->belongsToUsersTenant($user, $turnoAssignment->tenant_id)
            && $user->hasAnyRole(['company-admin', 'hr']);
    }

    public function deleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->hasAnyRole(['company-admin', 'hr']);
    }

    public function restore(User $user, TurnoAssignment $turnoAssignment): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, TurnoAssignment $turnoAssignment): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    private function isUsersOwnAssignment(User $user, TurnoAssignment $turnoAssignment): bool
    {
        return $turnoAssignment->user_id !== null
            && (string) $turnoAssignment->user_id === (string) $user->getKey();
    }

    private function belongsToUsersTenant(User $user, int|string|null $tenantId): bool
    {
        return $user->sharesTenantWithModel($tenantId);
    }
}
