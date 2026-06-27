<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\DocumentCategory;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DocumentInfolist
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
                TextEntry::make('category')
                    ->label('Categoria')
                    ->formatStateUsing(fn (DocumentCategory|string $state): string => ($state instanceof DocumentCategory ? $state : DocumentCategory::from($state))->label()),
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('description')
                    ->label('Descripcion')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('file_path')
                    ->label('Ruta de archivo')
                    ->columnSpanFull(),
                TextEntry::make('mime_type')
                    ->label('Tipo MIME'),
                TextEntry::make('file_size')
                    ->label('Tamano (bytes)')
                    ->numeric(),
                TextEntry::make('uploaded_at')
                    ->label('Fecha de subida')
                    ->dateTime(),
                IconEntry::make('is_visible_to_employee')
                    ->label('Visible para empleado')
                    ->boolean(),
            ])
            ->columns(2);
    }
}
