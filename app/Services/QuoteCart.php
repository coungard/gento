<?php

namespace App\Services;

use App\Models\Part;
use Illuminate\Support\Collection;

/**
 * Корзина-заявка: хранит позиции в сессии, чтобы работать без JavaScript.
 *
 * Пользователь не оплачивает заказ на сайте: он собирает список запчастей
 * и отправляет его менеджеру вместе с контактными данными.
 */
class QuoteCart
{
    public const SESSION_KEY = 'quote_cart';

    /**
     * Максимальное количество одной позиции в заявке.
     */
    public const MAX_QTY = 999;

    /**
     * Сырые данные корзины: [part_id => quantity].
     *
     * @return array<int, int>
     */
    public function raw(): array
    {
        $stored = session()->get(self::SESSION_KEY, []);

        if (! is_array($stored)) {
            return [];
        }

        $items = [];

        foreach ($stored as $partId => $quantity) {
            $items[(int) $partId] = max(1, min(self::MAX_QTY, (int) $quantity));
        }

        return $items;
    }

    /**
     * Позиции корзины с моделями запчастей.
     *
     * @return Collection<int, array{part: Part, qty: int, sum: float}>
     */
    public function items(): Collection
    {
        $raw = $this->raw();

        if ($raw === []) {
            return collect();
        }

        $parts = Part::query()
            ->with('category')
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        return collect($raw)
            ->map(function (int $qty, int $partId) use ($parts): ?array {
                $part = $parts->get($partId);

                if (! $part) {
                    return null;
                }

                return [
                    'part' => $part,
                    'qty' => $qty,
                    'sum' => (float) $part->price * $qty,
                ];
            })
            ->filter()
            ->values();
    }

    public function add(Part $part, int $qty = 1): void
    {
        $raw = $this->raw();
        $raw[$part->getKey()] = max(1, min(self::MAX_QTY, ($raw[$part->getKey()] ?? 0) + max(1, $qty)));

        session()->put(self::SESSION_KEY, $raw);
    }

    public function update(Part $part, int $qty): void
    {
        $raw = $this->raw();

        if (! isset($raw[$part->getKey()])) {
            return;
        }

        if ($qty < 1) {
            unset($raw[$part->getKey()]);
        } else {
            $raw[$part->getKey()] = min(self::MAX_QTY, $qty);
        }

        session()->put(self::SESSION_KEY, $raw);
    }

    public function remove(Part $part): void
    {
        $raw = $this->raw();
        unset($raw[$part->getKey()]);

        session()->put(self::SESSION_KEY, $raw);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Количество позиций в корзине (уникальных запчастей).
     */
    public function positions(): int
    {
        return count($this->raw());
    }

    /**
     * Общее количество единиц товара.
     */
    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function total(): float
    {
        return (float) $this->items()->sum('sum');
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }
}
