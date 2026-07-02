<?php

namespace App\Filament\Resources\Tenants\Tables;

use App\Models\Tenant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('locale')
                    ->label('Idioma')
                    ->searchable(),
                TextColumn::make('timezone')
                    ->label('Zona horaria')
                    ->searchable(),
                TextColumn::make('employee_license_limit')
                    ->label('Licencias')
                    ->formatStateUsing(fn (mixed $state): string => $state === null ? 'Ilimitadas' : (string) $state)
                    ->sortable(),
                TextColumn::make('employee_licenses_usage')
                    ->label('Consumo')
                    ->state(fn (Tenant $record): string => $record->employee_licenses_usage),
                TextColumn::make('employee_licenses_usage_percent')
                    ->label('% Uso')
                    ->state(fn (Tenant $record): string => $record->employee_licenses_usage_percent === null
                        ? 'Sin limite'
                        : number_format($record->employee_licenses_usage_percent, 2).' %')
                    ->sortable(false),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
                    ]),
                SelectFilter::make('locale')
                    ->label('Idioma')
                    ->options([
                        'es' => 'Espanol',
                        'en' => 'English',
                    ]),
                SelectFilter::make('license_limit_state')
                    ->label('Licencias')
                    ->options([
                        'limited' => 'Con limite',
                        'unlimited' => 'Ilimitadas',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'limited' => $query->whereNotNull('employee_license_limit'),
                            'unlimited' => $query->whereNull('employee_license_limit'),
                            default => $query,
                        };
                    }),
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
}
