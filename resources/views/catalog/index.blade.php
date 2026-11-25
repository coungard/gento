@extends('layouts.app')

@section('title', 'Каталог запчастей для генераторов')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <nav aria-label="Хлебные крошки" class="text-xs text-slate-500">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ route('home') }}" class="hover:text-brand-700">Главная</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-slate-700">Каталог</li>
            </ol>
        </nav>

        <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Каталог запчастей</h1>
        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
            Разделы собраны по узлам генераторной установки. Если не знаете точное название детали — ищите по артикулу в строке поиска.
        </p>

        <div class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach ($categories as $category)
                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="text-lg font-semibold text-slate-900">
                            <a href="{{ route('catalog.category', $category) }}" class="hover:text-brand-700">{{ $category->name }}</a>
                        </h2>
                        <span class="badge bg-slate-100 text-slate-600">{{ $category->parts_count }} поз.</span>
                    </div>

                    @if ($category->description)
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $category->description }}</p>
                    @endif

                    @if ($category->children->isNotEmpty())
                        <ul class="mt-4 flex flex-wrap gap-2">
                            @foreach ($category->children as $child)
                                <li>
                                    <a href="{{ route('catalog.category', $child) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1 text-xs text-slate-700 transition hover:border-brand-300 hover:text-brand-700">
                                        {{ $child->name }}
                                        <span class="text-slate-400">{{ $child->parts_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
@endsection
