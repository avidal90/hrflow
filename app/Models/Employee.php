<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Policies\EmployeePolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

#[Fillable(['tenant_id', 'user_id', 'department_id', 'employee_code', 'first_name', 'last_name', 'hire_date', 'employment_status', 'job_title'])]
#[UsePolicy(EmployeePolicy::class)]
class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use BelongsToTenant, HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $employee): void {
            $tenant = Tenant::query()->find($employee->tenant_id);

            if (! $tenant instanceof Tenant) {
                return;
            }

            $licenseLimit = $tenant->employee_license_limit;

            if ($licenseLimit === null) {
                return;
            }

            $currentEmployees = self::query()
                ->where('tenant_id', $tenant->getKey())
                ->when($employee->exists, fn ($query) => $query->whereKeyNot($employee->getKey()))
                ->count();

            if ($currentEmployees >= $licenseLimit) {
                throw ValidationException::withMessages([
                    'tenant_id' => __('Se ha alcanzado el limite de licencias para esta empresa.'),
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_employee_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
