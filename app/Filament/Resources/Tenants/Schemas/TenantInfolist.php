<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Models\Tenant;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la empresa')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => 'Activa',
                                'inactive' => 'Inactiva',
                                'suspended' => 'Suspendida',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'danger',
                                'suspended' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('locale')
                            ->label('Idioma'),
                        TextEntry::make('timezone')
                            ->label('Zona horaria'),
                        TextEntry::make('created_at')
                            ->label('Creada')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Licencias')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('employee_license_limit')
                            ->label('Licencias contratadas')
                            ->formatStateUsing(fn (mixed $state): string => $state === null ? 'Ilimitadas' : (string) $state),
                        TextEntry::make('employee_licenses_usage')
                            ->label('Licencias en uso')
                            ->state(fn (Tenant $record): string => $record->employee_licenses_usage),
                        TextEntry::make('employee_licenses_usage_percent')
                            ->label('Porcentaje de uso')
                            ->state(fn (Tenant $record): string => $record->employee_licenses_usage_percent === null
                                ? 'Sin límite'
                                : number_format($record->employee_licenses_usage_percent, 2).' %'),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
