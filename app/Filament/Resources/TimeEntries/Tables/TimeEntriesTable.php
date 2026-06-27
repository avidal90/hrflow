<?php

namespace App\Filament\Resources\TimeEntries\Tables;

use App\Enums\TimeEntryStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TimeEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin())
                    ->searchable(),
                TextColumn::make('employee.employee_code')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.first_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
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
                Filter::make('open_entries')
                    ->label('Solo incompletos')
                    ->query(fn (Builder $query): Builder => $query->where('status', TimeEntryStatus::Incomplete->value)),
            ])
            ->defaultSort('work_date', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User && $user->isSuperAdmin();
    }
}
