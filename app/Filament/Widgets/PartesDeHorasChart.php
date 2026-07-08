<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class PartesDeHorasChart extends ChartWidget
{
    public array $chartData = [];

    public array $chartOptions = [];

    public ?string $heading = null;

    public function mount(array $chartData = [], array $chartOptions = [], ?string $heading = null): void
    {
        $this->chartData = $chartData;
        $this->chartOptions = $chartOptions;
        $this->heading = $heading;
    }

    protected function getData(): array
    {
        return $this->chartData;
    }

    protected function getOptions(): array
    {
        return $this->chartOptions;
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
