<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Services\QuoteCart;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class QuoteCartTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_adding_part_stores_it_in_session_with_default_quantity(): void
    {
        $part = Part::factory()->create(['price' => 1850.50]);

        $response = $this->post(route('cart.store', $part));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $response->assertSessionHas(QuoteCart::SESSION_KEY, [$part->id => 1]);
    }

    public function test_repeated_add_increments_quantity(): void
    {
        $part = Part::factory()->create();

        $this->post(route('cart.store', $part), ['qty' => 2]);
        $this->post(route('cart.store', $part), ['qty' => 3]);

        $this->assertSame(5, app(QuoteCart::class)->count());
    }

    public function test_quantity_is_capped_at_maximum(): void
    {
        $part = Part::factory()->create();

        $this->post(route('cart.store', $part), ['qty' => 5000]);

        $this->assertSame(QuoteCart::MAX_QTY, session(QuoteCart::SESSION_KEY)[$part->id]);
    }

    public function test_update_replaces_quantity(): void
    {
        $part = Part::factory()->create();

        $this->post(route('cart.store', $part), ['qty' => 1]);

        $this->patch(route('cart.update', $part), ['qty' => 4]);

        $this->assertSame(4, session(QuoteCart::SESSION_KEY)[$part->id]);
    }

    public function test_update_to_zero_removes_position(): void
    {
        $part = Part::factory()->create();

        $this->post(route('cart.store', $part), ['qty' => 2]);

        $this->patch(route('cart.update', $part), ['qty' => 0]);

        $this->assertArrayNotHasKey($part->id, (array) session(QuoteCart::SESSION_KEY));
    }

    public function test_destroy_removes_part_from_cart(): void
    {
        $part = Part::factory()->create();

        $this->post(route('cart.store', $part), ['qty' => 1]);

        $response = $this->delete(route('cart.destroy', $part));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertSame([], (array) session(QuoteCart::SESSION_KEY));
    }

    public function test_inactive_part_cannot_be_added_to_cart(): void
    {
        $part = Part::factory()->inactive()->create();

        $this->post(route('cart.store', $part))->assertNotFound();
    }

    public function test_quote_page_lists_cart_positions_with_total(): void
    {
        $part = Part::factory()->create([
            'name' => 'Фильтр масляный Fleetguard',
            'price' => 1850.50,
        ]);

        $this->post(route('cart.store', $part), ['qty' => 2]);

        $response = $this->get(route('quote.index'));

        $response->assertOk();
        $response->assertSee('Фильтр масляный Fleetguard');
        $response->assertSee('3 701');
        $response->assertDontSee('В заявке пока нет позиций');
    }

    public function test_cart_skips_positions_of_deleted_parts(): void
    {
        $part = Part::factory()->create(['price' => 1850.50]);
        $this->withSession([QuoteCart::SESSION_KEY => [$part->id => 2]]);

        $part->delete();

        $response = $this->get(route('quote.index'));

        $response->assertOk();
        $response->assertSee('В заявке пока нет позиций');
        $this->assertSame(0.0, app(QuoteCart::class)->total());
    }
}
