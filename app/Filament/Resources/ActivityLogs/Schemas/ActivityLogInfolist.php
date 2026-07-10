<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use App\Models\Activity;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información general')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('causer.name')
                            ->label('Usuario')
                            ->placeholder('Sistema'),
                        TextEntry::make('tenant.name')
                            ->label('Empresa')
                            ->placeholder('-'),
                        TextEntry::make('event')
                            ->label('Evento')
                            ->badge()
                            ->formatStateUsing(fn (Activity $record): string => $record->eventLabel())
                            ->color(fn (Activity $record): string => $record->eventColor()),
                        TextEntry::make('subject_type')
                            ->label('Modelo')
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
                        TextEntry::make('subject_id')
                            ->label('Registro afectado')
                            ->formatStateUsing(fn (Activity $record): string => $record->subjectLabel()),
                        TextEntry::make('created_at')
                            ->label('Fecha y hora')
                            ->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('ip_address')
                            ->label('Dirección IP')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Valores anteriores')
                            ->columnSpan(1)
                            ->visible(fn (Activity $record): bool => filled($record->properties?->get('old')))
                            ->schema([
                                KeyValueEntry::make('properties.old')
                                    ->label('')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Valores nuevos')
                            ->columnSpan(1)
                            ->visible(fn (Activity $record): bool => filled($record->properties?->get('attributes')))
                            ->schema([
                                KeyValueEntry::make('properties.attributes')
                                    ->label('')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Datos del registro')
                    ->columnSpanFull()
                    ->visible(fn (Activity $record): bool => blank($record->properties?->get('old')) && filled($record->properties?->all()))
                    ->schema([
                        KeyValueEntry::make('properties')
                            ->label('')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
