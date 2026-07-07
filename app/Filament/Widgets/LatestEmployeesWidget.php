<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class LatestEmployeesWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Últimas incorporaciones';

    public function table(Table $table): Table
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        return $table
            ->query(
                User::query()
                    ->where('tenant_id', $currentUser->tenant_id)
                    ->orderByDesc('hire_date')
                    ->orderByDesc('created_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('employee_code')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('job_title')
                    ->label('Puesto')
                    ->placeholder('-'),
                TextColumn::make('department.name')
                    ->label('Departamento')
                    ->placeholder('-'),
                TextColumn::make('hire_date')
                    ->label('Alta')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
