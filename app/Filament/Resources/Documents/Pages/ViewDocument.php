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
                ->action(function () {
                    $record = $this->getRecord();

                    if (! $record instanceof Document) {
                        return null;
                    }

                    return response()->download(
                        Storage::disk($record->disk ?: Document::STORAGE_DISK)->path($record->file_path),
                        $record->original_filename ?: basename($record->file_path),
                    );
                }),
            EditAction::make(),
        ];
    }
}
