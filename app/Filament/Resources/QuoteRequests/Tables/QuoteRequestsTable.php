<?php

namespace App\Filament\Resources\QuoteRequests\Tables;

use App\Enums\LeadStatus;
use App\Models\QuoteRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuoteRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Клиент')
                    ->searchable()
                    ->description(fn (QuoteRequest $record): ?string => $record->company),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (LeadStatus $state): string => $state->label())
                    ->color(fn (LeadStatus $state): string => match ($state) {
                        LeadStatus::New => 'danger',
                        LeadStatus::InProgress => 'warning',
                        LeadStatus::Quoted => 'info',
                        LeadStatus::Won => 'success',
                        LeadStatus::Lost => 'gray',
                    }),
                TextColumn::make('items_count')
                    ->label('Позиций')
                    ->counts('items'),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->money('RUB', divideBy: 1)
                    ->sortable(),
                TextColumn::make('source')
                    ->label('Источник')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(LeadStatus::options())
                    ->default(LeadStatus::New->value),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('take')
                    ->label('В работу')
                    ->icon('heroicon-o-play')
                    ->visible(fn (QuoteRequest $record): bool => $record->status === LeadStatus::New)
                    ->action(fn (QuoteRequest $record) => $record->update(['status' => LeadStatus::InProgress])),
                Action::make('won')
                    ->label('В заказ')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (QuoteRequest $record): bool => ! $record->status->isFinal())
                    ->action(fn (QuoteRequest $record) => $record->update(['status' => LeadStatus::Won, 'processed_at' => now()])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
