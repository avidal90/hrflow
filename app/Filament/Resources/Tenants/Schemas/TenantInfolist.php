<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Models\Tenant;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TenantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('status')
                    ->label('Estado'),
                TextEntry::make('locale')
                    ->label('Idioma'),
                TextEntry::make('timezone')
                    ->label('Zona horaria'),
                TextEntry::make('employee_license_limit')
                    ->label('Licencias de empleados')
                    ->formatStateUsing(fn (mixed $state): string => $state === null ? 'Ilimitadas' : (string) $state),
                TextEntry::make('employee_licenses_usage')
                    ->label('Consumo de licencias')
                    ->state(fn (Tenant $record): string => $record->employee_licenses_usage),
                TextEntry::make('employee_licenses_usage_percent')
                    ->label('Porcentaje de uso')
                    ->state(fn (Tenant $record): string => $record->employee_licenses_usage_percent === null
                        ? 'Sin limite'
                        : number_format($record->employee_licenses_usage_percent, 2).' %'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->columns(2);
    }
}
