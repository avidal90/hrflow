<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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

    public function view(User $user, User $targetUser): bool
    {
        return $this->belongsToUsersTenant($user, $targetUser->tenant_id)
            && $this->canViewUser($user, $targetUser);
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->hasAnyRole(['company-admin', 'hr']);
    }

    public function update(User $user, User $targetUser): bool
    {
        return $this->belongsToUsersTenant($user, $targetUser->tenant_id)
            && ($user->hasAnyRole(['company-admin', 'hr']) || $user->managesUser($targetUser));
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $this->belongsToUsersTenant($user, $targetUser->tenant_id)
            && $user->isCompanyAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    public function restore(User $user, User $targetUser): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $targetUser): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    private function canViewUser(User $user, User $targetUser): bool
    {
        return $user->hasAnyRole(['company-admin', 'hr'])
            || $user->managesUser($targetUser);
    }

    private function belongsToUsersTenant(User $user, int|string|null $tenantId): bool
    {
        return $user->sharesTenantWithModel($tenantId);
    }
}
