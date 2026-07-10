<?php

namespace App\Models\Concerns;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsTenantActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $user = Auth::user();

        $activity->ip_address = Request::ip();

        if ($user instanceof User && $user->tenant_id !== null) {
            $activity->tenant_id = $user->tenant_id;
        } elseif (isset($this->tenant_id)) {
            $activity->tenant_id = $this->tenant_id;
        }
    }
}
