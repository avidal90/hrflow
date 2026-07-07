<?php

namespace App\Filament\Widgets;

use App\Enums\LeaveRequestStatus;
use App\Enums\TimeEntryStatus;
use App\Models\LeaveRequest;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperAdminStatsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalTenants = Tenant::query()
            ->where('id', '!=', Tenant::principalTenantId())
            ->count();

        $totalUsers = User::query()
            ->whereNotNull('tenant_id')
            ->count();

        $pendingRequests = LeaveRequest::query()
            ->where('status', LeaveRequestStatus::Pending)
            ->count();

        $activeTimeEntries = TimeEntry::query()
            ->where('status', TimeEntryStatus::Incomplete)
            ->whereDate('work_date', today())
            ->count();

        return [
            Stat::make('Empresas', $totalTenants)
                ->description('Total registradas en el sistema')
                ->icon('heroicon-o-building-office-2')
                ->color('primary'),

            Stat::make('Usuarios', $totalUsers)
                ->description('Total registrados')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Solicitudes pendientes', $pendingRequests)
                ->description('Sin revisar en el sistema')
                ->icon('heroicon-o-clipboard-document-list')
                ->color($pendingRequests > 0 ? 'warning' : 'success'),

            Stat::make('Fichajes activos', $activeTimeEntries)
                ->description('Empleados trabajando ahora mismo')
                ->icon('heroicon-o-clock')
                ->color('success'),
        ];
    }
}
