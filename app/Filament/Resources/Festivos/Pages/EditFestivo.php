<?php

namespace App\Filament\Resources\Festivos\Pages;

use App\Filament\Resources\Festivos\FestivoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFestivo extends EditRecord
{
    protected static string $resource = FestivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
