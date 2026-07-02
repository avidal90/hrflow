<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'leaveRequests';

    protected static ?string $title = 'Solicitudes';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('resolvedBy'))
            ->columns([
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
                Filter::make('pending_only')
                    ->label('Solo pendientes')
                    ->query(fn (Builder $query): Builder => $query->where('status', LeaveRequestStatus::Pending->value)),
                TrashedFilter::make(),
            ])
            ->defaultSort('start_date', 'desc');
    }
}
