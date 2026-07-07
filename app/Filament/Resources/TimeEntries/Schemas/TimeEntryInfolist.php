<?php

namespace App\Filament\Resources\TimeEntries\Schemas;

use App\Enums\TimeEntryStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimeEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del fichaje')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('tenant.name')
                            ->label('Empresa'),
                        TextEntry::make('user.employee_code')
                            ->label('Código empleado')
                            ->placeholder('-'),
                        TextEntry::make('user.name')
                            ->label('Nombre'),
                        TextEntry::make('work_date')
                            ->label('Fecha')
                            ->date('d/m/Y'),
                        TextEntry::make('check_in_time')
                            ->label('Hora entrada'),
                        TextEntry::make('check_out_time')
                            ->label('Hora salida')
                            ->placeholder('-'),
                        TextEntry::make('duration_minutes')
                            ->label('Duración (min)')
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (TimeEntryStatus|string $state): string => ($state instanceof TimeEntryStatus ? $state : TimeEntryStatus::from($state))->label())
                            ->color(fn (TimeEntryStatus|string $state): string => ($state instanceof TimeEntryStatus ? $state : TimeEntryStatus::from($state)) === TimeEntryStatus::Complete ? 'success' : 'warning'),
                        TextEntry::make('notes')
                            ->label('Observaciones')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
