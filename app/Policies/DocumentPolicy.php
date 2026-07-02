<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
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
            && $this->canManageDocuments($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Document $document): bool
    {
        return $this->belongsToUsersTenant($user, $document->tenant_id)
            && $this->canViewDocument($user, $document);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null
            && $this->canManageDocuments($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return $this->belongsToUsersTenant($user, $document->tenant_id)
            && $this->canManageDocuments($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return $this->belongsToUsersTenant($user, $document->tenant_id)
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
    public function restore(User $user, Document $document): bool
    {
        return $this->belongsToUsersTenant($user, $document->tenant_id)
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
    public function forceDelete(User $user, Document $document): bool
    {
        return $this->belongsToUsersTenant($user, $document->tenant_id)
            && $user->isCompanyAdmin();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin();
    }

    private function canViewDocument(User $user, Document $document): bool
    {
        if ($this->canManageDocuments($user)) {
            return true;
        }

        return false;
    }

    private function belongsToUsersTenant(User $user, int|string|null $tenantId): bool
    {
        return $user->sharesTenantWithModel($tenantId);
    }

    private function canManageDocuments(User $user): bool
    {
        return $user->isCompanyAdmin() || $user->isHr();
    }
}
