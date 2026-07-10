<?php

namespace App\Models;

use App\Policies\ActivityPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * @property string|null $tenant_id
 * @property string|null $ip_address
 */
#[UsePolicy(ActivityPolicy::class)]
class Activity extends SpatieActivity
{
    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subjectLabel(): string
    {
        if ($this->subject_type === null) {
            return '-';
        }

        $shortName = class_basename($this->subject_type);

        return $this->subject_id
            ? "{$shortName} #{$this->subject_id}"
            : $shortName;
    }

    public function causerLabel(): string
    {
        $causer = $this->causer;

        if ($causer instanceof User) {
            return $causer->name;
        }

        return 'Sistema';
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            'created' => 'Creación',
            'updated' => 'Modificación',
            'deleted' => 'Eliminación',
            'restored' => 'Restauración',
            default => ucfirst($this->event ?? '-'),
        };
    }

    public function eventColor(): string
    {
        return match ($this->event) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'restored' => 'warning',
            default => 'gray',
        };
    }
}
