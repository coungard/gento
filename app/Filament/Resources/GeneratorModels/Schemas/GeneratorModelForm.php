<?php

namespace App\Filament\Resources\GeneratorModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GeneratorModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('brand_id')
                            ->label('Бренд')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Модель')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('URL (slug)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('power_kva')
                            ->label('Мощность, кВА')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('engine')
                            ->label('Двигатель')
                            ->maxLength(128),
                        Textarea::make('notes')
                            ->label('Примечание')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
