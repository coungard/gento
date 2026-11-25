<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Models\Part;
use App\Models\QuoteRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatalogStats extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $newLeads = QuoteRequest::query()->where('status', LeadStatus::New->value)->count();
        $weekLeads = QuoteRequest::query()->where('created_at', '>=', now()->subWeek())->count();

        return [
            Stat::make('Запчастей в каталоге', (string) Part::query()->count())
                ->description('Опубликовано: '.Part::query()->where('is_active', true)->count())
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary'),
            Stat::make('Новых заявок', (string) $newLeads)
                ->description('За неделю: '.$weekLeads)
                ->icon('heroicon-o-inbox-arrow-down')
                ->color($newLeads > 0 ? 'danger' : 'success')
                ->url(fn (): string => \App\Filament\Resources\QuoteRequests\QuoteRequestResource::getUrl('index')),
            Stat::make('Без остатка', (string) Part::query()->where('is_active', true)->where('stock', '<=', 0)->count())
                ->description('Позиции под заказ')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
