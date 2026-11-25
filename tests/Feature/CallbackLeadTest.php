<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\QuoteRequest;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CallbackLeadTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_callback_request_is_saved_as_new_lead(): void
    {
        $response = $this->from(route('home'))->post(route('lead.store'), [
            'name' => 'Аркадий',
            'phone' => '+7 900 000-00-00',
            'comment' => 'Перезвоните после 18:00',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('lead_sent');

        $lead = QuoteRequest::query()->sole();

        $this->assertSame('Аркадий', $lead->name);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertSame('callback', $lead->source);
        $this->assertSame('Перезвоните после 18:00', $lead->comment);
    }

    public function test_callback_without_comment_gets_default_description(): void
    {
        $this->post(route('lead.store'), [
            'name' => 'Аркадий',
            'phone' => '+7 900 000-00-00',
            'consent' => '1',
        ]);

        $this->assertSame('Запрос обратного звонка', QuoteRequest::query()->sole()->comment);
    }

    public function test_callback_requires_name_phone_and_consent(): void
    {
        $response = $this->post(route('lead.store'), []);

        $response->assertSessionHasErrors(['name', 'phone', 'consent']);
        $this->assertSame(0, QuoteRequest::query()->count());
    }

    public function test_callback_rejects_honeypot_field(): void
    {
        $response = $this->post(route('lead.store'), [
            'name' => 'Аркадий',
            'phone' => '+7 900 000-00-00',
            'consent' => '1',
            'website' => 'https://spam.example',
        ]);

        $response->assertSessionHasErrors('website');
        $this->assertSame(0, QuoteRequest::query()->count());
    }

    public function test_phone_longer_than_allowed_is_rejected(): void
    {
        $response = $this->post(route('lead.store'), [
            'name' => 'Аркадий',
            'phone' => str_repeat('9', 40),
            'consent' => '1',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertSame(0, QuoteRequest::query()->count());
    }
}
