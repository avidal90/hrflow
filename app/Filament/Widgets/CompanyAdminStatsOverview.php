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

class CompanyAdminStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        /** @var User $user */
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $totalEmployees = User::query()
            ->where('tenant_id', $tenantId)
            ->count();

        $totalDepartments = Department::query()
            ->where('tenant_id', $tenantId)
            ->count();

        $pendingRequests = LeaveRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', LeaveRequestStatus::Pending)
            ->count();

        $activeTimeEntries = TimeEntry::query()
            ->where('tenant_id', $tenantId)
            ->where('status', TimeEntryStatus::Incomplete)
            ->whereDate('work_date', today())
            ->count();

        return [
            Stat::make('Empleados', $totalEmployees)
                ->description('Total en la empresa')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Departamentos', $totalDepartments)
                ->description('Activos en la empresa')
                ->icon('heroicon-o-building-office')
                ->color('info'),

            Stat::make('Solicitudes pendientes', $pendingRequests)
                ->description('Pendientes de revisión')
                ->icon('heroicon-o-clipboard-document-list')
                ->color($pendingRequests > 0 ? 'warning' : 'success'),

            Stat::make('Fichajes activos', $activeTimeEntries)
                ->description('Empleados trabajando ahora')
                ->icon('heroicon-o-clock')
                ->color('success'),
        ];
    }
}
