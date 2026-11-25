@props(['part'])

@php
    $partUrl = $part->category !== null
        ? route('catalog.part', ['category' => $part->category, 'part' => $part])
        : route('search', ['q' => $part->sku]);
@endphp

<article class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:border-brand-300 hover:shadow-lg hover:shadow-slate-200/60">
    <a href="{{ $partUrl }}" class="flex flex-1 flex-col p-4">
        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">{{ $part->category?->name }}</p>
        <h3 class="mt-1.5 line-clamp-2 font-semibold leading-snug text-slate-900 group-hover:text-brand-700">{{ $part->name }}</h3>
        <p class="mt-1 text-xs text-slate-500">
            Арт. {{ $part->sku }}@if ($part->brand !== null && $part->brand !== '') · {{ $part->brand }}@endif
        </p>

        <div class="mt-auto flex items-end justify-between gap-3 pt-4">
            <div>
                <p class="text-lg font-bold text-slate-900">{{ $part->price_formatted }}</p>
                @if ($part->old_price)
                    <p class="text-xs text-slate-400 line-through">{{ $part->old_price_formatted }}</p>
                @endif
            </div>
            @if ($part->in_stock)
                <span class="badge bg-emerald-50 text-emerald-700">В наличии</span>
            @else
                <span class="badge bg-slate-100 text-slate-600">Под заказ</span>
            @endif
        </div>
    </a>

    <div class="border-t border-slate-100 p-3">
        <form action="{{ route('cart.store', $part) }}" method="post" class="flex gap-2">
            @csrf
            <input type="hidden" name="qty" value="1">
            <button type="submit" class="btn-primary flex-1 py-2 text-xs">В заявку</button>
        </form>
    </div>
</article>
