<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\DocumentFolder;
use App\Models\Document;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del documento')
                    ->collapsible()
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Empresa')
                            ->options(fn (): array => Tenant::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->default(fn (): mixed => Auth::user()?->tenant_id)
                            ->disabled(fn (): bool => ! self::currentUserIsSuperAdmin())
                            ->dehydrated()
                            ->live()
                            ->required(),
                        Select::make('user_id')
                            ->label('Empleado')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Get $get): void {
                                    $tenantId = $get('tenant_id');

                                    if (filled($tenantId)) {
                                        $query->where('tenant_id', $tenantId);
                                    }

                                    $query->orderBy('name');
                                },
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record): string => $record instanceof User
                                ? sprintf('%s (%s)', $record->name, $record->employee_code ?? $record->email)
                                : (string) $record->getKey())
                            ->searchable(['name', 'email', 'employee_code'])
                            ->preload()
                            ->required(),
                        Select::make('folder')
                            ->label('Carpeta')
                            ->options(DocumentFolder::options())
                            ->default(DocumentFolder::Other->value)
                            ->required(),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_visible_to_employee')
                            ->label('Visible para el empleado')
                            ->default(false),
                        FileUpload::make('file_path')
                            ->label('Archivo')
                            ->disk(Document::STORAGE_DISK)
                            ->directory('tmp/documents')
                            ->preserveFilenames()
                            ->visibility('private')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp',
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            ])
                            ->maxSize(20480)
                            ->helperText('Formatos permitidos: imágenes, PDF, DOCX, XLSX, PPTX. Máximo 20 MB.')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Información técnica')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('original_filename')
                            ->label('Nombre original')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?Model $record): bool => $record !== null),
                        TextInput::make('mime_type')
                            ->label('Tipo MIME')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?Model $record): bool => $record !== null),
                        TextInput::make('file_size')
                            ->label('Tamaño (bytes)')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?Model $record): bool => $record !== null),
                    ])
                    ->columns(2)
                    ->visible(fn (?Model $record): bool => $record !== null),
            ])
            ->columns(1);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isSuperAdmin();
    }
}
