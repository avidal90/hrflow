<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
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
            && $user->isCompanyAdmin();
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->tenant_id !== null
            && $user->isCompanyAdmin()
            && $activity->tenant_id === $user->tenant_id;
    }
}
