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
        if ($this->isProtectedSuperAdmin($user, $targetUser)) {
            return false;
        }

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
        if ($this->isProtectedSuperAdmin($user, $targetUser)) {
            return false;
        }

        return $this->belongsToUsersTenant($user, $targetUser->tenant_id)
            && $this->canUpdateUser($user, $targetUser);
    }

    public function resetPassword(User $user, User $targetUser): bool
    {
        if ($this->isProtectedSuperAdmin($user, $targetUser)) {
            return false;
        }

        return $this->belongsToUsersTenant($user, $targetUser->tenant_id)
            && $this->canUpdateUser($user, $targetUser);
    }

    public function viewOwnProfile(User $user, User $targetUser): bool
    {
        return $this->belongsToUsersTenant($user, $targetUser->tenant_id)
            && $this->isSameUser($user, $targetUser);
    }

    public function updateOwnProfile(User $user, User $targetUser): bool
    {
        return $this->belongsToUsersTenant($user, $targetUser->tenant_id)
            && $this->isSameUser($user, $targetUser);
    }

    public function updateOwnPassword(User $user, User $targetUser): bool
    {
        return $this->belongsToUsersTenant($user, $targetUser->tenant_id)
            && $this->isSameUser($user, $targetUser);
    }

    public function delete(User $user, User $targetUser): bool
    {
        if ($this->isProtectedSuperAdmin($user, $targetUser)) {
            return false;
        }

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

    private function canUpdateUser(User $user, User $targetUser): bool
    {
        return $user->hasAnyRole(['company-admin', 'hr']);
    }

    private function belongsToUsersTenant(User $user, int|string|null $tenantId): bool
    {
        return $user->sharesTenantWithModel($tenantId);
    }

    private function isSameUser(User $user, User $targetUser): bool
    {
        return (string) $user->getKey() === (string) $targetUser->getKey();
    }

    private function isProtectedSuperAdmin(User $user, User $targetUser): bool
    {
        return $targetUser->isSuperAdmin() && ! $user->isSuperAdmin();
    }
}
