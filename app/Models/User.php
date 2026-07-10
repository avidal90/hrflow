<?php

namespace App\Models;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsTenantActivity;
use App\Policies\UserPolicy;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['tenant_id', 'department_id', 'name', 'email', 'password', 'employee_code', 'hire_date', 'employment_status', 'annual_vacation_days', 'job_title', 'avatar_path', 'phone_personal', 'phone_company', 'birth_date', 'national_id', 'social_security_number', 'birth_country', 'address'])]
#[Hidden(['password', 'remember_token'])]
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasFactory, HasRoles, LogsTenantActivity, Notifiable;

    /**
     * @return array<string, string>
     */
    public static function assignableRoleOptionsFor(?self $actingUser, ?self $targetUser = null): array
    {
        $tenantRoles = [
            'company-admin' => 'Administrador de empresa',
            'hr' => 'Recursos humanos',
            'department-manager' => 'Responsable de departamento',
            'employee' => 'Empleado',
        ];

        if ($actingUser?->isSuperAdmin()) {
            return ['super-admin' => 'Superadministrador'] + $tenantRoles;
        }

        if ($actingUser?->isCompanyAdmin()) {
            return $tenantRoles;
        }

        if ($targetUser instanceof self) {
            $roleName = $targetUser->primaryRoleName();

            if ($roleName !== null) {
                return [$roleName => self::roleLabel($roleName)];
            }
        }

        return [];
    }

    public static function roleLabel(?string $roleName): string
    {
        return match ($roleName) {
            'super-admin' => 'Superadministrador',
            'company-admin' => 'Administrador de empresa',
            'hr' => 'Recursos humanos',
            'department-manager' => 'Responsable de departamento',
            'employee' => 'Empleado',
            default => '-',
        };
    }

    public function belongsToPrincipalTenant(): bool
    {
        return (string) $this->tenant_id === Tenant::principalTenantId();
    }

    public static function canManageRoleAssignments(?self $actingUser, ?self $targetUser = null): bool
    {
        if (! $actingUser instanceof self) {
            return false;
        }

        if ($actingUser->isSuperAdmin()) {
            return ! ($targetUser instanceof self && $actingUser->is($targetUser));
        }

        if (! $actingUser->isCompanyAdmin()) {
            return false;
        }

        if (! $targetUser instanceof self) {
            return true;
        }

        return ! $actingUser->is($targetUser) && ! $targetUser->isSuperAdmin();
    }

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
                            'tenant_id' => __('Se ha alcanzado el límite de licencias para esta empresa.'),
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
                    'email' => __('El email ya está en uso.'),
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
            'birth_date' => 'date',
            'annual_vacation_days' => 'integer',
            'national_id' => 'encrypted',
            'social_security_number' => 'encrypted',
        ];
    }

    public function maskedNationalId(): ?string
    {
        return $this->maskedSensitiveValue($this->national_id);
    }

    public function maskedSocialSecurityNumber(): ?string
    {
        return $this->maskedSensitiveValue($this->social_security_number);
    }

    private function maskedSensitiveValue(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $clean = preg_replace('/\s+/', '', $value);

        if (! is_string($clean)) {
            return null;
        }

        $length = mb_strlen($clean);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4).mb_substr($clean, -4);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return HasMany<Document, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /** @return HasMany<LeaveRequest, $this> */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /** @return HasMany<Department, $this> */
    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_user_id');
    }

    /** @return HasMany<LeaveRequest, $this> */
    public function resolvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'resolved_by_user_id');
    }

    /** @return HasMany<TimeEntry, $this> */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /** @return HasMany<TurnoAssignment, $this> */
    public function turnoAssignments(): HasMany
    {
        return $this->hasMany(TurnoAssignment::class);
    }

    public function isAccountActive(): bool
    {
        return in_array($this->employment_status, ['active', 'on_leave'], true);
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

    public function primaryRoleName(): ?string
    {
        return $this->roles->pluck('name')->first();
    }

    public function primaryRoleLabel(): string
    {
        return self::roleLabel($this->primaryRoleName());
    }

    public function approvedVacationDaysBooked(): int
    {
        return (int) $this->leaveRequests()
            ->where('tenant_id', $this->tenant_id)
            ->where('request_type', LeaveRequestType::Vacation->value)
            ->where('status', LeaveRequestStatus::Approved->value)
            ->get()
            ->sum(fn (LeaveRequest $leaveRequest): int => (int) $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1);
    }

    public function approvedVacationDaysConsumedToDate(): int
    {
        $today = today();

        return (int) $this->leaveRequests()
            ->where('tenant_id', $this->tenant_id)
            ->where('request_type', LeaveRequestType::Vacation->value)
            ->where('status', LeaveRequestStatus::Approved->value)
            ->whereDate('start_date', '<=', $today)
            ->get()
            ->sum(function (LeaveRequest $leaveRequest) use ($today): int {
                $consumedUntil = $leaveRequest->end_date->lt($today)
                    ? $leaveRequest->end_date
                    : $today;

                return (int) $leaveRequest->start_date->diffInDays($consumedUntil) + 1;
            });
    }

    public function remainingVacationDays(): int
    {
        return max(0, (int) $this->annual_vacation_days - $this->approvedVacationDaysBooked());
    }

    /**
     * Returns why the user does not need to clock in today, or null if they should.
     * Possible values: 'leave', 'festivo', 'weekend'
     */
    public function todayOffReason(): ?string
    {
        $today = CarbonImmutable::today();

        if ($this->tenant_id !== null && $this->leaveRequests()
            ->where('tenant_id', $this->tenant_id)
            ->where('status', LeaveRequestStatus::Approved)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->exists()) {
            return 'leave';
        }

        if ($this->tenant_id !== null && Festivo::query()
            ->where('tenant_id', $this->tenant_id)
            ->whereDate('date', $today)
            ->exists()) {
            return 'festivo';
        }

        if ($today->isWeekend() && $this->tenant_id !== null) {
            $assignment = TurnoAssignment::query()
                ->where('user_id', $this->getKey())
                ->where('tenant_id', $this->tenant_id)
                ->with('turno')
                ->activeOn($today)
                ->latest('id')
                ->first();

            if ($assignment instanceof TurnoAssignment
                && $assignment->turno instanceof Turno
                && ! $assignment->turno->includes_weekends) {
                return 'weekend';
            }
        }

        return null;
    }

    public function canAccessAdministration(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->tenant_id !== null
            && $this->isAccountActive()
            && $this->hasAnyRole(['company-admin', 'hr', 'department-manager']);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessAdministration();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->avatar_path)) {
            return null;
        }

        try {
            return Storage::disk('avatars')
                ->temporaryUrl((string) $this->avatar_path, now()->addHour());
        } catch (\Throwable) {
            return null;
        }
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
        $query->whereDoesntHave('roles', function (Builder $roleQuery): void {
            $roleQuery->where('name', 'super-admin');
        });

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

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
