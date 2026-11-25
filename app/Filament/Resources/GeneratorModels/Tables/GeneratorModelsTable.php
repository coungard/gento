<?php

namespace App\Filament\Resources\GeneratorModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GeneratorModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('brand'))
            ->columns([
                TextColumn::make('brand.name')
                    ->label('Бренд')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Модель')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('power_kva')
                    ->label('кВА')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('engine')
                    ->label('Двигатель')
                    ->toggleable(),
                TextColumn::make('parts_count')
                    ->label('Запчастей')
                    ->counts('parts')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('brand_id')
                    ->label('Бренд')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
