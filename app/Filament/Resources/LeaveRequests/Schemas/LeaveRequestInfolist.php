<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LeaveRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tenant.name')
                    ->label('Empresa'),
                TextEntry::make('employee.employee_code')
                    ->label('Codigo empleado'),
                TextEntry::make('employee.first_name')
                    ->label('Nombre empleado'),
                TextEntry::make('request_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (LeaveRequestType|string $state): string => ($state instanceof LeaveRequestType ? $state : LeaveRequestType::from($state))->label()),
                TextEntry::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (LeaveRequestStatus|string $state): string => ($state instanceof LeaveRequestStatus ? $state : LeaveRequestStatus::from($state))->label()),
                TextEntry::make('start_date')
                    ->label('Fecha inicio')
                    ->date(),
                TextEntry::make('end_date')
                    ->label('Fecha fin')
                    ->date(),
                TextEntry::make('reason')
                    ->label('Motivo')
                    ->columnSpanFull(),
                TextEntry::make('resolvedBy.name')
                    ->label('Responsable')
                    ->placeholder('-'),
                TextEntry::make('resolved_at')
                    ->label('Fecha resolucion')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('manager_comment')
                    ->label('Comentario del responsable')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
