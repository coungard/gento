@extends('layouts.app')

@section('title', $category->name.' — запчасти для генераторов')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($category->description ?? $category->name.' — каталог запчастей для генераторов с подбором по модели.'), 160))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <nav aria-label="Хлебные крошки" class="text-xs text-slate-500">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ route('home') }}" class="hover:text-brand-700">Главная</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('catalog.index') }}" class="hover:text-brand-700">Каталог</a></li>
                @foreach ($breadcrumbs as $crumb)
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('catalog.category', $crumb) }}" class="hover:text-brand-700">{{ $crumb->name }}</a></li>
                @endforeach
                <li aria-hidden="true">/</li>
                <li class="text-slate-700">{{ $category->name }}</li>
            </ol>
        </nav>

        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ $category->name }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">
                    {{ $category->description ?: 'Запчасти раздела с указанием артикулов, совместимости и наличия на складе.' }}
                </p>
            </div>
            <p class="text-sm text-slate-500">Найдено: <span class="font-semibold text-slate-900">{{ $parts->total() }}</span></p>
        </div>

        @if ($children->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($children as $child)
                    <a href="{{ route('catalog.category', $child) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 transition hover:border-brand-300 hover:text-brand-700">
                        {{ $child->name }}
                        <span class="text-xs text-slate-400">{{ $child->parts_count }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="mt-8 grid gap-8 lg:grid-cols-[260px_1fr]">
            <aside>
                <details class="rounded-xl border border-slate-200 bg-white lg:open" open>
                    <summary class="cursor-pointer list-none px-5 py-4 text-sm font-semibold text-slate-900 lg:cursor-default">Фильтры</summary>
                    <form method="get" action="{{ route('catalog.category', $category) }}" class="space-y-4 border-t border-slate-100 px-5 py-4">
                        <div class="field">
                            <label for="filter-sort">Сортировка</label>
                            <select id="filter-sort" name="sort" data-auto-submit>
                                @foreach (['popular' => 'По популярности', 'price_asc' => 'Сначала дешевле', 'price_desc' => 'Сначала дороже', 'name' => 'По названию', 'new' => 'Сначала новые'] as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($brands->isNotEmpty())
                            <div class="field">
                                <label for="filter-brand">Бренд</label>
                                <select id="filter-brand" name="brand">
                                    <option value="">Все бренды</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand }}" @selected($filters['brand'] === $brand)>{{ $brand }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="field">
                            <span class="block text-sm font-medium text-slate-700">Цена, ₽</span>
                            <div class="flex items-center gap-2">
                                <label for="filter-price-from" class="sr-only">Цена от</label>
                                <input id="filter-price-from" type="number" name="price_from" min="0" step="1" value="{{ $filters['price_from'] ?? '' }}" placeholder="от" inputmode="numeric">
                                <span class="text-slate-400">—</span>
                                <label for="filter-price-to" class="sr-only">Цена до</label>
                                <input id="filter-price-to" type="number" name="price_to" min="0" step="1" value="{{ $filters['price_to'] ?? '' }}" placeholder="до" inputmode="numeric">
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="in_stock" value="1" class="size-4 rounded border-slate-300 text-brand-600" @checked($filters['in_stock'])>
                            Только в наличии
                        </label>

                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="btn-primary flex-1 py-2 text-xs">Применить</button>
                            <a href="{{ route('catalog.category', $category) }}" class="btn-secondary py-2 text-xs">Сброс</a>
                        </div>
                    </form>
                </details>
            </aside>

            <div>
                @if ($parts->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                        <p class="text-lg font-semibold text-slate-900">Ничего не нашлось</p>
                        <p class="mx-auto mt-2 max-w-md text-sm text-slate-600">Попробуйте изменить фильтры или отправить заявку — подберём нужную деталь вручную.</p>
                        <div class="mt-5 flex flex-wrap justify-center gap-3">
                            <a href="{{ route('catalog.category', $category) }}" class="btn-secondary">Сбросить фильтры</a>
                            <a href="{{ route('quote.index') }}" class="btn-primary">Оставить заявку</a>
                        </div>
                    </div>
                @else
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($parts as $part)
                            <x-part-card :part="$part" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $parts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
