<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\DocumentFolder;
use App\Models\Document;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                Select::make('tenant_id')
                    ->label('Empresa')
                    ->options(fn (): array => Tenant::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->default(fn (): mixed => Auth::user()?->tenant_id)
                    ->disabled(fn (): bool => ! self::currentUserIsSuperAdmin())
                    ->dehydrated()
                    ->live()
                    ->required(),
                Select::make('user_id')
                    ->label('Usuario')
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
                FileUpload::make('file_path')
                    ->label('Archivo')
                    ->disk(Document::STORAGE_DISK)
                    ->directory('tmp/documents')
                    ->preserveFilenames()
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.oasis.opendocument.text',
                    ])
                    ->maxSize(20480)
                    ->helperText('Maximo 20 MB por documento.')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->columnSpanFull(),
                DateTimePicker::make('uploaded_at')
                    ->label('Fecha de subida')
                    ->disabled()
                    ->dehydrated(false)
                    ->seconds(false)
                    ->default(now()),
                Toggle::make('is_visible_to_employee')
                    ->label('Visible para empleado')
                    ->default(false),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->maxLength(2000)
                    ->columnSpanFull(),
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
                    ->label('Tamano (bytes)')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (?Model $record): bool => $record !== null),
            ])
            ->columns(2);
    }

    private static function currentUserIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isSuperAdmin();
    }
}
