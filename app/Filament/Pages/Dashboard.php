<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CompanyAdminStatsOverview;
use App\Filament\Widgets\DepartmentLatestRequestsWidget;
use App\Filament\Widgets\DepartmentManagerStatsOverview;
use App\Filament\Widgets\DepartmentUpcomingTurnosWidget;
use App\Filament\Widgets\GlobalLatestLeaveRequestsWidget;
use App\Filament\Widgets\HrStatsOverview;
use App\Filament\Widgets\LatestDocumentsWidget;
use App\Filament\Widgets\LatestEmployeesWidget;
use App\Filament\Widgets\LatestTenantsWidget;
use App\Filament\Widgets\PendingLeaveRequestsWidget;
use App\Filament\Widgets\SuperAdminStatsOverview;
use App\Filament\Widgets\UpcomingFestivosWidget;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'Panel de control';

    public function getWidgets(): array
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return [
                SuperAdminStatsOverview::class,
                LatestTenantsWidget::class,
                GlobalLatestLeaveRequestsWidget::class,
            ];
        }

        if ($user->isCompanyAdmin()) {
            return [
                CompanyAdminStatsOverview::class,
                LatestEmployeesWidget::class,
                LatestDocumentsWidget::class,
                UpcomingFestivosWidget::class,
            ];
        }

        if ($user->isHr()) {
            return [
                HrStatsOverview::class,
                PendingLeaveRequestsWidget::class,
                LatestDocumentsWidget::class,
                UpcomingFestivosWidget::class,
            ];
        }

        if ($user->isDepartmentManager()) {
            return [
                DepartmentManagerStatsOverview::class,
                DepartmentLatestRequestsWidget::class,
                DepartmentUpcomingTurnosWidget::class,
                UpcomingFestivosWidget::class,
            ];
        }

        return [];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
