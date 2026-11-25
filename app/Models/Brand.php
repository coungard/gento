<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'country', 'logo'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function generatorModels(): HasMany
    {
        return $this->hasMany(GeneratorModel::class);
    }
}
