<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'Запчасти и генераторы: подбор по модели, наличие на складе, доставка по России.')">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-full flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow">К содержанию</a>

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="hidden border-b border-slate-800/60 bg-slate-900 text-xs text-slate-300 md:block">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2">
                <p>Подбор запчастей по номеру и модели генератора · отгрузка со склада</p>
                <div class="flex items-center gap-5">
                    <span class="text-slate-400">{{ config('site.work_hours') }}</span>
                    <a href="tel:{{ config('site.phone_href') }}" class="font-semibold text-white hover:text-brand-300">{{ config('site.phone') }}</a>
                </div>
            </div>
        </div>

        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
                <span class="grid size-9 place-items-center rounded-lg bg-brand-600 text-white shadow-sm">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5Z" />
                    </svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-sm font-bold tracking-tight text-slate-900">Генераторы<span class="text-brand-600">Запчасти</span></span>
                    <span class="block text-[11px] text-slate-500">каталог и сервис</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Основная навигация">
                <a href="{{ route('catalog.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 {{ request()->routeIs('catalog.*') ? 'bg-slate-100 text-brand-700' : '' }}">Каталог</a>
                @foreach ($navPages as $navPage)
                    <a href="{{ route('pages.show', $navPage) }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 {{ request()->routeIs('pages.show') && request()->route('page')?->is($navPage) ? 'bg-slate-100 text-brand-700' : '' }}">{{ $navPage->title }}</a>
                @endforeach
            </nav>

            <form action="{{ route('search') }}" method="get" role="search" class="ml-auto hidden w-full max-w-xs md:block">
                <label for="header-search" class="sr-only">Поиск по каталогу</label>
                <div class="relative">
                    <input id="header-search" type="search" name="q" value="{{ request()->routeIs('search') ? request('q') : '' }}" placeholder="Артикул, название, модель" class="py-2 pl-9 pr-3">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3.5-3.5" stroke-linecap="round" />
                    </svg>
                </div>
            </form>

            <a href="{{ route('quote.index') }}" class="ml-auto inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 md:ml-0">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 5h2l2 11h10l2-8H7" />
                    <circle cx="9" cy="20" r="1.2" />
                    <circle cx="18" cy="20" r="1.2" />
                </svg>
                Заявка
                @if ($quoteCount > 0)
                    <span class="grid min-w-5 place-items-center rounded-full bg-brand-500 px-1.5 text-xs font-bold text-white">{{ $quoteCount }}</span>
                @endif
            </a>

            <button type="button" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav" class="inline-flex size-10 items-center justify-center rounded-lg border border-slate-300 text-slate-700 lg:hidden">
                <span class="sr-only">Меню</span>
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>

        <div id="mobile-nav" class="hidden border-t border-slate-200 bg-white lg:hidden" aria-hidden="true">
            <div class="mx-auto max-w-7xl space-y-2 px-4 py-4">
                <form action="{{ route('search') }}" method="get" role="search">
                    <label for="mobile-search" class="sr-only">Поиск по каталогу</label>
                    <input id="mobile-search" type="search" name="q" value="{{ request()->routeIs('search') ? request('q') : '' }}" placeholder="Артикул, название, модель">
                </form>
                <a href="{{ route('catalog.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-100">Каталог запчастей</a>
                @foreach ($navCategories as $navCategory)
                    <a href="{{ route('catalog.category', $navCategory) }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">{{ $navCategory->name }}</a>
                @endforeach
                <div class="border-t border-slate-200 pt-2">
                    @foreach ($navPages as $navPage)
                        <a href="{{ route('pages.show', $navPage) }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">{{ $navPage->title }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    <main id="main" class="flex-1">
        @if (session('status') || session('error') || session('lead_sent'))
            <div class="mx-auto max-w-7xl px-4 pt-6">
                @if (session('status'))
                    <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div role="alert" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('error') }}</div>
                @endif
                @if (session('lead_sent'))
                    <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('lead_sent') }}</div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-16 border-t border-slate-800 bg-slate-900 text-slate-300">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-sm font-bold text-white">Генераторы<span class="text-brand-400">Запчасти</span></p>
                <p class="mt-3 text-sm leading-relaxed text-slate-400">Запчасти и расходники для дизельных генераторов: фильтры, форсунки, насосы, датчики, элементы охлаждения. Подбираем по каталожному номеру и модели.</p>
                <a href="tel:{{ config('site.phone_href') }}" class="mt-4 block text-lg font-semibold text-white hover:text-brand-300">{{ config('site.phone') }}</a>
                <p class="mt-1 text-sm text-slate-400">{{ config('site.work_hours') }}</p>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Каталог</p>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($navCategories as $navCategory)
                        <li><a href="{{ route('catalog.category', $navCategory) }}" class="hover:text-white">{{ $navCategory->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Информация</p>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($navPages as $navPage)
                        <li><a href="{{ route('pages.show', $navPage) }}" class="hover:text-white">{{ $navPage->title }}</a></li>
                    @endforeach
                    <li><a href="{{ route('quote.index') }}" class="hover:text-white">Заявка на подбор</a></li>
                </ul>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Обратный звонок</p>
                <form action="{{ route('lead.store') }}" method="post" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="lead-name" class="sr-only">Имя</label>
                        <input id="lead-name" type="text" name="name" value="{{ old('name') }}" placeholder="Ваше имя" required>
                        @error('name')
                            <p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="lead-phone" class="sr-only">Телефон</label>
                        <input id="lead-phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="Телефон" required>
                        @error('phone')
                            <p class="mt-1 text-xs font-medium text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <input type="text" name="website" value="" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <label class="flex items-start gap-2 text-xs text-slate-400">
                        <input type="checkbox" name="consent" value="1" class="mt-0.5 size-4 rounded border-slate-600 bg-slate-800 text-brand-500" required>
                        <span>Согласен на обработку персональных данных</span>
                    </label>
                    @error('consent')
                        <p class="text-xs font-medium text-red-400">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="btn-primary w-full">Заказать звонок</button>
                </form>
            </div>
        </div>

        <div class="border-t border-slate-800">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-500 md:flex-row md:items-center md:justify-between">
                <p>© {{ now()->year }} {{ config('app.name') }}@if (config('site.legal_name') !== '') · {{ config('site.legal_name') }}@endif</p>
                <p>{{ config('site.address') }} · <a href="mailto:{{ config('site.email') }}" class="hover:text-slate-300">{{ config('site.email') }}</a></p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
