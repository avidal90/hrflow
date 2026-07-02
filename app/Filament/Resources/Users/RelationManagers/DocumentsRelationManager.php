<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\DocumentFolder;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documentos';

    public function isReadOnly(): bool
    {
        if (is_subclass_of($this->getPageClass(), ViewRecord::class)) {
            return false;
        }

        return parent::isReadOnly();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_visible_to_employee')
                    ->label('Visible para empleado')
                    ->default(false),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('uploadedBy'))
            ->columns([
                TextColumn::make('folder')
                    ->label('Carpeta')
                    ->formatStateUsing(fn (DocumentFolder|string $state): string => ($state instanceof DocumentFolder ? $state : DocumentFolder::from($state))->label())
                    ->badge(),
                TextColumn::make('name')
                    ->label('Nombre documento')
                    ->searchable(),
                TextColumn::make('file_size')
                    ->label('Tamano')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? Number::fileSize($state) : '-')
                    ->sortable(),
                IconColumn::make('is_visible_to_employee')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('uploadedBy.name')
                    ->label('Subido por')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('uploaded_at')
                    ->label('Subido')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('folder')
                    ->label('Carpeta')
                    ->options(DocumentFolder::options()),
                TernaryFilter::make('is_visible_to_employee')
                    ->label('Visible para empleado'),
                TrashedFilter::make(),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Añadir documento')
                    ->mutateFormDataUsing(fn (array $data): array => $this->mutateFormDataBeforeCreate($data)),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Descargar')
                    ->action(fn (Document $record) => response()->download(
                        Storage::disk($record->disk ?: Document::STORAGE_DISK)->path($record->file_path),
                        $record->original_filename ?: basename($record->file_path),
                    )),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mutateFormDataBeforeCreate(array $data): array
    {
        $ownerRecord = $this->getOwnerRecord();
        $tenantId = (string) $ownerRecord->tenant_id;
        $userId = $ownerRecord->getKey();

        $data['tenant_id'] = $tenantId;
        $data['user_id'] = $userId;
        $data['disk'] = Document::STORAGE_DISK;
        $data['uploaded_by_user_id'] = Auth::id();
        $data['original_filename'] = basename((string) $data['file_path']);
        $data['file_path'] = $this->moveUploadedFileToFinalPath(
            $tenantId,
            $userId,
            (string) $data['folder'],
            (string) $data['file_path'],
            (string) $data['original_filename'],
        );

        $storage = Storage::disk(Document::STORAGE_DISK);

        $data['mime_type'] = mime_content_type($storage->path($data['file_path'])) ?: 'application/octet-stream';
        $data['file_size'] = $storage->size($data['file_path']);
        $data['uploaded_at'] = now();

        return $data;
    }

    private function moveUploadedFileToFinalPath(string $tenantId, int|string $userId, string $folder, string $currentPath, string $originalFilename): string
    {
        $finalPath = Document::buildStoragePath($tenantId, $userId, $folder, $originalFilename);
        $storage = Storage::disk(Document::STORAGE_DISK);

        if ($currentPath !== $finalPath) {
            $storage->move($currentPath, $finalPath);
        }

        return $finalPath;
    }
}
