<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del departamento')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('tenant.name')
                            ->label('Empresa'),
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('manager.name')
                            ->label('Responsable')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Creado')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Actualizado')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
