<x-filament-panels::page>
    {{-- Filtros --}}
    <x-filament::section>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Empleado
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="selectedUserId">
                        <option value="">— Selecciona un empleado —</option>
                        @foreach ($this->getEmployeeOptions() as $id => $name)
                            <option value="{{ $id }}" @selected($this->selectedUserId == $id)>
                                {{ $name }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Año
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="year">
                        @foreach ($this->getYearOptions() as $value => $label)
                            <option value="{{ $value }}" @selected($this->year == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Mes
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="month">
                        @foreach ($this->getMonthOptions() as $value => $label)
                            <option value="{{ $value }}" @selected($this->month == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>
    </x-filament::section>

    {{-- Grafico de barras diario --}}
    <x-filament::section>
        <x-slot name="heading">
            @if ($this->selectedUserId)
                @php
                    $monthNames = $this->getMonthOptions();
                    $employeeOptions = $this->getEmployeeOptions();
                @endphp
                Horas diarias —
                {{ $employeeOptions[$this->selectedUserId] ?? '' }},
                {{ $monthNames[(string) $this->month] ?? '' }} {{ $this->year }}
            @else
                Horas diarias
            @endif
        </x-slot>

        @if (!$this->selectedUserId)
            <div class="flex items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                <div class="text-center">
                    <x-filament::icon icon="heroicon-o-user-group" class="mx-auto mb-3 h-10 w-10" />
                    <p class="text-sm">Selecciona un empleado para visualizar su parte de horas.</p>
                </div>
            </div>
        @else
            <livewire:app.filament.widgets.partes-de-horas-chart :chart-data="$this->getChartData()" :chart-options="$this->getChartOptions()" :heading="null"
                :key="'partes-chart-' . $this->selectedUserId . '-' . $this->year . '-' . $this->month" />
        @endif
    </x-filament::section>

    {{-- Resumen del mes --}}
    @if ($this->selectedUserId)
        @php $stats = $this->getMonthlyStats(); @endphp
        <x-filament::section heading="Resumen del mes">
            <livewire:app.filament.widgets.partes-de-horas-stats :total-hours="$stats['totalHours']" :worked-days="$stats['workedDays']" :daily-average="$stats['dailyAverage']"
                :key="'partes-stats-' . $this->selectedUserId . '-' . $this->year . '-' . $this->month" />
        </x-filament::section>
    @endif
</x-filament-panels::page>
