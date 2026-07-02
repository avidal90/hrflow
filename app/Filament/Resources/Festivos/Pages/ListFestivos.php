<?php

namespace App\Filament\Resources\Festivos\Pages;

use App\Filament\Resources\Festivos\FestivoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFestivos extends ListRecords
{
    protected static string $resource = FestivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
