<?php

namespace App\Livewire\Portal;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action as FilamentAction;
use Filament\Notifications\Notification;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveRequests extends Component
{
    use WithPagination;

    #[Validate('required')]
    public string $requestType = '';

    #[Validate('required|date')]
    public string $startDate = '';

    #[Validate('required|date|after_or_equal:startDate')]
    public string $endDate = '';

    #[Validate('nullable|string|max:500')]
    public string $reason = '';

    public bool $submitted = false;

    #[Computed]
    public function requestedDays(): int
    {
        if (blank($this->startDate) || blank($this->endDate)) {
            return 0;
        }

        try {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->startOfDay();

            if ($end->lt($start)) {
                return 0;
            }

            return (int) $start->diffInDays($end) + 1;
        } catch (\Throwable) {
            return 0;
        }
    }

    #[Computed]
    public function remainingVacationDays(): int
    {
        $user = auth()->user();

        $usedDays = LeaveRequest::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('request_type', LeaveRequestType::Vacation->value)
            ->where('status', LeaveRequestStatus::Approved->value)
            ->get()
            ->sum(fn (LeaveRequest $r): int => (int) $r->start_date->diffInDays($r->end_date) + 1);

        return max(0, (int) $user->annual_vacation_days - $usedDays);
    }

    public function submit(): void
    {
        $this->validate();

        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $type = LeaveRequestType::from($this->requestType);
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->startOfDay();
        $days = (int) $start->diffInDays($end) + 1;

        if ($type->isVacation() && $days > $this->remainingVacationDays) {
            $this->addError('startDate', "No tienes suficientes días de vacaciones disponibles ({$this->remainingVacationDays} restantes).");

            return;
        }

        $leaveRequest = LeaveRequest::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'request_type' => $type->value,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'reason' => filled($this->reason) ? $this->reason : null,
            'status' => LeaveRequestStatus::Pending->value,
        ]);

        $this->notifyManager($user, $leaveRequest);

        $this->reset('requestType', 'startDate', 'endDate', 'reason');
        $this->resetPage();
        $this->submitted = true;
        unset($this->requestedDays, $this->remainingVacationDays);
    }

    private function notifyManager(User $employee, LeaveRequest $leaveRequest): void
    {
        $manager = $employee->department?->manager;

        if (! $manager instanceof User || $manager->is($employee)) {
            return;
        }

        $editUrl = route('filament.admin.resources.leave-requests.edit', $leaveRequest);

        Notification::make()
            ->title('Nueva solicitud de ausencia')
            ->body("{$employee->name} ha enviado una solicitud de {$leaveRequest->request_type->label()}.")
            ->warning()
            ->actions([
                FilamentAction::make('view')
                    ->label('Ver solicitud')
                    ->url($editUrl),
            ])
            ->sendToDatabase($manager);
    }

    public function render(): View
    {
        $user = auth()->user();

        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.portal.leave-requests', [
            'leaveRequests' => $leaveRequests,
            'vacationDays' => (int) $user->annual_vacation_days,
        ]);
    }
}
