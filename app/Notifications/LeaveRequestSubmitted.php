<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        private readonly LeaveRequest $leaveRequest
    ) {}

    /** @return array<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $employee = $this->leaveRequest->user;
        $editUrl = route('filament.admin.resources.leave-requests.edit', $this->leaveRequest);

        return [
            'message' => "{$employee->name} ha enviado una solicitud de {$this->leaveRequest->request_type->label()}.",
            'employee_name' => $employee->name,
            'request_type' => $this->leaveRequest->request_type->label(),
            'start_date' => $this->leaveRequest->start_date->toDateString(),
            'end_date' => $this->leaveRequest->end_date->toDateString(),
            'edit_url' => $editUrl,
        ];
    }
}
