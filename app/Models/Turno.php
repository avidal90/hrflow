<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Policies\TurnoPolicy;
use Carbon\CarbonImmutable;
use Database\Factories\TurnoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

#[Fillable(['tenant_id', 'name', 'start_time', 'end_time', 'break_minutes', 'total_hours'])]
#[UsePolicy(TurnoPolicy::class)]
class Turno extends Model
{
    /** @use HasFactory<TurnoFactory> */
    use BelongsToTenant, HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $turno): void {
            if (blank($turno->start_time) || blank($turno->end_time)) {
                return;
            }

            $start = CarbonImmutable::parse((string) $turno->start_time);
            $end = CarbonImmutable::parse((string) $turno->end_time);

            if ($end->lessThanOrEqualTo($start)) {
                throw ValidationException::withMessages([
                    'end_time' => __('La hora de finalizacion debe ser posterior a la hora de inicio.'),
                ]);
            }

            $grossMinutes = $start->diffInMinutes($end);
            $breakMinutes = max((int) ($turno->break_minutes ?? 0), 0);

            if ($breakMinutes >= $grossMinutes) {
                throw ValidationException::withMessages([
                    'break_minutes' => __('El tiempo de descanso debe ser menor que la duracion total del turno.'),
                ]);
            }

            $turno->total_hours = round(($grossMinutes - $breakMinutes) / 60, 2);
        });
    }

    protected function casts(): array
    {
        return [
            'break_minutes' => 'integer',
            'total_hours' => 'decimal:2',
        ];
    }

    public function turnoAssignments(): HasMany
    {
        return $this->hasMany(TurnoAssignment::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
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

        if ($user->hasAnyRole(['company-admin', 'hr', 'department-manager'])) {
            return $query;
        }

        return $query->whereHas('turnoAssignments', function (Builder $assignmentQuery) use ($user): void {
            $assignmentQuery->where('user_id', $user->getKey());
        });
    }
}
