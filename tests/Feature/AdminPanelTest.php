<?php

namespace Tests\Feature;

use App\Filament\Pages\ImportCatalog;
use App\Models\Part;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_user_without_admin_flag_cannot_open_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_open_dashboard(): void
    {
        $this->actingAsAdmin()->get('/admin')->assertOk();
    }

    public function test_admin_can_open_catalog_resource(): void
    {
        Part::factory()->create(['sku' => 'FLT-OIL-001', 'name' => 'Фильтр масляный Fleetguard']);

        $response = $this->actingAsAdmin()->get('/admin/parts');

        $response->assertOk();
        $response->assertSee('Фильтр масляный Fleetguard');
    }

    public function test_admin_can_open_csv_import_page(): void
    {
        $response = $this->actingAsAdmin()->get(ImportCatalog::getUrl());

        $response->assertOk();
        $response->assertSee('Импорт каталога из CSV');
        $response->assertSee('Импортировать');
    }

    private function actingAsAdmin(): static
    {
        return $this->actingAs(User::factory()->admin()->create());
    }
}
