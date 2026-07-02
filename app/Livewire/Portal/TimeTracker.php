<?php

namespace App\Livewire\Portal;

use App\Enums\TimeEntryStatus;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;
use Livewire\Attributes\Poll;
use Livewire\Component;
use Livewire\WithPagination;

class TimeTracker extends Component
{
    use WithPagination;

    public ?TimeEntry $activeEntry = null;

    public function mount(): void
    {
        $this->syncActiveEntry();
    }

    public function startTracking(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        abort_unless($this->activeEntry === null, 422);

        TimeEntry::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'check_in_time' => now()->format('H:i:s'),
        ]);
        $this->resetPage();
        $this->syncActiveEntry();
    }

    public function stopTracking(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        abort_unless($this->activeEntry !== null, 422);
        abort_unless((int) $this->activeEntry->user_id === (int) $user->id, 403);

        $this->activeEntry->update(['check_out_time' => now()->format('H:i:s')]);
        $this->activeEntry = null;
        $this->resetPage();
    }

    #[Poll('30s')]
    public function syncActiveEntry(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $this->activeEntry = TimeEntry::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('status', TimeEntryStatus::Incomplete->value)
            ->latest('id')
            ->first();
    }

    public function render(): View
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $recentEntries = TimeEntry::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->orderByDesc('work_date')
            ->orderByDesc('check_in_time')
            ->paginate(20);

        $checkInTimestamp = 0;

        if ($this->activeEntry) {
            $checkIn = CarbonImmutable::parse(
                $this->activeEntry->work_date->toDateString().' '.$this->activeEntry->check_in_time
            );
            $checkInTimestamp = $checkIn->timestamp;
        }

        return view('livewire.portal.time-tracker', [
            'recentEntries' => $recentEntries,
            'checkInTimestamp' => $checkInTimestamp,
        ]);
    }
}
