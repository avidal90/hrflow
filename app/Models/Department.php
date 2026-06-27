<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Policies\DepartmentPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tenant_id', 'name', 'manager_employee_id'])]
#[UsePolicy(DepartmentPolicy::class)]
class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use BelongsToTenant, HasFactory;

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_employee_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
