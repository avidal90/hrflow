<?php

namespace App\Filament\Widgets;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\LeaveRequest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class GlobalLatestLeaveRequestsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Últimas solicitudes recibidas';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LeaveRequest::query()
                    ->with(['user', 'tenant'])
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Empleado')
                    ->searchable(),
                TextColumn::make('request_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (LeaveRequestType $state): string => $state->label())
                    ->badge(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (LeaveRequestStatus $state): string => $state->label())
                    ->badge()
                    ->color(fn (LeaveRequestStatus $state): string => match ($state) {
                        LeaveRequestStatus::Pending => 'warning',
                        LeaveRequestStatus::Approved => 'success',
                        LeaveRequestStatus::Rejected => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
