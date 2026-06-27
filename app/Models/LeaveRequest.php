<?php

namespace App\Models;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\Concerns\BelongsToTenant;
use App\Policies\LeaveRequestPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id',
    'employee_id',
    'request_type',
    'start_date',
    'end_date',
    'reason',
    'status',
    'resolved_by_user_id',
    'resolved_at',
    'manager_comment',
])]
#[UsePolicy(LeaveRequestPolicy::class)]
class LeaveRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LeaveRequestFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'resolved_at' => 'datetime',
            'request_type' => LeaveRequestType::class,
            'status' => LeaveRequestStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
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
