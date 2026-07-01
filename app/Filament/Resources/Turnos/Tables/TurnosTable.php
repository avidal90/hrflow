<?php

namespace App\Filament\Resources\Turnos\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TurnosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin())
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('start_time')
                    ->label('Inicio')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Fin')
                    ->sortable(),
                TextColumn::make('break_minutes')
                    ->label('Descanso (min)')
                    ->sortable(),
                TextColumn::make('total_hours')
                    ->label('Horas jornada')
                    ->sortable(),
                TextColumn::make('turnoAssignments_count')
                    ->label('Asignaciones')
                    ->counts('turnoAssignments')
                    ->sortable(),
            ])
            ->defaultSort('name')
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

        return $user instanceof User && $user->isSuperAdmin();
    }
}
