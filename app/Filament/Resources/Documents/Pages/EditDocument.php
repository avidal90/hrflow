<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected ?string $previousFileDisk = null;

    protected ?string $previousFilePath = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Descargar')
                ->action(fn () => response()->download(
                    Storage::disk($this->record->disk ?: Document::STORAGE_DISK)->path($this->record->file_path),
                    $this->record->original_filename ?: basename($this->record->file_path),
                )),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->ensureUserBelongsToTenant($data);

        if (($data['file_path'] ?? null) === $this->record->file_path) {
            return $data;
        }

        $this->previousFileDisk = $this->record->disk;
        $this->previousFilePath = $this->record->file_path;

        $data['disk'] = Document::STORAGE_DISK;
        $data['uploaded_by_user_id'] = Auth::id();
        $data['original_filename'] = basename((string) $data['file_path']);
        $data['file_path'] = $this->moveUploadedFileToFinalPath(
            $data['tenant_id'],
            $data['user_id'],
            $data['folder'],
            $data['file_path'],
            $data['original_filename'],
        );

        $storage = Storage::disk(Document::STORAGE_DISK);

        $data['mime_type'] = $storage->mimeType($data['file_path']) ?: 'application/octet-stream';
        $data['file_size'] = $storage->size($data['file_path']);
        $data['uploaded_at'] = now();

        return $data;
    }

    protected function afterSave(): void
    {
        Document::deleteStoredFile($this->previousFileDisk, $this->previousFilePath);

        $this->previousFileDisk = null;
        $this->previousFilePath = null;
    }

    private function ensureUserBelongsToTenant(array $data): void
    {
        $userBelongsToTenant = User::query()
            ->whereKey($data['user_id'])
            ->where('tenant_id', $data['tenant_id'])
            ->exists();

        if ($userBelongsToTenant) {
            return;
        }

        throw ValidationException::withMessages([
            'user_id' => 'El usuario seleccionado no pertenece a la empresa indicada.',
        ]);
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
