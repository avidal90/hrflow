<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
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
            && $user->hasAnyRole(['company-admin', 'hr', 'department-manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersTenant($user, $employee->tenant_id)
            && $this->canViewEmployee($user, $employee);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->hasAnyRole(['company-admin', 'hr']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersTenant($user, $employee->tenant_id)
            && ($user->hasAnyRole(['company-admin', 'hr']) || $user->managesEmployee($employee));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersTenant($user, $employee->tenant_id)
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersTenant($user, $employee->tenant_id)
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersTenant($user, $employee->tenant_id)
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    private function canViewEmployee(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['company-admin', 'hr'])
            || $user->managesEmployee($employee);
    }

    private function belongsToUsersTenant(User $user, int|string|null $tenantId): bool
    {
        return $user->sharesTenantWithModel($tenantId);
    }
}
