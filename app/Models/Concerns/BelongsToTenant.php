<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        $modelClass = static::class;

        $modelClass::creating(function (Model $model): void {
            self::normalizeTenantForAuthenticatedUser($model);
        });

        $modelClass::updating(function (Model $model): void {
            self::normalizeTenantForAuthenticatedUser($model);
        });
    }

    private static function normalizeTenantForAuthenticatedUser(Model $model): void
    {
        $user = Auth::user();

        if (! $user instanceof User || $user->isSuperAdmin() || $user->tenant_id === null) {
            return;
        }

        $model->setAttribute('tenant_id', $user->tenant_id);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return $query;
        }

        return $this->scopeVisibleTo($query, $user);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForTenant(Builder $query, int|string $tenantId): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('tenant_id'), $tenantId);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->tenant_id === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($query->getModel()->qualifyColumn('tenant_id'), $user->tenant_id);
    }
}
