<?php

namespace App\Filament\Resources\Documents\Pages;

use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Descargar')
                ->action(fn () => response()->download(
                    Storage::disk($this->record->disk ?: Document::STORAGE_DISK)->path($this->record->file_path),
                    $this->record->original_filename ?: basename($this->record->file_path),
                )),
            EditAction::make(),
        ];
    }
}
