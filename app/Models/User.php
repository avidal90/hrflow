<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Policies\UserPolicy;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['tenant_id', 'department_id', 'name', 'email', 'password', 'employee_code', 'hire_date', 'employment_status', 'job_title'])]
#[Hidden(['password', 'remember_token'])]
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if ($user->tenant_id !== null) {
                $tenant = Tenant::query()->find($user->tenant_id);

                if ($tenant instanceof Tenant && $tenant->employee_license_limit !== null) {
                    $currentUsers = self::query()
                        ->where('tenant_id', $tenant->getKey())
                        ->when($user->exists, fn ($query) => $query->whereKeyNot($user->getKey()))
                        ->count();

                    if ($currentUsers >= $tenant->employee_license_limit) {
                        throw ValidationException::withMessages([
                            'tenant_id' => __('Se ha alcanzado el limite de licencias para esta empresa.'),
                        ]);
                    }
                }
            }

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
            'hire_date' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_user_id');
    }

    public function resolvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'resolved_by_user_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function turnoAssignments(): HasMany
    {
        return $this->hasMany(TurnoAssignment::class);
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

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->tenant_id !== null
            && $this->hasAnyRole(['company-admin', 'hr', 'department-manager']);
    }

    public function managesDepartment(Department $department): bool
    {
        return $department->manager_user_id !== null
            && (string) $this->getKey() === (string) $department->manager_user_id;
    }

    public function managesUser(User $user): bool
    {
        $departmentManagerId = $user->department?->manager_user_id;

        return $departmentManagerId !== null
            && (string) $this->getKey() === (string) $departmentManagerId;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->tenant_id === null) {
            return $query->whereRaw('1 = 0');
        }

        $query->where($query->getModel()->qualifyColumn('tenant_id'), $user->tenant_id);

        if ($user->isCompanyAdmin() || $user->isHr()) {
            return $query;
        }

        if ($user->isDepartmentManager()) {
            return $query->where(function (Builder $departmentQuery) use ($user): void {
                $departmentQuery
                    ->whereKey($user->getKey())
                    ->orWhereHas('department', function (Builder $managedDepartmentQuery) use ($user): void {
                        $managedDepartmentQuery->where('manager_user_id', $user->getKey());
                    });
            });
        }

        if ($user->hasRole('employee')) {
            return $query->whereKey($user->getKey());
        }

        return $query->whereRaw('1 = 0');
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
