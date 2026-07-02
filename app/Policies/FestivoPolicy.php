<?php

namespace App\Policies;

use App\Models\Festivo;
use App\Models\User;

class FestivoPolicy
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
            && $this->canViewFestivos($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Festivo $festivo): bool
    {
        return $this->belongsToUsersTenant($user, $festivo->tenant_id)
            && $this->canViewFestivos($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Festivo $festivo): bool
    {
        return $this->belongsToUsersTenant($user, $festivo->tenant_id)
            && $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Festivo $festivo): bool
    {
        return $this->belongsToUsersTenant($user, $festivo->tenant_id)
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
    public function restore(User $user, Festivo $festivo): bool
    {
        return $this->belongsToUsersTenant($user, $festivo->tenant_id)
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
    public function forceDelete(User $user, Festivo $festivo): bool
    {
        return $this->belongsToUsersTenant($user, $festivo->tenant_id)
            && $user->isCompanyAdmin();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    private function canViewFestivos(User $user): bool
    {
        return $user->hasAnyRole(['company-admin', 'hr', 'department-manager']);
    }

    private function belongsToUsersTenant(User $user, int|string|null $tenantId): bool
    {
        return $user->sharesTenantWithModel($tenantId);
    }
}
