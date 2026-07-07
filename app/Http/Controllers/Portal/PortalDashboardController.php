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
use Illuminate\Support\Carbon;

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

        $nextApprovedLeaveRequest = $this->nearestApprovedLeaveRequest($user);

        $visibleDocumentsCount = $user->documents()
            ->when($user->hasRole('employee'), fn ($query) => $query->where('is_visible_to_employee', true))
            ->count();

        $usedVacationDays = $user->approvedVacationDaysConsumedToDate();
        $remainingVacationDays = $user->remainingVacationDays();

        $todayOffReason = $todayTimeEntry === null ? $user->todayOffReason() : null;

        return view('portal.dashboard', [
            'portalUser' => $user,
            'calendarDateLabel' => $this->calendarDateLabel($nextApprovedLeaveRequest),
            'calendarDescription' => $this->calendarDescription($nextApprovedLeaveRequest),
            'documentDescription' => $this->documentDescription($visibleDocumentsCount),
            'nextApprovedLeaveRequest' => $nextApprovedLeaveRequest,
            'pendingLeaveRequestsCount' => $pendingLeaveRequestsCount,
            'remainingVacationDays' => $remainingVacationDays,
            'requestsDescription' => $this->requestsDescription($pendingLeaveRequestsCount, $totalLeaveRequestsCount),
            'todayTimeEntry' => $todayTimeEntry,
            'todayOffReason' => $todayOffReason,
            'timeTrackingDescription' => $this->timeTrackingDescription($todayTimeEntry),
            'totalLeaveRequestsCount' => $totalLeaveRequestsCount,
            'usedVacationDays' => $usedVacationDays,
            'visibleDocumentsCount' => $visibleDocumentsCount,
        ]);
    }

    private function calendarDescription(?LeaveRequest $leaveRequest): string
    {
        if (! $leaveRequest instanceof LeaveRequest) {
            return 'No tienes ausencias aprobadas proximas en tu calendario.';
        }

        $today = today();
        $startDate = $leaveRequest->start_date;
        $endDate = $leaveRequest->end_date;

        if ($startDate instanceof Carbon && $startDate->isAfter($today)) {
            return sprintf(
                'Tu siguiente ausencia aprobada empieza el %s.',
                $startDate->format('d/m/Y')
            );
        }

        if ($startDate instanceof Carbon && $endDate instanceof Carbon && $today->betweenIncluded($startDate, $endDate)) {
            return sprintf(
                'Tu ausencia aprobada actual termina el %s.',
                $endDate->format('d/m/Y')
            );
        }

        return sprintf(
            'Tu ausencia aprobada mas cercana fue del %s al %s.',
            $startDate?->format('d/m/Y') ?? '-',
            $endDate?->format('d/m/Y') ?? '-'
        );
    }

    private function calendarDateLabel(?LeaveRequest $leaveRequest): string
    {
        if (! $leaveRequest instanceof LeaveRequest) {
            return '-';
        }

        $today = today();
        $startDate = $leaveRequest->start_date;
        $endDate = $leaveRequest->end_date;

        if ($startDate instanceof Carbon && $startDate->isAfter($today)) {
            return $startDate->format('d/m');
        }

        return $endDate?->format('d/m') ?? $startDate?->format('d/m') ?? '-';
    }

    private function nearestApprovedLeaveRequest(User $user): ?LeaveRequest
    {
        $today = today();
        $approvedLeaveRequests = $user->leaveRequests()
            ->where('status', LeaveRequestStatus::Approved->value);

        $currentLeaveRequest = (clone $approvedLeaveRequests)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('end_date')
            ->first();

        if ($currentLeaveRequest instanceof LeaveRequest) {
            return $currentLeaveRequest;
        }

        $upcomingLeaveRequest = (clone $approvedLeaveRequests)
            ->whereDate('start_date', '>', $today)
            ->orderBy('start_date')
            ->first();

        $recentPastLeaveRequest = (clone $approvedLeaveRequests)
            ->whereDate('end_date', '<', $today)
            ->orderByDesc('end_date')
            ->first();

        if (! $upcomingLeaveRequest instanceof LeaveRequest) {
            return $recentPastLeaveRequest;
        }

        if (! $recentPastLeaveRequest instanceof LeaveRequest) {
            return $upcomingLeaveRequest;
        }

        $daysUntilUpcoming = $today->diffInDays($upcomingLeaveRequest->start_date);
        $daysSinceRecentPast = $today->diffInDays($recentPastLeaveRequest->end_date);

        return $daysUntilUpcoming <= $daysSinceRecentPast
            ? $upcomingLeaveRequest
            : $recentPastLeaveRequest;
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
