<?php

namespace App\Filament\Resources\TimeEntries\Schemas;

use App\Enums\TimeEntryStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TimeEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant.name')
                    ->label('Empresa'),
                TextEntry::make('user.employee_code')
                    ->label('Codigo empleado')
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('Nombre usuario'),
                TextEntry::make('work_date')
                    ->label('Fecha')
                    ->date(),
                TextEntry::make('check_in_time')
                    ->label('Hora entrada'),
                TextEntry::make('check_out_time')
                    ->label('Hora salida')
                    ->placeholder('-'),
                TextEntry::make('duration_minutes')
                    ->label('Duracion (min)')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (TimeEntryStatus|string $state): string => ($state instanceof TimeEntryStatus ? $state : TimeEntryStatus::from($state))->label()),
                TextEntry::make('notes')
                    ->label('Observaciones')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
