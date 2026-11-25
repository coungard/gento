@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->excerpt ?? $page->body ?? $page->title), 160))

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8">
        <nav aria-label="Хлебные крошки" class="text-xs text-slate-500">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ route('home') }}" class="hover:text-brand-700">Главная</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-slate-700">{{ $page->title }}</li>
            </ol>
        </nav>

        <article class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ $page->title }}</h1>

            @if ($page->published_at)
                <p class="mt-2 text-xs text-slate-500">Обновлено {{ $page->published_at->translatedFormat('d F Y') }}</p>
            @endif

            @if ($page->excerpt)
                <p class="mt-4 border-l-4 border-brand-200 pl-4 text-base leading-relaxed text-slate-600">{{ $page->excerpt }}</p>
            @endif

            <div class="rich-text mt-6">
                {!! $page->body !!}
            </div>
        </article>

        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 sm:flex sm:items-center sm:justify-between sm:gap-6">
            <div>
                <p class="font-semibold text-slate-900">Остались вопросы?</p>
                <p class="mt-1 text-sm text-slate-600">Позвоните или оставьте заявку — ответим в рабочее время.</p>
            </div>
            <div class="mt-4 flex shrink-0 flex-wrap gap-3 sm:mt-0">
                <a href="tel:{{ config('site.phone_href') }}" class="btn-secondary">{{ config('site.phone') }}</a>
                <a href="{{ route('quote.index') }}" class="btn-primary">Оставить заявку</a>
            </div>
        </div>
    </div>
@endsection
