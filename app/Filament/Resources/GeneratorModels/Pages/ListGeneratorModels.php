<?php

namespace App\Filament\Resources\GeneratorModels\Pages;

use App\Filament\Resources\GeneratorModels\GeneratorModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGeneratorModels extends ListRecords
{
    protected static string $resource = GeneratorModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
