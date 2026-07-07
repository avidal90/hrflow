<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestTenantsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Últimas empresas registradas';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tenant::query()
                    ->where('id', '!=', Tenant::principalTenantId())
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Empresa')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Registrada')
                    ->since()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
