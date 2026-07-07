<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Solicitud')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('tenant.name')
                            ->label('Empresa'),
                        TextEntry::make('user.employee_code')
                            ->label('Código empleado')
                            ->placeholder('-'),
                        TextEntry::make('user.name')
                            ->label('Empleado'),
                        TextEntry::make('request_type')
                            ->label('Tipo')
                            ->badge()
                            ->formatStateUsing(fn (LeaveRequestType|string $state): string => ($state instanceof LeaveRequestType ? $state : LeaveRequestType::from($state))->label()),
                        TextEntry::make('start_date')
                            ->label('Fecha inicio')
                            ->date('d/m/Y'),
                        TextEntry::make('end_date')
                            ->label('Fecha fin')
                            ->date('d/m/Y'),
                        TextEntry::make('reason')
                            ->label('Motivo')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Resolución')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn (LeaveRequestStatus|string $state): string => ($state instanceof LeaveRequestStatus ? $state : LeaveRequestStatus::from($state))->label())
                            ->color(fn (LeaveRequestStatus|string $state): string => match ($state instanceof LeaveRequestStatus ? $state : LeaveRequestStatus::from($state)) {
                                LeaveRequestStatus::Pending => 'warning',
                                LeaveRequestStatus::Approved => 'success',
                                LeaveRequestStatus::Rejected => 'danger',
                            }),
                        TextEntry::make('resolvedBy.name')
                            ->label('Responsable')
                            ->placeholder('-'),
                        TextEntry::make('resolved_at')
                            ->label('Fecha resolución')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('manager_comment')
                            ->label('Comentario del responsable')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
