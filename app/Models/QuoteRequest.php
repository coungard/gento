<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    /** @use HasFactory<\Database\Factories\QuoteRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'number', 'name', 'phone', 'email', 'company', 'comment', 'status',
        'total', 'manager_comment', 'source', 'utm', 'ip', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'total' => 'decimal:2',
            'utm' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $request): void {
            $request->number ??= static::nextNumber();
        });
    }

    public static function nextNumber(): string
    {
        $prefix = 'ЗК-'.now()->format('ym').'-';
        $last = static::query()
            ->where('number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('number');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    /** @return array<string, string> */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function recalculateTotal(): void
    {
        $total = $this->items()->get()->sum(fn (QuoteRequestItem $item) => (float) $item->price * $item->qty);

        $this->forceFill(['total' => $total])->save();
    }

    public function scopeStatus(Builder $query, LeadStatus|string $status): Builder
    {
        return $query->where('status', $status instanceof LeadStatus ? $status->value : $status);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            LeadStatus::New->value,
            LeadStatus::InProgress->value,
            LeadStatus::Quoted->value,
        ]);
    }
}
