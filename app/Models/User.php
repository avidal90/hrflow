<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['tenant_id', 'name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if (! $user->isDirty('email')) {
                return;
            }

            $exists = self::query()
                ->where('email', $user->email)
                ->when($user->exists, fn ($query) => $query->whereKeyNot($user->getKey()))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'email' => __('El email ya esta en uso.'),
                ]);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function resolvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'resolved_by_user_id');
    }

    public function isCompanyAdmin(): bool
    {
        return $this->hasRole('company-admin');
    }

    public function isDepartmentManager(): bool
    {
        return $this->hasRole('department-manager');
    }

    public function isHr(): bool
    {
        return $this->hasRole('hr');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function managesDepartment(Department $department): bool
    {
        $managerEmployeeId = $this->employee?->getKey();

        return $managerEmployeeId !== null
            && $department->manager_employee_id !== null
            && (string) $managerEmployeeId === (string) $department->manager_employee_id;
    }

    public function managesEmployee(Employee $employee): bool
    {
        $managerEmployeeId = $this->employee?->getKey();
        $departmentManagerId = $employee->department?->manager_employee_id;

        return $managerEmployeeId !== null
            && $departmentManagerId !== null
            && (string) $managerEmployeeId === (string) $departmentManagerId;
    }

    public function sharesTenantWithModel(int|string|null $tenantId): bool
    {
        return $this->tenant_id !== null
            && $tenantId !== null
            && (string) $this->tenant_id === (string) $tenantId;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
