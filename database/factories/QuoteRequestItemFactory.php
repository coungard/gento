<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\QuoteRequestItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteRequestItem>
 */
class QuoteRequestItemFactory extends Factory
{
    protected $model = QuoteRequestItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $part = Part::factory();

        return [
            'part_id' => $part,
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'name' => 'Позиция '.fake()->unique()->numberBetween(1, 99999),
            'price' => fake()->randomFloat(2, 500, 50000),
            'qty' => fake()->numberBetween(1, 5),
        ];
    }
}
