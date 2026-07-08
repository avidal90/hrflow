<?php

namespace App\Filament\Pages;

use App\Enums\TimeEntryStatus;
use App\Models\TimeEntry;
use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class PartesDeHoras extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Control de tiempo';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Partes de horas';

    protected static ?string $title = 'Partes de horas';

    protected string $view = 'filament.pages.partes-de-horas';

    public ?int $selectedUserId = null;

    public int $year;

    public int $month;

    private ?Collection $cachedDailyEntries = null;

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->can('viewAny', TimeEntry::class);
    }

    public function updatedSelectedUserId(): void
    {
        $this->resetDailyEntriesCache();
        $this->dispatch('updateChartData', data: $this->getChartData());
    }

    public function updatedYear(): void
    {
        $this->resetDailyEntriesCache();
        $this->dispatch('updateChartData', data: $this->getChartData());
    }

    public function updatedMonth(): void
    {
        $this->resetDailyEntriesCache();
        $this->dispatch('updateChartData', data: $this->getChartData());
    }

    /**
     * @return array<string, mixed>
     */
    public function getChartData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Horas trabajadas',
                    'data' => $this->buildDailyHoursArray(),
                ],
            ],
            'labels' => range(1, $this->daysInSelectedMonth()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChartOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => ['display' => true, 'text' => 'Horas'],
                ],
                'x' => [
                    'title' => ['display' => true, 'text' => 'Día'],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }

    /**
     * @return array{totalHours: float, workedDays: int, dailyAverage: float}
     */
    public function getMonthlyStats(): array
    {
        $entries = $this->loadDailyEntries();

        if ($entries->isEmpty()) {
            return ['totalHours' => 0.0, 'workedDays' => 0, 'dailyAverage' => 0.0];
        }

        $totalMinutes = (int) $entries->sum('total_minutes');
        $workedDays = $entries->count();
        $totalHours = round($totalMinutes / 60, 1);
        $dailyAverage = $workedDays > 0 ? round($totalHours / $workedDays, 1) : 0.0;

        return [
            'totalHours' => $totalHours,
            'workedDays' => $workedDays,
            'dailyAverage' => $dailyAverage,
        ];
    }

    /**
     * @return array<int|string, string>
     */
    public function getEmployeeOptions(): array
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if ($authUser->isSuperAdmin()) {
            return User::query()
                ->whereNotNull('tenant_id')
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        $query = User::query()->where('tenant_id', $authUser->tenant_id);

        if ($authUser->isDepartmentManager()) {
            $query->whereHas('department', function ($q) use ($authUser): void {
                $q->where('manager_user_id', $authUser->getKey());
            });
        }

        return $query->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    public function getYearOptions(): array
    {
        $currentYear = now()->year;
        $options = [];

        for ($year = $currentYear - 2; $year <= $currentYear; $year++) {
            $options[(string) $year] = (string) $year;
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public function getMonthOptions(): array
    {
        return [
            '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo',
            '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio',
            '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre',
            '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
        ];
    }

    /**
     * @return array<int, float>
     */
    private function buildDailyHoursArray(): array
    {
        $daysInMonth = $this->daysInSelectedMonth();
        $hoursByDay = array_fill(1, $daysInMonth, 0.0);

        foreach ($this->loadDailyEntries() as $entry) {
            $day = (int) $entry->work_date->day;
            $hoursByDay[$day] = round((float) $entry->total_minutes / 60, 1);
        }

        return array_values($hoursByDay);
    }

    private function loadDailyEntries(): Collection
    {
        if ($this->cachedDailyEntries !== null) {
            return $this->cachedDailyEntries;
        }

        /** @var User $authUser */
        $authUser = Auth::user();

        if ($this->selectedUserId === null || ! $this->canAccessUserData($authUser, $this->selectedUserId)) {
            return $this->cachedDailyEntries = new Collection;
        }

        return $this->cachedDailyEntries = TimeEntry::query()
            ->visibleTo($authUser)
            ->where('user_id', $this->selectedUserId)
            ->whereYear('work_date', $this->year)
            ->whereMonth('work_date', $this->month)
            ->where('status', TimeEntryStatus::Complete)
            ->select('work_date')
            ->selectRaw('SUM(duration_minutes) as total_minutes')
            ->groupBy('work_date')
            ->get();
    }

    private function canAccessUserData(User $authUser, int $targetUserId): bool
    {
        if ($authUser->isSuperAdmin() || $authUser->isCompanyAdmin() || $authUser->isHr()) {
            return true;
        }

        if ($authUser->isDepartmentManager()) {
            return User::query()
                ->where('id', $targetUserId)
                ->where('tenant_id', $authUser->tenant_id)
                ->whereHas('department', function ($q) use ($authUser): void {
                    $q->where('manager_user_id', $authUser->getKey());
                })
                ->exists();
        }

        return false;
    }

    private function daysInSelectedMonth(): int
    {
        return Carbon::create($this->year, $this->month, 1)->daysInMonth;
    }

    private function resetDailyEntriesCache(): void
    {
        $this->cachedDailyEntries = null;
    }
}
