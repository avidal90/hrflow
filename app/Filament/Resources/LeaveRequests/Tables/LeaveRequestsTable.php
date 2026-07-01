<?php

namespace App\Filament\Resources\LeaveRequests\Tables;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LeaveRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->visible(fn (): bool => self::currentUserIsSuperAdmin())
                    ->searchable(),
                TextColumn::make('user.employee_code')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('request_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (LeaveRequestType|string $state): string => ($state instanceof LeaveRequestType ? $state : LeaveRequestType::from($state))->label())
                    ->badge(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (LeaveRequestStatus|string $state): string => ($state instanceof LeaveRequestStatus ? $state : LeaveRequestStatus::from($state))->label())
                    ->badge(),
                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date()
                    ->sortable(),
                TextColumn::make('resolvedBy.name')
                    ->label('Resuelto por')
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(LeaveRequestStatus::options()),
                SelectFilter::make('request_type')
                    ->label('Tipo')
                    ->options(LeaveRequestType::options()),
                TrashedFilter::make(),
            ])
            ->defaultSort('start_date', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isSuperAdmin();
    }
}
