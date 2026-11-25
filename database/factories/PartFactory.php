<?php

namespace Database\Factories;

use App\Enums\PartSource;
use App\Models\Category;
use App\Models\Part;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Part>
 */
class PartFactory extends Factory
{
    protected $model = Part::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Запчасть '.fake()->unique()->numberBetween(1, 99999);
        $price = fake()->randomFloat(2, 500, 80000);

        return [
            'category_id' => Category::factory(),
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'external_id' => null,
            'source' => PartSource::Manual,
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'brand' => 'OEM',
            'description' => fake()->paragraph(),
            'specs' => ['Материал' => 'Сталь', 'Гарантия' => '12 мес.'],
            'price' => $price,
            'old_price' => null,
            'currency' => 'RUB',
            'unit' => 'шт',
            'stock' => fake()->numberBetween(0, 50),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
