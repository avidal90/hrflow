<?php

namespace App\Models;

use App\Enums\TimeEntryStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Policies\TimeEntryPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

#[Fillable(['tenant_id', 'employee_id', 'work_date', 'check_in_time', 'check_out_time', 'duration_minutes', 'status', 'notes'])]
#[UsePolicy(TimeEntryPolicy::class)]
class TimeEntry extends Model
{
    /** @use HasFactory<\Database\Factories\TimeEntryFactory> */
    use BelongsToTenant, HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $timeEntry): void {
            if (blank($timeEntry->check_out_time)) {
                $timeEntry->status = TimeEntryStatus::Incomplete->value;
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

            $timeEntry->duration_minutes = $checkIn->diffInMinutes($checkOut);
            $timeEntry->status = TimeEntryStatus::Complete->value;
        });
    }

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'status' => TimeEntryStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

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
            $managerEmployeeId = $user->employee?->getKey();

            if ($managerEmployeeId === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('employee.department', function (Builder $departmentQuery) use ($managerEmployeeId): void {
                $departmentQuery->where('manager_employee_id', $managerEmployeeId);
            });
        }

        if ($user->hasRole('employee')) {
            return $query->whereHas('employee', function (Builder $employeeQuery) use ($user): void {
                $employeeQuery->where('user_id', $user->getKey());
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
