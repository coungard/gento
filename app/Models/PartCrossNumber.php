<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartCrossNumber extends Model
{
    protected $fillable = ['part_id', 'number', 'brand', 'kind'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function scopeOem(Builder $query): Builder
    {
        return $query->where('kind', 'oem');
    }

    public function scopeAnalog(Builder $query): Builder
    {
        return $query->where('kind', 'analog');
    }
}
