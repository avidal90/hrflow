<?php

namespace App\Filament\Resources\Festivos\Pages;

use App\Filament\Resources\Festivos\FestivoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFestivo extends ViewRecord
{
    protected static string $resource = FestivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
