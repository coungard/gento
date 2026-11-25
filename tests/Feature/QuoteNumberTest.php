<?php

namespace Tests\Feature;

use App\Models\QuoteRequest;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class QuoteNumberTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_number_sequence_starts_from_one_in_current_month(): void
    {
        $this->travelTo('2026-03-15 10:00:00');

        $this->assertSame('ЗК-2603-0001', QuoteRequest::nextNumber());
    }

    public function test_number_sequence_increments_within_month(): void
    {
        $this->travelTo('2026-03-15 10:00:00');

        QuoteRequest::factory()->create();
        QuoteRequest::factory()->create();

        $this->assertSame('ЗК-2603-0003', QuoteRequest::nextNumber());
    }

    public function test_number_sequence_restarts_in_new_month(): void
    {
        $this->travelTo('2026-03-31 23:00:00');
        QuoteRequest::factory()->create();

        $this->travelTo('2026-04-02 09:00:00');

        $this->assertSame('ЗК-2604-0001', QuoteRequest::nextNumber());
    }

    public function test_created_quote_gets_unique_sequential_number(): void
    {
        $this->travelTo('2026-03-15 10:00:00');

        $first = QuoteRequest::factory()->create();
        $second = QuoteRequest::factory()->create();

        $this->assertSame('ЗК-2603-0001', $first->number);
        $this->assertSame('ЗК-2603-0002', $second->number);
        $this->assertNotSame($first->number, $second->number);
    }

    public function test_explicit_number_is_not_overwritten(): void
    {
        $quote = QuoteRequest::factory()->create(['number' => 'ЗК-2603-0042']);

        $this->assertSame('ЗК-2603-0042', $quote->number);
    }
}
