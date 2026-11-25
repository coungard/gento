<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\GeneratorModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GeneratorModel>
 */
class GeneratorModelFactory extends Factory
{
    protected $model = GeneratorModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Генератор '.fake()->unique()->numberBetween(1, 9999);

        return [
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'power_kva' => fake()->randomFloat(2, 2, 500),
            'engine' => 'Дизельный',
            'notes' => null,
        ];
    }
}
