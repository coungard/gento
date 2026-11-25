<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Http\Middleware\TrackUtm;
use App\Models\Part;
use App\Models\QuoteRequest;
use App\Services\QuoteCart;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class QuoteRequestSubmissionTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Иван Петров',
            'phone' => '+7 999 123-45-67',
            'email' => 'ivan@example.com',
            'company' => 'ООО «Ромашка»',
            'comment' => 'Нужен счёт на оплату',
            'consent' => '1',
        ];
    }

    public function test_submission_creates_quote_with_items_and_total(): void
    {
        $part = Part::factory()->create([
            'sku' => 'FLT-OIL-001',
            'name' => 'Фильтр масляный Fleetguard',
            'price' => 1850.50,
        ]);

        $query = $this->withSession([QuoteCart::SESSION_KEY => [$part->id => 2]]);

        $response = $query->post(route('quote.store'), $this->validPayload());

        $response->assertRedirect(route('quote.index'));
        $response->assertSessionHas('quote_sent');
        $response->assertSessionMissing(QuoteCart::SESSION_KEY);

        $quote = QuoteRequest::query()->sole();

        $this->assertSame('Иван Петров', $quote->name);
        $this->assertSame('+7 999 123-45-67', $quote->phone);
        $this->assertSame(LeadStatus::New, $quote->status);
        $this->assertSame('site', $quote->source);
        $this->assertEqualsWithDelta(3701.0, (float) $quote->total, 0.001);
        $this->assertMatchesRegularExpression('/^ЗК-\d{4}-\d{4}$/', $quote->number);

        $item = $quote->items()->sole();

        $this->assertSame($part->id, $item->part_id);
        $this->assertSame('FLT-OIL-001', $item->sku);
        $this->assertSame('Фильтр масляный Fleetguard', $item->name);
        $this->assertSame(2, $item->qty);
        $this->assertEqualsWithDelta(1850.50, (float) $item->price, 0.001);
    }

    public function test_submission_stores_utm_marks_from_session(): void
    {
        $part = Part::factory()->create();

        $this->withSession([
            QuoteCart::SESSION_KEY => [$part->id => 1],
            TrackUtm::SESSION_KEY => ['utm_source' => 'yandex', 'utm_campaign' => 'filters'],
        ])->post(route('quote.store'), $this->validPayload());

        $quote = QuoteRequest::query()->sole();

        $this->assertSame(
            ['utm_source' => 'yandex', 'utm_campaign' => 'filters'],
            $quote->utm,
        );
    }

    public function test_submission_requires_contact_data(): void
    {
        $part = Part::factory()->create();
        $this->withSession([QuoteCart::SESSION_KEY => [$part->id => 1]]);

        $response = $this->post(route('quote.store'), []);

        $response->assertSessionHasErrors(['name', 'phone', 'consent']);
        $this->assertSame(0, QuoteRequest::query()->count());
    }

    public function test_submission_with_empty_cart_is_rejected(): void
    {
        $response = $this->post(route('quote.store'), $this->validPayload());

        $response->assertRedirect(route('quote.index'));
        $response->assertSessionHas('error');
        $this->assertSame(0, QuoteRequest::query()->count());
    }

    public function test_honeypot_field_blocks_submission(): void
    {
        $part = Part::factory()->create();
        $this->withSession([QuoteCart::SESSION_KEY => [$part->id => 1]]);

        $response = $this->post(route('quote.store'), [
            ...$this->validPayload(),
            'website' => 'https://spam.example',
        ]);

        $response->assertSessionHasErrors('website');
        $this->assertSame(0, QuoteRequest::query()->count());
    }

    public function test_successful_submission_shows_quote_number_on_quote_page(): void
    {
        $part = Part::factory()->create();
        $this->withSession([QuoteCart::SESSION_KEY => [$part->id => 1]]);

        $response = $this->post(route('quote.store'), $this->validPayload());
        $quote = QuoteRequest::query()->sole();

        $response->assertSessionHas('quote_sent', $quote->number);

        $this->get(route('quote.index'))
            ->assertOk()
            ->assertSee($quote->number);
    }
}
