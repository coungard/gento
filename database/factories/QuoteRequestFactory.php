<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\QuoteRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteRequest>
 */
class QuoteRequestFactory extends Factory
{
    protected $model = QuoteRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+7'.fake()->numerify('9########'),
            'email' => fake()->safeEmail(),
            'company' => fake()->boolean(30) ? fake()->company() : null,
            'comment' => fake()->boolean(50) ? fake()->sentence() : null,
            'status' => LeadStatus::New,
            'total' => 0,
            'source' => 'site',
        ];
    }

    public function withItems(int $count = 2): static
    {
        return $this->afterCreating(function (QuoteRequest $request) use ($count): void {
            QuoteRequestItemFactory::new()
                ->count($count)
                ->for($request, 'quoteRequest')
                ->create();

            $request->recalculateTotal();
        });
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => LeadStatus::InProgress]);
    }

    public function quoted(): static
    {
        return $this->state(fn () => ['status' => LeadStatus::Quoted]);
    }
}
