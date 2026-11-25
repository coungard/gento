<?php

namespace App\Filament\Resources\Parts\Tables;

use App\Enums\PartSource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('brand')
                    ->label('Бренд')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB', divideBy: 1)
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Остаток')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('source')
                    ->label('Источник')
                    ->badge()
                    ->formatStateUsing(fn (PartSource $state): string => $state->label())
                    ->toggleable(),
                TextColumn::make('cross_numbers_count')
                    ->label('Кросс-номера')
                    ->counts('crossNumbers')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Опубликовано')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label('На главной')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('source')
                    ->label('Источник')
                    ->options(PartSource::options()),
                TernaryFilter::make('is_active')
                    ->label('Опубликовано'),
                TernaryFilter::make('is_featured')
                    ->label('На главной'),
                TernaryFilter::make('stock')
                    ->label('В наличии')
                    ->queries(
                        true: fn ($query) => $query->where('stock', '>', 0),
                        false: fn ($query) => $query->where('stock', '<=', 0),
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
