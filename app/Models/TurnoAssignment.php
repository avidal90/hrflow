<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Policies\TurnoAssignmentPolicy;
use Carbon\CarbonImmutable;
use Database\Factories\TurnoAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * @property Turno|null $turno
 */
#[Fillable(['tenant_id', 'turno_id', 'user_id', 'valid_from', 'valid_until'])]
#[UsePolicy(TurnoAssignmentPolicy::class)]
class TurnoAssignment extends Model
{
    /** @use HasFactory<TurnoAssignmentFactory> */
    use BelongsToTenant, HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $turnoAssignment): void {
            if (blank($turnoAssignment->turno_id) || blank($turnoAssignment->user_id)) {
                throw ValidationException::withMessages([
                    'turno_id' => __('Debes seleccionar un turno y un usuario para crear la asignacion.'),
                ]);
            }

            $turnoAssignment->tenant_id ??= $turnoAssignment->user->tenant_id
                ?? $turnoAssignment->turno?->tenant_id;

            if (
                $turnoAssignment->tenant_id !== null
                && $turnoAssignment->user?->tenant_id !== null
                && (string) $turnoAssignment->tenant_id !== (string) $turnoAssignment->user->tenant_id
            ) {
                throw ValidationException::withMessages([
                    'user_id' => __('El usuario debe pertenecer a la misma empresa que la asignacion.'),
                ]);
            }
            if (
                $turnoAssignment->tenant_id !== null
                && $turnoAssignment->turno?->tenant_id !== null
                && (string) $turnoAssignment->tenant_id !== (string) $turnoAssignment->turno->tenant_id
            ) {
                throw ValidationException::withMessages([
                    'turno_id' => __('El turno debe pertenecer a la misma empresa que la asignacion.'),
                ]);
            }

            if (blank($turnoAssignment->valid_from) || blank($turnoAssignment->valid_until)) {
                return;
            }

            $validFrom = CarbonImmutable::parse((string) $turnoAssignment->valid_from)->startOfDay();
            $validUntil = CarbonImmutable::parse((string) $turnoAssignment->valid_until)->startOfDay();

            if ($validUntil->lessThan($validFrom)) {
                throw ValidationException::withMessages([
                    'valid_until' => __('La fecha de fin de vigencia debe ser posterior o igual a la fecha de inicio.'),
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Turno, $this> */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActiveOn(Builder $query, CarbonImmutable|string|null $date = null): Builder
    {
        $targetDate = CarbonImmutable::parse($date ?? now())->startOfDay();

        return $query
            ->where(function (Builder $validFromQuery) use ($targetDate): void {
                $validFromQuery
                    ->whereNull('valid_from')
                    ->orWhereDate('valid_from', '<=', $targetDate);
            })
            ->where(function (Builder $validUntilQuery) use ($targetDate): void {
                $validUntilQuery
                    ->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', $targetDate);
            });
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

        $query->where($query->getModel()->qualifyColumn('tenant_id'), $user->tenant_id);

        if ($user->hasAnyRole(['company-admin', 'hr', 'department-manager'])) {
            return $query;
        }

        return $query->where('user_id', $user->getKey());
    }
}
