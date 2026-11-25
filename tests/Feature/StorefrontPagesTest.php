<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Part;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StorefrontPagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_page_shows_categories_and_featured_parts(): void
    {
        $category = Category::factory()->create(['name' => 'Фильтры', 'is_active' => true]);
        $featured = Part::factory()->create([
            'category_id' => $category->id,
            'name' => 'Фильтр масляный Fleetguard',
            'is_featured' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Запчасти для генераторов любых брендов');
        $response->assertSee('Фильтры');
        $response->assertSee('Фильтр масляный Fleetguard');
        $response->assertSee(route('catalog.part', [$category, $featured]), false);
    }

    public function test_catalog_index_lists_active_root_categories_only(): void
    {
        Category::factory()->create(['name' => 'Фильтры', 'parent_id' => null, 'is_active' => true]);
        Category::factory()->create(['name' => 'Скрытый раздел', 'parent_id' => null, 'is_active' => false]);

        $response = $this->get(route('catalog.index'));

        $response->assertOk();
        $response->assertSee('Каталог запчастей');
        $response->assertSee('Фильтры');
        $response->assertDontSee('Скрытый раздел');
    }

    public function test_category_page_shows_only_active_parts(): void
    {
        $category = Category::factory()->create(['name' => 'Масляные фильтры', 'is_active' => true]);
        Part::factory()->create([
            'category_id' => $category->id,
            'name' => 'Фильтр масляный Fleetguard',
            'is_active' => true,
        ]);
        Part::factory()->inactive()->create([
            'category_id' => $category->id,
            'name' => 'Снятая позиция',
        ]);

        $response = $this->get(route('catalog.category', $category));

        $response->assertOk();
        $response->assertSee('Масляные фильтры');
        $response->assertSee('Фильтр масляный Fleetguard');
        $response->assertDontSee('Снятая позиция');
    }

    public function test_part_page_shows_price_stock_and_cross_numbers(): void
    {
        $category = Category::factory()->create();
        $part = Part::factory()->create([
            'category_id' => $category->id,
            'name' => 'Фильтр масляный Fleetguard',
            'sku' => 'FLT-OIL-001',
            'price' => 1800,
            'stock' => 7,
        ]);
        $part->crossNumbers()->create(['number' => 'LF-3345', 'brand' => 'Fleetguard', 'kind' => 'analog']);

        $response = $this->get(route('catalog.part', [$category, $part]));

        $response->assertOk();
        $response->assertSee('Фильтр масляный Fleetguard');
        $response->assertSee('FLT-OIL-001');
        $response->assertSee('LF-3345');
        $response->assertSee('1 800');
    }

    public function test_quote_page_without_cart_prompts_to_open_catalog(): void
    {
        $response = $this->get(route('quote.index'));

        $response->assertOk();
        $response->assertSee('Заявка на подбор и расчёт');
        $response->assertSee('В заявке пока нет позиций');
        $response->assertSee(route('catalog.index'), false);
    }

    public function test_content_page_renders_body(): void
    {
        Page::factory()->create([
            'slug' => 'delivery',
            'title' => 'Доставка и оплата',
            'body' => '<p>Доставляем по всей России.</p>',
        ]);

        $this->get(route('pages.show', 'delivery'))
            ->assertOk()
            ->assertSee('Доставка и оплата')
            ->assertSee('Доставляем по всей России.');
    }

    public function test_inactive_content_page_is_not_found(): void
    {
        Page::factory()->create([
            'slug' => 'hidden-page',
            'title' => 'Черновик',
            'is_active' => false,
        ]);

        $this->get(route('pages.show', 'hidden-page'))->assertNotFound();
    }

    public function test_unpublished_content_page_is_not_found(): void
    {
        Page::factory()->create([
            'slug' => 'scheduled-page',
            'title' => 'Будущая публикация',
            'published_at' => now()->addWeek(),
        ]);

        $this->get(route('pages.show', 'scheduled-page'))->assertNotFound();
    }

    public function test_inactive_category_is_not_available(): void
    {
        $category = Category::factory()->create(['is_active' => false]);

        $this->get(route('catalog.category', $category))->assertNotFound();
    }
}
