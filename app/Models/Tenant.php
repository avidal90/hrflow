<?php

namespace App\Models;

use App\Policies\TenantPolicy;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

#[UsePolicy(TenantPolicy::class)]
class Tenant extends BaseTenant
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'status',
            'locale',
            'timezone',
            'employee_license_limit',
        ];
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'tenant_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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

    public function getEmployeeLicensesUsageAttribute(): string
    {
        $used = $this->users_count ?? $this->users()->count();
        $limit = $this->employee_license_limit;

        if ($limit === null) {
            return sprintf('%d / Ilimitadas', $used);
        }

        return sprintf('%d / %d', $used, $limit);
    }

    public function getEmployeeLicensesUsagePercentAttribute(): ?float
    {
        $used = (float) ($this->users_count ?? $this->users()->count());
        $limit = $this->employee_license_limit;

        if ($limit === null || $limit <= 0) {
            return null;
        }

        return round(min(($used / $limit) * 100, 100), 2);
    }
}
