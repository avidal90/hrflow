<?php

namespace App\Filament\Widgets;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class DepartmentLatestRequestsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Solicitudes pendientes del departamento';

    public function table(Table $table): Table
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $departmentUserIds = User::query()
            ->whereIn(
                'department_id',
                Department::query()
                    ->where('manager_user_id', $currentUser->id)
                    ->pluck('id')
            )
            ->pluck('id');

        return $table
            ->query(
                LeaveRequest::query()
                    ->with(['user'])
                    ->whereIn('user_id', $departmentUserIds)
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
