<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PartesDeHorasStats extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    public float $totalHours = 0.0;

    public int $workedDays = 0;

    public float $dailyAverage = 0.0;

    public function mount(float $totalHours = 0.0, int $workedDays = 0, float $dailyAverage = 0.0): void
    {
        $this->totalHours = $totalHours;
        $this->workedDays = $workedDays;
        $this->dailyAverage = $dailyAverage;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Horas totales', number_format($this->totalHours, 1).' h')
                ->description('Fichajes completados en el mes')
                ->icon('heroicon-o-clock')
                ->color('primary'),

            Stat::make('Dias trabajados', (string) $this->workedDays)
                ->description('Dias con fichaje completo')
                ->icon('heroicon-o-calendar-days')
                ->color('success'),

            Stat::make('Media diaria', number_format($this->dailyAverage, 1).' h')
                ->description('Promedio por dia trabajado')
                ->icon('heroicon-o-presentation-chart-bar')
                ->color('info'),
        ];
    }
}
