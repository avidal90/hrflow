<?php

namespace App\Filament\Resources\Turnos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TurnoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant.name')
                    ->label('Empresa'),
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('start_time')
                    ->label('Hora inicio'),
                TextEntry::make('end_time')
                    ->label('Hora fin'),
                TextEntry::make('break_minutes')
                    ->label('Descanso (min)'),
                TextEntry::make('total_hours')
                    ->label('Horas jornada'),
                TextEntry::make('turnoAssignments_count')
                    ->label('Asignaciones'),
            ])
            ->columns(2);
    }
}
