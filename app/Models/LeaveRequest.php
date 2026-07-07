<?php

namespace App\Models;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\Concerns\BelongsToTenant;
use App\Policies\LeaveRequestPolicy;
use Database\Factories\LeaveRequestFactory;
use Filament\Actions\Action as FilamentAction;
use Filament\Notifications\Notification;
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
    /** @use HasFactory<LeaveRequestFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::updated(function (self $leaveRequest): void {
            if (! $leaveRequest->wasChanged('status')) {
                return;
            }

            $newStatus = $leaveRequest->status;

            if ($newStatus === LeaveRequestStatus::Pending) {
                return;
            }

            $employee = $leaveRequest->user;

            if (! $employee instanceof User) {
                return;
            }

            $isApproved = $newStatus === LeaveRequestStatus::Approved;
            $statusLabel = $newStatus->label();
            $typeLabel = $leaveRequest->request_type->label();
            $notificationTitle = $isApproved ? 'Solicitud aprobada' : 'Solicitud rechazada';
            $portalUrl = route('portal.requests.index', ['tenant' => $leaveRequest->tenant_id]);

            $notification = Notification::make()
                ->title($notificationTitle)
                ->body("Tu solicitud de {$typeLabel} ha sido {$statusLabel}.")
                ->actions([
                    FilamentAction::make('view')
                        ->label('Ver solicitudes')
                        ->url($portalUrl),
                ]);

            $isApproved ? $notification->success() : $notification->danger();

            $notification->sendToDatabase($employee);
        });
    }

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
