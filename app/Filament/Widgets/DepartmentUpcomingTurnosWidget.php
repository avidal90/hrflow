<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\TurnoAssignment;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class DepartmentUpcomingTurnosWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Turnos activos del departamento';

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
                TurnoAssignment::query()
                    ->with(['user', 'turno'])
                    ->whereIn('user_id', $departmentUserIds)
                    ->where(function ($query): void {
                        $query->whereNull('valid_until')
                            ->orWhereDate('valid_until', '>=', today());
                    })
                    ->orderBy('valid_from')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Empleado')
                    ->searchable(),
                TextColumn::make('turno.name')
                    ->label('Turno'),
                TextColumn::make('turno.start_time')
                    ->label('Entrada')
                    ->time('H:i'),
                TextColumn::make('turno.end_time')
                    ->label('Salida')
                    ->time('H:i'),
                TextColumn::make('valid_from')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->placeholder('Indefinido'),
                TextColumn::make('valid_until')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->placeholder('Indefinido'),
            ])
            ->paginated(false);
    }
}
