<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GeneratorModel extends Model
{
    /** @use HasFactory<\Database\Factories\GeneratorModelFactory> */
    use HasFactory;

    protected $fillable = ['brand_id', 'name', 'slug', 'power_kva', 'engine', 'notes'];

    protected function casts(): array
    {
        return ['power_kva' => 'decimal:2'];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class, 'part_generator_model')->withPivot('note');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->brand?->name ?? '').' '.$this->name);
    }

    public function scopeWithBrand(Builder $query): Builder
    {
        return $query->with('brand');
    }
}
