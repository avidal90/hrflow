<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $actingUser = Auth::user();

        if ($actingUser instanceof User && ! $actingUser->isSuperAdmin()) {
            $data['tenant_id'] = (string) $actingUser->tenant_id;
        }

        $this->ensureUserBelongsToTenant($data);

        $data['disk'] = Document::STORAGE_DISK;
        $data['uploaded_by_user_id'] = Auth::id();
        $data['original_filename'] = basename($data['file_path']);
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

    /**
     * @param  array{user_id: int|string, tenant_id: int|string}  $data
     */
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
