<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\TimeEntryStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimeEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'timeEntries';

    protected static ?string $title = 'Fichajes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('work_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('check_in_time')
                    ->label('Entrada')
                    ->sortable(),
                TextColumn::make('check_out_time')
                    ->label('Salida')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label('Duracion (min)')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (TimeEntryStatus|string $state): string => ($state instanceof TimeEntryStatus ? $state : TimeEntryStatus::from($state))->label())
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(TimeEntryStatus::options()),
                Filter::make('work_date')
                    ->label('Rango de fechas')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Desde'),
                        DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('work_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('work_date', '<=', $date));
                    }),
                Filter::make('open_entries')
                    ->label('Solo incompletos')
                    ->query(fn (Builder $query): Builder => $query->where('status', TimeEntryStatus::Incomplete->value)),
            ])
            ->defaultSort('work_date', 'desc');
    }
}
