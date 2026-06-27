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
        /** @var class-string<Model> $modelClass */
        $modelClass = static::class;

        $modelClass::creating(function (Model $model): void {
            $user = Auth::user();

            if (! $user instanceof User || $user->isSuperAdmin()) {
                return;
            }

            if ($user->tenant_id === null || filled($model->tenant_id)) {
                return;
            }

            $model->tenant_id = $user->tenant_id;
        });
    }

    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return $query;
        }

        return $this->scopeVisibleTo($query, $user);
    }

    public function scopeForTenant(Builder $query, int|string $tenantId): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('tenant_id'), $tenantId);
    }

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
