@extends('layouts.app')

@section('title', 'Заявка на подбор запчастей')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <nav aria-label="Хлебные крошки" class="text-xs text-slate-500">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ route('home') }}" class="hover:text-brand-700">Главная</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-slate-700">Заявка</li>
            </ol>
        </nav>

        <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Заявка на подбор и расчёт</h1>
        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
            Оформление без оплаты и регистрации: менеджер проверит совместимость, уточнит наличие и пришлёт счёт.
        </p>

        @if (session('quote_sent'))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-6">
                <p class="text-lg font-semibold text-emerald-900">Заявка №{{ session('quote_sent') }} принята</p>
                <p class="mt-2 text-sm leading-relaxed text-emerald-800">
                    Менеджер свяжется с вами в рабочее время и подтвердит состав, цены и сроки доставки.
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('catalog.index') }}" class="btn-secondary">Продолжить подбор</a>
                    <a href="tel:{{ config('site.phone_href') }}" class="btn-primary">Позвонить: {{ config('site.phone') }}</a>
                </div>
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <p class="text-lg font-semibold text-slate-900">В заявке пока нет позиций</p>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-600">
                    Добавьте запчасти из каталога — или опишите задачу в форме ниже, и мы подберём детали сами.
                </p>
                <a href="{{ route('catalog.index') }}" class="btn-primary mt-5">Перейти в каталог</a>
            </div>
        @endif

        <div class="mt-8 grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-4">
                @if ($items->isNotEmpty())
                    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <div class="border-b border-slate-100 px-5 py-4">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Позиции заявки</h2>
                        </div>

                        <ul class="divide-y divide-slate-100">
                            @foreach ($items as $item)
                                <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                                    <div class="min-w-0 flex-1">
                                        @if ($item['part']->category !== null)
                                            <a href="{{ route('catalog.part', ['category' => $item['part']->category, 'part' => $item['part']]) }}" class="font-medium text-slate-900 hover:text-brand-700">
                                                {{ $item['part']->name }}
                                            </a>
                                        @else
                                            <span class="font-medium text-slate-900">{{ $item['part']->name }}</span>
                                        @endif
                                        <p class="mt-0.5 text-xs text-slate-500">Арт. {{ $item['part']->sku }} · {{ $item['part']->price_formatted }} / {{ $item['part']->unit }}</p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <form action="{{ route('cart.update', $item['part']) }}" method="post" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <label for="qty-{{ $item['part']->id }}" class="sr-only">Количество</label>
                                            <input id="qty-{{ $item['part']->id }}" type="number" name="qty" value="{{ $item['qty'] }}" min="1" max="999" step="1" class="w-20 text-center" inputmode="numeric">
                                            <button type="submit" class="btn-secondary px-2.5 py-2 text-xs">Обновить</button>
                                        </form>

                                        <p class="w-28 text-right text-sm font-semibold text-slate-900">{{ number_format($item['sum'], 0, ',', ' ') }} ₽</p>

                                        <form action="{{ route('cart.destroy', $item['part']) }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600" title="Удалить позицию">
                                                <span class="sr-only">Удалить {{ $item['part']->name }}</span>
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                                    <path d="M4 7h16M9 7V5h6v2M6 7l1 13h10l1-13" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-5 py-4">
                            <p class="text-sm text-slate-600">Итого без стоимости доставки</p>
                            <p class="text-xl font-bold text-slate-900">{{ number_format($total, 0, ',', ' ') }} ₽</p>
                        </div>
                    </section>
                @endif

                <section class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="text-lg font-semibold text-slate-900">Контактные данные</h2>
                    <p class="mt-1 text-sm text-slate-600">Отправьте заявку — перезвоним и подтвердим детали.</p>

                    @if ($errors->any())
                        <div role="alert" class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            Проверьте заполнение формы: {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('quote.store') }}" method="post" class="mt-5 space-y-4">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="field">
                                <label for="name">Имя <span class="text-red-500">*</span></label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" @class(['border-red-400' => $errors->has('name')])>
                                @error('name')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="phone">Телефон <span class="text-red-500">*</span></label>
                                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="+7 (___) ___-__-__" @class(['border-red-400' => $errors->has('phone')])>
                                @error('phone')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="email">E-mail</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="для счёта и документов" @class(['border-red-400' => $errors->has('email')])>
                                @error('email')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="company">Компания</label>
                                <input id="company" type="text" name="company" value="{{ old('company') }}" autocomplete="organization" @class(['border-red-400' => $errors->has('company')])>
                                @error('company')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label for="comment">Комментарий</label>
                            <textarea id="comment" name="comment" rows="4" placeholder="Модель генератора, артикулы, сроки — всё, что важно учесть" @class(['border-red-400' => $errors->has('comment')])>{{ old('comment') }}</textarea>
                            @error('comment')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <input type="text" name="website" value="" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                        <label class="flex items-start gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="consent" value="1" class="mt-0.5 size-4 rounded border-slate-300 text-brand-600" required @checked(old('consent'))>
                            <span>Согласен на обработку персональных данных и получение ответа по указанным контактам</span>
                        </label>
                        @error('consent')
                            <p class="field-error">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="btn-primary w-full sm:w-auto">Отправить заявку</button>
                    </form>
                </section>
            </div>

            <aside class="space-y-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Что дальше</h2>
                    <ol class="mt-3 space-y-3 text-sm text-slate-600">
                        <li class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">1</span> Проверяем артикулы по модели генератора.</li>
                        <li class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">2</span> Подтверждаем наличие и сроки поставки.</li>
                        <li class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">3</span> Выставляем счёт и отправляем заказ.</li>
                    </ol>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Контакты</h2>
                    <a href="tel:{{ config('site.phone_href') }}" class="mt-3 block text-lg font-semibold text-slate-900 hover:text-brand-700">{{ config('site.phone') }}</a>
                    <p class="mt-1 text-sm text-slate-500">{{ config('site.work_hours') }}</p>
                    <a href="mailto:{{ config('site.email') }}" class="mt-3 block text-sm text-brand-700 hover:text-brand-800">{{ config('site.email') }}</a>
                    <p class="mt-3 text-xs leading-relaxed text-slate-500">{{ config('site.address') }}</p>
                </div>
            </aside>
        </div>
    </div>
@endsection
