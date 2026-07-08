<?php

namespace App\Livewire\Portal;

use App\Enums\TimeEntryStatus;
use App\Models\TimeEntry;
use App\Models\Turno;
use App\Models\TurnoAssignment;
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

        $now = $this->tenantNow($user);

        TimeEntry::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'work_date' => $now->toDateString(),
            'check_in_time' => $now->format('H:i:s'),
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

        $this->activeEntry->update(['check_out_time' => $this->tenantNow($user)->format('H:i:s')]);
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

        $elapsedSeconds = 0;
        $elapsedLabel = '00:00:00';
        $tenantNow = $this->tenantNow($user);
        $tenantTimezone = $this->tenantTimezone($user);

        if ($this->activeEntry) {
            $checkIn = CarbonImmutable::parse(
                $this->activeEntry->work_date->toDateString().' '.$this->activeEntry->check_in_time,
                $tenantTimezone
            );
            $elapsedSeconds = max(0, $tenantNow->timestamp - $checkIn->timestamp);
            $elapsedLabel = $this->formatElapsedClock($elapsedSeconds);
        }

        $todayShiftSummary = $this->todayShiftSummary($user);

        return view('livewire.portal.time-tracker', [
            'recentEntries' => $recentEntries,
            'elapsedSeconds' => $elapsedSeconds,
            'elapsedLabel' => $elapsedLabel,
            'todayShiftSummary' => $todayShiftSummary,
            'todayOffReason' => $this->activeEntry === null ? $user->todayOffReason() : null,
        ]);
    }

    private function formatElapsedClock(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }

    /**
     * @return array{name:string,start:string,end:string,totalMinutes:int,totalLabel:string,workedMinutes:int,workedLabel:string,remainingMinutes:int,remainingLabel:string}|null
     */
    private function todayShiftSummary(User $user): ?array
    {
        $today = CarbonImmutable::today($this->tenantTimezone($user));

        $assignment = TurnoAssignment::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->with('turno')
            ->activeOn($today)
            ->orderByDesc('valid_from')
            ->latest('id')
            ->first();

        if (! $assignment instanceof TurnoAssignment || ! $assignment->turno instanceof Turno) {
            return null;
        }

        $turno = $assignment->turno;

        if ($today->isWeekend() && ! $turno->includes_weekends) {
            return null;
        }

        $totalMinutes = $this->shiftTotalMinutes($turno);
        $workedMinutes = $this->workedMinutesToday($user, $today);
        $remainingMinutes = max($totalMinutes - $workedMinutes, 0);

        return [
            'name' => $turno->name,
            'start' => substr((string) $turno->start_time, 0, 5),
            'end' => substr((string) $turno->end_time, 0, 5),
            'totalMinutes' => $totalMinutes,
            'totalLabel' => $this->formatMinutes($totalMinutes),
            'workedMinutes' => $workedMinutes,
            'workedLabel' => $this->formatMinutes($workedMinutes),
            'remainingMinutes' => $remainingMinutes,
            'remainingLabel' => $this->formatMinutes($remainingMinutes),
        ];
    }

    private function workedMinutesToday(User $user, CarbonImmutable $today): int
    {
        $tenantTimezone = $this->tenantTimezone($user);

        return (int) TimeEntry::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->whereDate('work_date', $today->toDateString())
            ->get()
            ->sum(function (TimeEntry $entry) use ($tenantTimezone, $user): int {
                $checkIn = CarbonImmutable::parse($entry->work_date->toDateString().' '.$entry->check_in_time, $tenantTimezone);

                if ($entry->check_out_time !== null) {
                    $checkOut = CarbonImmutable::parse($entry->work_date->toDateString().' '.$entry->check_out_time, $tenantTimezone);

                    return (int) $checkIn->diffInMinutes($checkOut);
                }

                return (int) $checkIn->diffInMinutes($this->tenantNow($user));
            });
    }

    private function tenantNow(User $user): CarbonImmutable
    {
        return CarbonImmutable::now($this->tenantTimezone($user));
    }

    private function tenantTimezone(User $user): string
    {
        $timezone = tenant()?->timezone;

        if (! is_string($timezone) || ! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = $user->tenant()->value('timezone');
        }

        if (! is_string($timezone) || ! in_array($timezone, timezone_identifiers_list(), true)) {
            return config('app.timezone', 'UTC');
        }

        return $timezone;
    }

    private function shiftTotalMinutes(Turno $turno): int
    {
        return (int) round((float) $turno->total_hours * 60);
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%d h %02d min', $hours, $remainingMinutes);
    }
}
