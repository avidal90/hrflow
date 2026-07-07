<?php

namespace App\Filament\Widgets;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Enums\TimeEntryStatus;
use App\Models\LeaveRequest;
use App\Models\TimeEntry;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class HrStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        /** @var User $user */
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $pendingRequests = LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', LeaveRequestStatus::Pending)
            ->count();

        $pendingVacations = LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', LeaveRequestStatus::Pending)
            ->where('request_type', LeaveRequestType::Vacation)
            ->count();

        $pendingLeaves = LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', LeaveRequestStatus::Pending)
            ->where('request_type', LeaveRequestType::PaidLeave)
            ->count();

        $workingNow = TimeEntry::query()
            ->where('tenant_id', $tenantId)
            ->where('status', TimeEntryStatus::Incomplete)
            ->whereDate('work_date', today())
            ->distinct('user_id')
            ->count('user_id');

        $absentToday = User::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('leaveRequests', function ($query): void {
                $query->where('status', LeaveRequestStatus::Approved)
                    ->whereDate('start_date', '<=', today())
                    ->whereDate('end_date', '>=', today());
            })
            ->count();

        return [
            Stat::make('Solicitudes pendientes', $pendingRequests)
                ->description('Total sin revisar')
                ->icon('heroicon-o-clipboard-document-list')
                ->color($pendingRequests > 0 ? 'warning' : 'success'),

            Stat::make('Vacaciones pendientes', $pendingVacations)
                ->description('Sin aprobar')
                ->icon('heroicon-o-sun')
                ->color($pendingVacations > 0 ? 'warning' : 'success'),

            Stat::make('Permisos pendientes', $pendingLeaves)
                ->description('Sin aprobar')
                ->icon('heroicon-o-calendar-days')
                ->color($pendingLeaves > 0 ? 'warning' : 'success'),

            Stat::make('Trabajando ahora', $workingNow)
                ->description('Fichajes activos hoy')
                ->icon('heroicon-o-clock')
                ->color('success'),

            Stat::make('Ausentes hoy', $absentToday)
                ->description('Con solicitud aprobada')
                ->icon('heroicon-o-user-minus')
                ->color($absentToday > 0 ? 'info' : 'gray'),
        ];
    }
}
