@extends('layouts.app')

@section('title', 'Запчасти для генераторов — каталог, подбор по номеру и модели')

@section('content')
    <section class="relative overflow-hidden bg-slate-900">
        <div class="absolute inset-0 opacity-20" aria-hidden="true" style="background-image: radial-gradient(circle at 20% 20%, #2563eb 0, transparent 45%), radial-gradient(circle at 80% 0%, #0ea5e9 0, transparent 40%);"></div>

        <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:py-20">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-medium text-brand-200">
                    <span class="size-1.5 rounded-full bg-emerald-400"></span>
                    Подбор по артикулу и модели · отгрузка со склада
                </p>
                <h1 class="mt-5 text-3xl font-bold leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Запчасти для генераторов любых брендов
                </h1>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-300">
                    Фильтры, форсунки, ТНВД, датчики и расходники для дизельных электростанций. Подбираем по каталожному номеру, модели генератора или VIN двигателя.
                </p>

                <form action="{{ route('search') }}" method="get" role="search" class="mt-7 max-w-xl">
                    <label for="hero-search" class="sr-only">Поиск запчасти по артикулу или названию</label>
                    <div class="flex flex-col gap-2 rounded-xl bg-white p-2 shadow-lg shadow-slate-950/30 sm:flex-row">
                        <input id="hero-search" type="search" name="q" placeholder="Введите артикул, например 4930152" class="border-0 bg-transparent shadow-none focus:ring-0" required>
                        <button type="submit" class="btn-primary shrink-0">Найти запчасть</button>
                    </div>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                    <span>Популярное:</span>
                    @foreach (['фильтр', 'Cummins', 'Perkins', 'датчик'] as $hint)
                        <a href="{{ route('search', ['q' => $hint]) }}" class="rounded-full border border-white/15 px-2.5 py-1 transition hover:border-brand-400 hover:text-white">{{ $hint }}</a>
                    @endforeach
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-3">
                @foreach ([
                    ['Подбор по модели', 'Сверяем совместимость с генератором перед отгрузкой'],
                    ['Наличие на складе', 'Отправляем в день оплаты по всей России'],
                    ['Оригинал и аналоги', 'Показываем кросс-номера для замены'],
                    ['Гарантия 12 месяцев', 'Обмен при заводском дефекте'],
                ] as [$title, $text])
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <dt class="text-sm font-semibold text-white">{{ $title }}</dt>
                        <dd class="mt-1 text-xs leading-relaxed text-slate-400">{{ $text }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4">
        <section class="py-14" aria-labelledby="categories-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="categories-heading" class="text-2xl font-bold tracking-tight text-slate-900">Категории запчастей</h2>
                    <p class="mt-1 text-sm text-slate-600">Выберите узел — внутри разделы по типам деталей и брендам.</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800">Весь каталог →</a>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('catalog.category', $category) }}" class="group rounded-xl border border-slate-200 bg-white p-5 transition hover:border-brand-300 hover:shadow-lg hover:shadow-slate-200/60">
                        <span class="grid size-10 place-items-center rounded-lg bg-brand-50 text-brand-700">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 2v4M12 18v4M2 12h4M18 12h4M5 5l3 3M16 16l3 3M19 5l-3 3M8 16l-3 3" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </span>
                        <h3 class="mt-3 font-semibold text-slate-900 group-hover:text-brand-700">{{ $category->name }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $category->parts_count }} позиций</p>
                        @if ($category->children->isNotEmpty())
                            <p class="mt-3 text-xs leading-relaxed text-slate-500">{{ $category->children->take(3)->pluck('name')->join(' · ') }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        <section class="border-t border-slate-200 py-14" aria-labelledby="featured-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="featured-heading" class="text-2xl font-bold tracking-tight text-slate-900">Хиты продаж</h2>
                    <p class="mt-1 text-sm text-slate-600">Позиции, которые чаще всего заказывают для планового ТО.</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800">Смотреть каталог →</a>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featured as $part)
                    <x-part-card :part="$part" />
                @endforeach
            </div>
        </section>

        <section class="border-t border-slate-200 py-14" aria-labelledby="steps-heading">
            <h2 id="steps-heading" class="text-2xl font-bold tracking-tight text-slate-900">Как оформить заказ</h2>
            <ol class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['1', 'Соберите заявку', 'Добавьте нужные позиции в заявку — регистрация не требуется.'],
                    ['2', 'Сверяем совместимость', 'Менеджер проверяет артикулы по модели генератора и подтверждает наличие.'],
                    ['3', 'Оплата и отгрузка', 'Выставляем счёт, отправляем транспортной компанией или со склада.'],
                ] as [$step, $title, $text])
                    <li class="rounded-xl border border-slate-200 bg-white p-5">
                        <span class="grid size-8 place-items-center rounded-full bg-slate-900 text-sm font-bold text-white">{{ $step }}</span>
                        <h3 class="mt-3 font-semibold text-slate-900">{{ $title }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="py-14" aria-labelledby="new-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="new-heading" class="text-2xl font-bold tracking-tight text-slate-900">Новые поступления</h2>
                    <p class="mt-1 text-sm text-slate-600">Только что добавили в каталог.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($latest as $part)
                    <x-part-card :part="$part" />
                @endforeach
            </div>
        </section>

        <section class="mb-4 overflow-hidden rounded-2xl bg-brand-700 px-6 py-10 sm:px-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-white">Не нашли нужную запчасть?</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-brand-100">Отправьте артикул или фото шильдика генератора — подберём аналог и посчитаем стоимость с доставкой.</p>
                </div>
                <div class="flex shrink-0 flex-col gap-3 sm:flex-row">
                    <a href="{{ route('quote.index') }}" class="btn-secondary">Оставить заявку</a>
                    <a href="tel:{{ config('site.phone_href') }}" class="btn-primary bg-white/10 ring-1 ring-inset ring-white/30 hover:bg-white/20">{{ config('site.phone') }}</a>
                </div>
            </div>
        </section>
    </div>
@endsection
