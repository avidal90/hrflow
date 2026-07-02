<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->hasAnyRole(['company-admin', 'hr', 'department-manager', 'employee']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->belongsToUsersTenant($user, $leaveRequest->tenant_id)
            && $this->canViewLeaveRequest($user, $leaveRequest);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->hasAnyRole(['company-admin', 'hr', 'department-manager', 'employee']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->belongsToUsersTenant($user, $leaveRequest->tenant_id)
            && (
                $user->hasAnyRole(['company-admin', 'hr'])
                || $this->isOwnRequest($user, $leaveRequest)
                || $this->isDepartmentManagerOfRequestOwner($user, $leaveRequest)
            );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->belongsToUsersTenant($user, $leaveRequest->tenant_id)
            && (
                $user->isCompanyAdmin()
                || $this->isDepartmentManagerOfRequestOwner($user, $leaveRequest)
            );
    }

    public function deleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->belongsToUsersTenant($user, $leaveRequest->tenant_id)
            && $user->isCompanyAdmin();
    }

    public function restoreAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->belongsToUsersTenant($user, $leaveRequest->tenant_id)
            && $user->isCompanyAdmin();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    private function canViewLeaveRequest(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasAnyRole(['company-admin', 'hr'])) {
            return true;
        }

        if ($user->isDepartmentManager()) {
            $ownedUser = $leaveRequest->user;

            if (! $ownedUser instanceof User) {
                return false;
            }

            return $user->managesUser($ownedUser);
        }

        return $this->isOwnRequest($user, $leaveRequest);
    }

    private function isOwnRequest(User $user, LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->user_id !== null
            && (string) $leaveRequest->user_id === (string) $user->getKey();
    }

    private function isDepartmentManagerOfRequestOwner(User $user, LeaveRequest $leaveRequest): bool
    {
        if (! $user->isDepartmentManager()) {
            return false;
        }

        $owner = $leaveRequest->user;

        return $owner instanceof User && $user->managesUser($owner);
    }

    private function belongsToUsersTenant(User $user, int|string|null $tenantId): bool
    {
        return $user->sharesTenantWithModel($tenantId);
    }
}
