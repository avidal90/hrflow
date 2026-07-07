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

        $todayShiftSummary = $this->todayShiftSummary($user);

        return view('livewire.portal.time-tracker', [
            'recentEntries' => $recentEntries,
            'checkInTimestamp' => $checkInTimestamp,
            'todayShiftSummary' => $todayShiftSummary,
            'todayOffReason' => $this->activeEntry === null ? $user->todayOffReason() : null,
        ]);
    }

    /**
     * @return array{name:string,start:string,end:string,totalMinutes:int,totalLabel:string,workedMinutes:int,workedLabel:string,remainingMinutes:int,remainingLabel:string}|null
     */
    private function todayShiftSummary(User $user): ?array
    {
        $today = CarbonImmutable::today();

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
        return (int) TimeEntry::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->whereDate('work_date', $today->toDateString())
            ->get()
            ->sum(function (TimeEntry $entry): int {
                $checkIn = CarbonImmutable::parse($entry->work_date->toDateString().' '.$entry->check_in_time);

                if ($entry->check_out_time !== null) {
                    $checkOut = CarbonImmutable::parse($entry->work_date->toDateString().' '.$entry->check_out_time);

                    return $checkIn->diffInMinutes($checkOut);
                }

                return $checkIn->diffInMinutes(now());
            });
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
