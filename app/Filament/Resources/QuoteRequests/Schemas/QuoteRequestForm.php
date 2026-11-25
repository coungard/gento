<?php

namespace App\Filament\Resources\QuoteRequests\Schemas;

use App\Enums\LeadStatus;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Обработка')
                    ->columns(3)
                    ->schema([
                        TextInput::make('number')
                            ->label('Номер')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('status')
                            ->label('Статус')
                            ->options(LeadStatus::options())
                            ->default(LeadStatus::New->value)
                            ->required(),
                        DateTimePicker::make('processed_at')
                            ->label('Обработана')
                            ->seconds(false)
                            ->displayFormat('d.m.Y H:i'),
                        Textarea::make('manager_comment')
                            ->label('Комментарий менеджера')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Клиент')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Телефон')
                            ->required()
                            ->tel()
                            ->maxLength(32),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('company')
                            ->label('Компания')
                            ->maxLength(255),
                        Textarea::make('comment')
                            ->label('Комментарий клиента')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Состав заявки')
                    ->schema([
                        Repeater::make('items')
                            ->label('Позиции')
                            ->relationship()
                            ->columns(4)
                            ->defaultItems(0)
                            ->addActionLabel('Добавить позицию')
                            ->schema([
                                TextInput::make('sku')
                                    ->label('Артикул')
                                    ->maxLength(64),
                                TextInput::make('name')
                                    ->label('Наименование')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('price')
                                    ->label('Цена')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('₽'),
                                TextInput::make('qty')
                                    ->label('Кол-во')
                                    ->integer()
                                    ->default(1),
                            ]),
                        TextInput::make('total')
                            ->label('Итого')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('₽'),
                    ]),
            ]);
    }
}
