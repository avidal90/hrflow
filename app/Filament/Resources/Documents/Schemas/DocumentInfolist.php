<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\DocumentFolder;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del documento')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('tenant.name')
                            ->label('Empresa'),
                        TextEntry::make('user.employee_code')
                            ->label('Código empleado')
                            ->placeholder('-'),
                        TextEntry::make('user.name')
                            ->label('Empleado'),
                        TextEntry::make('uploadedBy.name')
                            ->label('Subido por')
                            ->placeholder('-'),
                        TextEntry::make('folder')
                            ->label('Carpeta')
                            ->badge()
                            ->formatStateUsing(fn (DocumentFolder|string $state): string => ($state instanceof DocumentFolder ? $state : DocumentFolder::from($state))->label()),
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('uploaded_at')
                            ->label('Fecha de subida')
                            ->dateTime(),
                        IconEntry::make('is_visible_to_employee')
                            ->label('Visible para empleado')
                            ->boolean(),
                        TextEntry::make('description')
                            ->label('Descripción')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Información técnica')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('original_filename')
                            ->label('Archivo original')
                            ->placeholder('-'),
                        TextEntry::make('mime_type')
                            ->label('Tipo MIME')
                            ->placeholder('-'),
                        TextEntry::make('file_size')
                            ->label('Tamaño')
                            ->formatStateUsing(fn (?int $state): string => $state !== null ? Number::fileSize($state) : '-'),
                        TextEntry::make('disk')
                            ->label('Disco')
                            ->placeholder('-'),
                        TextEntry::make('file_path')
                            ->label('Ruta de archivo')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
