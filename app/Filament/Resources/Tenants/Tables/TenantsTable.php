<?php

namespace App\Filament\Resources\Tenants\Tables;

use App\Models\Tenant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                //
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
