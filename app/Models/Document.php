<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use App\Models\Concerns\BelongsToTenant;
use App\Policies\DocumentPolicy;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id',
    'user_id',
    'category',
    'name',
    'description',
    'file_path',
    'mime_type',
    'file_size',
    'uploaded_at',
    'is_visible_to_employee',
])]
#[UsePolicy(DocumentPolicy::class)]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'is_visible_to_employee' => 'boolean',
            'category' => DocumentCategory::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            return $query->whereHas('user.department', function (Builder $departmentQuery) use ($user): void {
                $departmentQuery->where('manager_user_id', $user->getKey());
            });
        }

        if ($user->hasRole('employee')) {
            return $query
                ->where('is_visible_to_employee', true)
                ->where('user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }
}
