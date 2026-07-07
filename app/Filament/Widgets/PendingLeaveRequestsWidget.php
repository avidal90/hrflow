<?php

namespace App\Filament\Widgets;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class PendingLeaveRequestsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Solicitudes pendientes de revisión';

    public function table(Table $table): Table
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        return $table
            ->query(
                LeaveRequest::query()
                    ->with(['user'])
                    ->where('tenant_id', $currentUser->tenant_id)
                    ->where('status', LeaveRequestStatus::Pending)
                    ->oldest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('user.employee_code')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Empleado')
                    ->searchable(),
                TextColumn::make('request_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (LeaveRequestType $state): string => $state->label())
                    ->badge(),
                TextColumn::make('start_date')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Recibida')
                    ->since(),
            ])
            ->paginated(false);
    }
}
