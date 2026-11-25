@extends('layouts.app')

@section('title', $term !== '' ? 'Поиск: '.$term : 'Поиск запчастей по артикулу')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <nav aria-label="Хлебные крошки" class="text-xs text-slate-500">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ route('home') }}" class="hover:text-brand-700">Главная</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('catalog.index') }}" class="hover:text-brand-700">Каталог</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-slate-700">Поиск</li>
            </ol>
        </nav>

        <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Поиск по каталогу</h1>

        <form action="{{ route('search') }}" method="get" role="search" class="mt-5 max-w-2xl">
            <label for="search-q" class="sr-only">Артикул, название или модель генератора</label>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input id="search-q" type="search" name="q" value="{{ $term }}" placeholder="Например, 4930152 или масляный фильтр" required>
                <button type="submit" class="btn-primary shrink-0">Найти</button>
            </div>
        </form>

        @if ($parts === null)
            <div class="mt-8 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <p class="text-lg font-semibold text-slate-900">Введите артикул или название</p>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-slate-600">
                    Поиск работает по названию, артикулу и кросс-номерам. Если не уверены в написании — пришлите фото шильдика, подберём вручную.
                </p>
                <a href="{{ route('quote.index') }}" class="btn-primary mt-5">Отправить заявку на подбор</a>
            </div>
        @elseif ($parts->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <p class="text-lg font-semibold text-slate-900">По запросу «{{ $term }}» ничего не найдено</p>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-slate-600">
                    Проверьте написание артикула или отправьте заявку — менеджер подберёт запчасть по модели генератора.
                </p>
                <div class="mt-5 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('catalog.index') }}" class="btn-secondary">Перейти в каталог</a>
                    <a href="{{ route('quote.index') }}" class="btn-primary">Оставить заявку</a>
                </div>
            </div>
        @else
            <p class="mt-6 text-sm text-slate-500">
                По запросу «{{ $term }}» найдено: <span class="font-semibold text-slate-900">{{ $parts->total() }}</span>
            </p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($parts as $part)
                    <x-part-card :part="$part" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $parts->links() }}
            </div>
        @endif
    </div>
@endsection
