<?php

namespace App\Models;

use App\Enums\PartSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends Model
{
    /** @use HasFactory<\Database\Factories\PartFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'sku', 'external_id', 'source', 'name', 'slug', 'brand',
        'description', 'specs', 'price', 'old_price', 'currency', 'unit', 'stock',
        'is_active', 'is_featured', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'source' => PartSource::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function generatorModels(): BelongsToMany
    {
        return $this->belongsToMany(GeneratorModel::class, 'part_generator_model')->withPivot('note');
    }

    public function crossNumbers(): HasMany
    {
        return $this->hasMany(PartCrossNumber::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PartPriceHistory::class);
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    public function getPriceFormattedAttribute(): string
    {
        return $this->formatMoney($this->price);
    }

    public function getOldPriceFormattedAttribute(): ?string
    {
        return $this->old_price ? $this->formatMoney($this->old_price) : null;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->old_price || (float) $this->old_price <= (float) $this->price) {
            return null;
        }

        return (int) round(100 - ((float) $this->price / (float) $this->old_price) * 100);
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    protected function formatMoney(float|string|null $value): string
    {
        return number_format((float) $value, 0, ',', ' ').' ₽';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInCategory(Builder $query, Category|int $category): Builder
    {
        $id = $category instanceof Category ? $category->getKey() : $category;

        return $query->where('category_id', $id);
    }

    public function scopePriceBetween(Builder $query, ?float $from, ?float $to): Builder
    {
        return $query
            ->when($from !== null, fn (Builder $q) => $q->where('price', '>=', $from))
            ->when($to !== null, fn (Builder $q) => $q->where('price', '<=', $to));
    }

    /**
     * Поиск по названию, артикулу, описанию и кросс-номерам.
     *
     * На MySQL используется FULLTEXT-индекс (см. миграцию add_parts_fulltext_index),
     * на SQLite и для коротких запросов — обычный LIKE.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $driver = $query->getModel()->getConnection()->getDriverName();
        $like = '%'.$term.'%';

        if (in_array($driver, ['mysql', 'mariadb'], true) && mb_strlen($term) >= 3) {
            $booleanSafe = (string) preg_replace('/[+\-><()~*"@]+/', ' ', $term);

            return $query->where(function (Builder $q) use ($booleanSafe, $like) {
                $q->whereFullText(['name', 'sku', 'description'], $booleanSafe)
                    ->orWhereHas('crossNumbers', fn (Builder $c) => $c->where('number', 'like', $like));
            });
        }

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhereHas('crossNumbers', fn (Builder $c) => $c->where('number', 'like', $like));
        });
    }
}
