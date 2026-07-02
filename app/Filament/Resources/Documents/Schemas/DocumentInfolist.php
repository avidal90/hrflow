<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\DocumentFolder;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class DocumentInfolist
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
                TextEntry::make('uploadedBy.name')
                    ->label('Subido por')
                    ->placeholder('-'),
                TextEntry::make('folder')
                    ->label('Carpeta')
                    ->formatStateUsing(fn (DocumentFolder|string $state): string => ($state instanceof DocumentFolder ? $state : DocumentFolder::from($state))->label()),
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('description')
                    ->label('Descripcion')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('original_filename')
                    ->label('Archivo original')
                    ->placeholder('-'),
                TextEntry::make('disk')
                    ->label('Disco')
                    ->placeholder('-'),
                TextEntry::make('file_path')
                    ->label('Ruta de archivo')
                    ->columnSpanFull(),
                TextEntry::make('mime_type')
                    ->label('Tipo MIME'),
                TextEntry::make('file_size')
                    ->label('Tamano')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? Number::fileSize($state) : '-'),
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
