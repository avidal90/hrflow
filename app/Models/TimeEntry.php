<?php

namespace App\Models;

use App\Enums\TimeEntryStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsTenantActivity;
use App\Policies\TimeEntryPolicy;
use Carbon\CarbonImmutable;
use Database\Factories\TimeEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * @property Carbon $work_date
 * @property TimeEntryStatus $status
 */
#[Fillable(['tenant_id', 'user_id', 'work_date', 'check_in_time', 'check_out_time', 'duration_minutes', 'status', 'notes'])]
#[UsePolicy(TimeEntryPolicy::class)]
class TimeEntry extends Model
{
    /** @use HasFactory<TimeEntryFactory> */
    use BelongsToTenant, HasFactory, LogsTenantActivity;

    protected static function booted(): void
    {
        static::saving(function (self $timeEntry): void {
            if (blank($timeEntry->check_out_time)) {
                $timeEntry->status = TimeEntryStatus::Incomplete;
                $timeEntry->duration_minutes = null;

                return;
            }

            $checkIn = CarbonImmutable::parse((string) $timeEntry->check_in_time);
            $checkOut = CarbonImmutable::parse((string) $timeEntry->check_out_time);

            if ($checkOut->lessThanOrEqualTo($checkIn)) {
                throw ValidationException::withMessages([
                    'check_out_time' => __('La hora de salida debe ser posterior a la hora de entrada.'),
                ]);
            }

            $timeEntry->duration_minutes = (int) $checkIn->diffInMinutes($checkOut);
            $timeEntry->status = TimeEntryStatus::Complete;
        });
    }

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'status' => TimeEntryStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $query->visibleTo($user);

        if ($user->isCompanyAdmin() || $user->isHr()) {
            return $query;
        }

        if ($user->isDepartmentManager()) {
            return $query->whereHas('user.department', function (Builder $departmentQuery) use ($user): void {
                $departmentQuery->where('manager_user_id', $user->getKey());
            });
        }

        if ($user->hasRole('employee')) {
            return $query->where('user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }
}
