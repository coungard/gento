<?php

namespace Tests\Feature;

use App\Models\Part;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PartSearchTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_search_page_without_query_prompts_for_input(): void
    {
        $this->get(route('search'))
            ->assertOk()
            ->assertSee('Поиск по каталогу')
            ->assertSee('Введите артикул или название');
    }

    public function test_search_finds_part_by_sku(): void
    {
        $part = Part::factory()->create([
            'sku' => 'FLT-OIL-777',
            'name' => 'Фильтр масляный Cummins',
        ]);

        $this->get(route('search', ['q' => 'FLT-OIL-777']))
            ->assertOk()
            ->assertSee('Фильтр масляный Cummins');
    }

    public function test_search_finds_part_by_name_fragment(): void
    {
        $part = Part::factory()->create([
            'sku' => 'GEN-BELT-001',
            'name' => 'Ремень приводной Gates',
        ]);

        $this->get(route('search', ['q' => 'приводной']))
            ->assertOk()
            ->assertSee('Ремень приводной Gates');
    }

    public function test_search_finds_part_by_cross_number(): void
    {
        $part = Part::factory()->create([
            'sku' => 'FLT-OIL-888',
            'name' => 'Фильтр топливный Fleetguard',
        ]);
        $part->crossNumbers()->create(['number' => 'LF-3345', 'kind' => 'analog']);
        $part->crossNumbers()->create(['number' => '3401544', 'kind' => 'oem']);

        $this->get(route('search', ['q' => 'LF-3345']))
            ->assertOk()
            ->assertSee('Фильтр топливный Fleetguard');
    }

    public function test_search_does_not_show_inactive_parts(): void
    {
        Part::factory()->inactive()->create([
            'sku' => 'HIDDEN-001',
            'name' => 'Скрытая позиция каталога',
        ]);

        $this->get(route('search', ['q' => 'Скрытая позиция']))
            ->assertOk()
            ->assertDontSee('Скрытая позиция каталога')
            ->assertSee('ничего не найдено');
    }

    public function test_search_reports_empty_result(): void
    {
        $this->get(route('search', ['q' => 'ZZZ-UNKNOWN-999']))
            ->assertOk()
            ->assertSee('ничего не найдено')
            ->assertSee('ZZZ-UNKNOWN-999');
    }

    public function test_short_query_is_truncated_and_does_not_break_search(): void
    {
        $part = Part::factory()->create(['name' => 'Ремень приводной Gates']);

        $this->get(route('search', ['q' => str_repeat('а', 200)]))
            ->assertOk()
            ->assertSee('Поиск по каталогу');
    }
}
