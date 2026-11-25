<?php

namespace App\Filament\Resources\GeneratorModels\Pages;

use App\Filament\Resources\GeneratorModels\GeneratorModelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGeneratorModel extends EditRecord
{
    protected static string $resource = GeneratorModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
