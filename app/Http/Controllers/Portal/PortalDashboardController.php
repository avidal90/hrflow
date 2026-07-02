<?php

namespace App\Http\Controllers\Portal;

use App\Enums\LeaveRequestStatus;
use App\Enums\TimeEntryStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PortalDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing(['department', 'roles', 'tenant']);

        $todayTimeEntry = $user->timeEntries()
            ->whereDate('work_date', today())
            ->latest('id')
            ->first();

        $pendingLeaveRequestsCount = $user->leaveRequests()
            ->where('status', LeaveRequestStatus::Pending->value)
            ->count();

        $totalLeaveRequestsCount = $user->leaveRequests()->count();

        $nextApprovedLeaveRequest = $user->leaveRequests()
            ->where('status', LeaveRequestStatus::Approved->value)
            ->whereDate('start_date', '>=', today())
            ->orderBy('start_date')
            ->first();

        $visibleDocumentsCount = $user->documents()
            ->when($user->hasRole('employee'), fn ($query) => $query->where('is_visible_to_employee', true))
            ->count();

        return view('portal.dashboard', [
            'portalUser' => $user,
            'calendarDescription' => $this->calendarDescription($nextApprovedLeaveRequest),
            'documentDescription' => $this->documentDescription($visibleDocumentsCount),
            'nextApprovedLeaveRequest' => $nextApprovedLeaveRequest,
            'pendingLeaveRequestsCount' => $pendingLeaveRequestsCount,
            'requestsDescription' => $this->requestsDescription($pendingLeaveRequestsCount, $totalLeaveRequestsCount),
            'todayTimeEntry' => $todayTimeEntry,
            'timeTrackingDescription' => $this->timeTrackingDescription($todayTimeEntry),
            'totalLeaveRequestsCount' => $totalLeaveRequestsCount,
            'visibleDocumentsCount' => $visibleDocumentsCount,
        ]);
    }

    private function calendarDescription(?LeaveRequest $leaveRequest): string
    {
        if (! $leaveRequest instanceof LeaveRequest) {
            return 'No tienes ausencias aprobadas proximas en tu calendario.';
        }

        return sprintf(
            'Tu siguiente ausencia aprobada empieza el %s.',
            $leaveRequest->start_date?->format('d/m/Y') ?? '-'
        );
    }

    private function documentDescription(int $visibleDocumentsCount): string
    {
        return sprintf(
            '%d %s disponible%s en tu expediente personal.',
            $visibleDocumentsCount,
            $visibleDocumentsCount === 1 ? 'documento' : 'documentos',
            $visibleDocumentsCount === 1 ? '' : 's'
        );
    }

    private function requestsDescription(int $pendingLeaveRequestsCount, int $totalLeaveRequestsCount): string
    {
        return sprintf(
            '%d solicitud%s pendiente%s y %d solicitud%s registrada%s.',
            $pendingLeaveRequestsCount,
            $pendingLeaveRequestsCount === 1 ? '' : 'es',
            $pendingLeaveRequestsCount === 1 ? '' : 's',
            $totalLeaveRequestsCount,
            $totalLeaveRequestsCount === 1 ? '' : 'es',
            $totalLeaveRequestsCount === 1 ? '' : 's'
        );
    }

    private function timeTrackingDescription(?TimeEntry $timeEntry): string
    {
        if (! $timeEntry instanceof TimeEntry) {
            return 'Todavia no has registrado jornada hoy.';
        }

        $checkInTime = $timeEntry->check_in_time !== null
            ? substr((string) $timeEntry->check_in_time, 0, 5)
            : '--:--';

        if ($timeEntry->status === TimeEntryStatus::Incomplete) {
            return sprintf('Has fichado a las %s y tu salida sigue pendiente.', $checkInTime);
        }

        $checkOutTime = $timeEntry->check_out_time !== null
            ? substr((string) $timeEntry->check_out_time, 0, 5)
            : '--:--';

        return sprintf('Jornada de hoy completada de %s a %s.', $checkInTime, $checkOutTime);
    }
}
