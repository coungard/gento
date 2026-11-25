<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Страница')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок')
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
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Textarea::make('excerpt')
                            ->label('Краткое описание')
                            ->rows(2)
                            ->maxLength(512)
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->label('Содержимое')
                            ->columnSpanFull(),
                        TextInput::make('sort')
                            ->label('Порядок сортировки')
                            ->integer()
                            ->default(0),
                        DateTimePicker::make('published_at')
                            ->label('Дата публикации')
                            ->seconds(false)
                            ->default(now()),
                        Toggle::make('is_active')
                            ->label('Опубликовано')
                            ->default(true),
                    ]),
                Section::make('SEO')
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Title')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label('Description')
                            ->rows(2)
                            ->maxLength(512),
                    ]),
            ]);
    }
}
