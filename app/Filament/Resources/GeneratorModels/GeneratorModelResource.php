<?php

namespace App\Filament\Resources\GeneratorModels;

use App\Filament\Resources\GeneratorModels\Pages\CreateGeneratorModel;
use App\Filament\Resources\GeneratorModels\Pages\EditGeneratorModel;
use App\Filament\Resources\GeneratorModels\Pages\ListGeneratorModels;
use App\Filament\Resources\GeneratorModels\Schemas\GeneratorModelForm;
use App\Filament\Resources\GeneratorModels\Tables\GeneratorModelsTable;
use App\Models\GeneratorModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GeneratorModelResource extends Resource
{
    protected static ?string $model = GeneratorModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'модель генератора';

    protected static ?string $pluralModelLabel = 'Модели генераторов';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return GeneratorModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneratorModelsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGeneratorModels::route('/'),
            'create' => CreateGeneratorModel::route('/create'),
            'edit' => EditGeneratorModel::route('/{record}/edit'),
        ];
    }
}
