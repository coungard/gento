@extends('layouts.app')

@section('title', $part->name.' — купить для генератора')
@section('meta_description', \Illuminate\Support\Str::limit($part->meta_description ?: $part->name.' (арт. '.$part->sku.'). Наличие, цена и совместимость с генераторами.', 160))

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
                @if ($part->category)
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('catalog.category', $part->category) }}" class="hover:text-brand-700">{{ $part->category->name }}</a></li>
                @endif
                <li aria-hidden="true">/</li>
                <li class="text-slate-700">{{ $part->name }}</li>
            </ol>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1.1fr_1fr]">
            <div class="space-y-4">
                <div class="grid aspect-[4/3] place-items-center rounded-2xl border border-slate-200 bg-white">
                    <div class="text-center">
                        <svg class="mx-auto size-16 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <circle cx="8.5" cy="10" r="1.5" />
                            <path d="m21 16-5-5-9 8" />
                        </svg>
                        <p class="mt-3 text-xs text-slate-400">Фото уточняйте у менеджера</p>
                    </div>
                </div>

                @if ($part->generatorModels->isNotEmpty())
                    <section class="rounded-xl border border-slate-200 bg-white p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Применяемость</h2>
                        <ul class="mt-3 divide-y divide-slate-100 text-sm">
                            @foreach ($part->generatorModels as $model)
                                <li class="flex flex-wrap items-baseline justify-between gap-2 py-2">
                                    <span class="font-medium text-slate-800">
                                        {{ $model->brand?->name }} {{ $model->name }}
                                    </span>
                                    @if ($model->pivot->note)
                                        <span class="text-xs text-slate-500">{{ $model->pivot->note }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($part->crossNumbers->isNotEmpty())
                    <section class="rounded-xl border border-slate-200 bg-white p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Кросс-номера и аналоги</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($part->crossNumbers as $cross)
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-700">
                                    {{ $cross->number }}
                                    @if ($cross->brand)
                                        <span class="text-slate-400">{{ $cross->brand }}</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <div>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Категория и бренд --}}
                    @if ($part->category)
                        <a href="{{ route('catalog.category', $part->category) }}" class="badge bg-brand-50 text-brand-700">{{ $part->category->name }}</a>
                    @endif
                    @if ($part->brand)
                        <span class="badge bg-slate-100 text-slate-600">{{ $part->brand }}</span>
                    @endif
                    @if ($part->source)
                        <span class="badge bg-slate-100 text-slate-500">{{ $part->source instanceof \App\Enums\PartSource ? $part->source->label() : $part->source }}</span>
                    @endif
                </div>

                <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $part->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Артикул: <span class="font-mono font-medium text-slate-800">{{ $part->sku }}</span></p>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex flex-wrap items-end gap-3">
                        <p class="text-3xl font-bold text-slate-900">{{ $part->price_formatted }}</p>
                        @if ($part->old_price)
                            <p class="text-base text-slate-400 line-through">{{ $part->old_price_formatted }}</p>
                            <span class="badge bg-amber-50 text-amber-700">-{{ $part->discount_percent }}%</span>
                        @endif
                    </div>

                    <p class="mt-2 text-sm">
                        @if ($part->in_stock)
                            <span class="inline-flex items-center gap-1.5 text-emerald-700">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                В наличии: {{ $part->stock }} {{ $part->unit }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-slate-500">
                                <span class="size-1.5 rounded-full bg-slate-400"></span>
                                Под заказ — уточним срок при подтверждении
                            </span>
                        @endif
                    </p>

                    <form action="{{ route('cart.store', $part) }}" method="post" class="mt-5 flex flex-col gap-3 sm:flex-row">
                        @csrf
                        <div class="flex w-full items-center gap-2 sm:w-auto">
                            <label for="qty" class="sr-only">Количество</label>
                            <input id="qty" type="number" name="qty" value="1" min="1" max="999" step="1" class="w-24 text-center" inputmode="numeric">
                            <span class="text-sm text-slate-500">{{ $part->unit }}</span>
                        </div>
                        <button type="submit" class="btn-primary flex-1">Добавить в заявку</button>
                    </form>

                    <p class="mt-3 text-xs leading-relaxed text-slate-500">
                        Кнопка «Добавить в заявку» не создаёт оплату: менеджер подтвердит цену, наличие и доставку.
                    </p>
                </div>

                <ul class="mt-4 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-brand-500"></span> Гарантия 12 месяцев</li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-brand-500"></span> Отгрузка в день оплаты</li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-brand-500"></span> Проверка совместимости</li>
                    <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-brand-500"></span> Доставка по России</li>
                </ul>

                @if (! empty($part->specs))
                    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Характеристики</h2>
                        <dl class="mt-3 divide-y divide-slate-100 text-sm">
                            @foreach ($part->specs as $key => $value)
                                <div class="flex items-start justify-between gap-4 py-2">
                                    <dt class="text-slate-500">{{ $key }}</dt>
                                    <dd class="text-right font-medium text-slate-900">{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif
            </div>
        </div>

        @if ($part->description)
            <section class="mt-10 rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-semibold text-slate-900">Описание</h2>
                <div class="rich-text mt-3">
                    {!! $part->description !!}
                </div>
            </section>
        @endif

        @if ($related->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-xl font-bold tracking-tight text-slate-900">Похожие позиции</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-part-card :part="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
