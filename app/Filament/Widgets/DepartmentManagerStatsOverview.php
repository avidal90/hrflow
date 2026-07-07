<?php

namespace App\Filament\Widgets;

use App\Enums\LeaveRequestStatus;
use App\Enums\TimeEntryStatus;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\TimeEntry;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DepartmentManagerStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        /** @var User $user */
        $user = Auth::user();

        $departmentIds = Department::query()
            ->where('manager_user_id', $user->id)
            ->pluck('id');

        $departmentEmployees = User::query()
            ->whereIn('department_id', $departmentIds)
            ->count();

        $departmentUserIds = User::query()
            ->whereIn('department_id', $departmentIds)
            ->pluck('id');

        $pendingRequests = LeaveRequest::query()
            ->whereIn('user_id', $departmentUserIds)
            ->where('status', LeaveRequestStatus::Pending)
            ->count();

        $workingNow = TimeEntry::query()
            ->whereIn('user_id', $departmentUserIds)
            ->where('status', TimeEntryStatus::Incomplete)
            ->whereDate('work_date', today())
            ->distinct('user_id')
            ->count('user_id');

        $absentToday = User::query()
            ->whereIn('id', $departmentUserIds)
            ->whereHas('leaveRequests', function ($query): void {
                $query->where('status', LeaveRequestStatus::Approved)
                    ->whereDate('start_date', '<=', today())
                    ->whereDate('end_date', '>=', today());
            })
            ->count();

        return [
            Stat::make('Empleados del departamento', $departmentEmployees)
                ->description('Total en tu departamento')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Solicitudes pendientes', $pendingRequests)
                ->description('Sin revisar en tu departamento')
                ->icon('heroicon-o-clipboard-document-list')
                ->color($pendingRequests > 0 ? 'warning' : 'success'),

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
