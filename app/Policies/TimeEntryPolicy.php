<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy
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
    public function view(User $user, TimeEntry $timeEntry): bool
    {
        return $this->belongsToUsersTenant($user, $timeEntry->tenant_id)
            && $this->canViewTimeEntry($user, $timeEntry);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->hasAnyRole(['company-admin', 'hr', 'employee']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TimeEntry $timeEntry): bool
    {
        return $this->belongsToUsersTenant($user, $timeEntry->tenant_id)
            && ($user->hasAnyRole(['company-admin', 'hr']) || $this->isOwnTimeEntry($user, $timeEntry));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TimeEntry $timeEntry): bool
    {
        return $this->belongsToUsersTenant($user, $timeEntry->tenant_id)
            && $user->isCompanyAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TimeEntry $timeEntry): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TimeEntry $timeEntry): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    private function canViewTimeEntry(User $user, TimeEntry $timeEntry): bool
    {
        if ($user->hasAnyRole(['company-admin', 'hr'])) {
            return true;
        }

        if ($user->isDepartmentManager()) {
            $employee = $timeEntry->employee;

            if (! $employee instanceof \App\Models\Employee) {
                return false;
            }

            return $user->managesEmployee($employee);
        }

        return $this->isOwnTimeEntry($user, $timeEntry);
    }

    private function isOwnTimeEntry(User $user, TimeEntry $timeEntry): bool
    {
        return $timeEntry->employee !== null
            && $timeEntry->employee->user_id !== null
            && (string) $timeEntry->employee->user_id === (string) $user->getKey();
    }

    private function belongsToUsersTenant(User $user, int|string|null $tenantId): bool
    {
        return $user->sharesTenantWithModel($tenantId);
    }
}
