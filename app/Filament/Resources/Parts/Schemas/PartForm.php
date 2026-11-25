<?php

namespace App\Filament\Resources\Parts\Schemas;

use App\Enums\PartSource;
use App\Models\GeneratorModel;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Наименование')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('sku')
                            ->label('Артикул')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64),
                        TextInput::make('slug')
                            ->label('URL (slug)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label('Категория')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('brand')
                            ->label('Бренд / производитель')
                            ->maxLength(128),
                        Select::make('source')
                            ->label('Источник')
                            ->options(PartSource::options())
                            ->default(PartSource::Manual->value)
                            ->required(),
                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
                Section::make('Цена и наличие')
                    ->columns(4)
                    ->schema([
                        TextInput::make('price')
                            ->label('Цена')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->suffix('₽'),
                        TextInput::make('old_price')
                            ->label('Цена до скидки')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('₽'),
                        TextInput::make('stock')
                            ->label('Остаток')
                            ->required()
                            ->integer()
                            ->default(0)
                            ->suffix('шт'),
                        TextInput::make('unit')
                            ->label('Ед. изм.')
                            ->required()
                            ->default('шт')
                            ->maxLength(16),
                        Toggle::make('is_active')
                            ->label('Опубликовано')
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('На главной'),
                    ]),
                Section::make('Характеристики')
                    ->collapsed()
                    ->schema([
                        KeyValue::make('specs')
                            ->label('Параметры')
                            ->keyLabel('Параметр')
                            ->valueLabel('Значение')
                            ->addActionLabel('Добавить параметр')
                            ->columnSpanFull(),
                    ]),
                Section::make('Применимость')
                    ->schema([
                        Select::make('generatorModels')
                            ->label('Подходит для моделей генераторов')
                            ->relationship('generatorModels', 'name')
                            ->getOptionLabelFromRecordUsing(
                                fn (GeneratorModel $record) => $record->full_name.($record->power_kva ? " ({$record->power_kva} кВА)" : '')
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        Repeater::make('crossNumbers')
                            ->label('Кросс-номера (аналоги и OEM)')
                            ->relationship()
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Добавить номер')
                            ->schema([
                                TextInput::make('number')
                                    ->label('Номер')
                                    ->required()
                                    ->maxLength(128),
                                TextInput::make('brand')
                                    ->label('Бренд')
                                    ->maxLength(128),
                                Select::make('kind')
                                    ->label('Тип')
                                    ->options(['analog' => 'Аналог', 'oem' => 'OEM'])
                                    ->default('analog')
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('SEO')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Title')
                            ->maxLength(255),
                        TextInput::make('meta_description')
                            ->label('Description')
                            ->maxLength(512),
                    ]),
            ]);
    }
}
