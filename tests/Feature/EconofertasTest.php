<?php

namespace Tests\Feature;

use App\Models\Flyer;
use App\Models\Offer;
use App\Models\Supermarket;
use App\Models\User;
use App\Services\OfferSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EconofertasTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_only_active_supermarkets(): void
    {
        Supermarket::create(['name' => 'Mercado Ativo', 'slug' => 'ativo', 'color' => '#177245', 'is_active' => true]);
        Supermarket::create(['name' => 'Mercado Oculto', 'slug' => 'oculto', 'color' => '#177245', 'is_active' => false]);

        $this->get('/')->assertOk()->assertSee('Mercado Ativo')->assertDontSee('Mercado Oculto');
    }

    public function test_admin_routes_require_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/entrar');
        $this->get('/admin/folhetos')->assertRedirect('/admin/entrar');
    }

    public function test_admin_login_is_hidden_through_quick_tunnel(): void
    {
        $this->get('https://teste.trycloudflare.com/admin/entrar')
            ->assertNotFound();
    }

    public function test_user_can_log_in_to_admin_panel(): void
    {
        $user = User::factory()->create(['password' => 'segredo123']);

        $this->post('/admin/entrar', ['email' => $user->email, 'password' => 'segredo123'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_upload_a_pdf_flyer(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $market = Supermarket::create(['name' => 'Mercado', 'slug' => 'mercado', 'color' => '#177245', 'is_active' => true]);

        $this->actingAs($user)->post('/admin/folhetos', [
            'supermarket_id' => $market->id,
            'title' => 'Ofertas da semana',
            'pdf' => UploadedFile::fake()->create('ofertas.pdf', 100, 'application/pdf'),
            'is_published' => '1',
        ])->assertRedirect('/admin/folhetos');

        $flyer = Flyer::firstOrFail();
        Storage::disk('local')->assertExists($flyer->file_path);
    }

    public function test_expired_flyer_is_not_visible(): void
    {
        $market = Supermarket::create(['name' => 'Mercado', 'slug' => 'mercado', 'color' => '#177245', 'is_active' => true]);
        Flyer::create(['supermarket_id' => $market->id, 'title' => 'Antigo', 'file_path' => 'flyers/old.pdf', 'ends_at' => today()->subDay(), 'is_published' => true]);

        $this->get('/supermercados/mercado')->assertOk()->assertDontSee('Antigo');
    }

    public function test_flyer_ending_today_can_be_opened(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('flyers/today.pdf', '%PDF-1.4 test');
        $market = Supermarket::create(['name' => 'Mercado', 'slug' => 'mercado', 'color' => '#177245', 'is_active' => true]);
        $flyer = Flyer::create([
            'supermarket_id' => $market->id,
            'title' => 'Válido hoje',
            'file_path' => 'flyers/today.pdf',
            'ends_at' => today(),
            'is_published' => true,
        ]);

        $this->get(route('flyers.view', $flyer))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_api_lists_markets_and_only_available_flyers(): void
    {
        $market = Supermarket::create(['name' => 'Mercado Mobile', 'slug' => 'mobile', 'color' => '#177245', 'is_active' => true]);
        Flyer::create(['supermarket_id' => $market->id, 'title' => 'Folheto atual', 'file_path' => 'flyers/current.pdf', 'is_published' => true]);
        Flyer::create(['supermarket_id' => $market->id, 'title' => 'Rascunho', 'file_path' => 'flyers/draft.pdf', 'is_published' => false]);

        $this->getJson('/api/supermercados')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'mobile')
            ->assertJsonPath('data.0.flyers_count', 1);

        $this->withHeader('X-Forwarded-Proto', 'https')
            ->getJson('https://teste.trycloudflare.com/api/supermercados/mobile')
            ->assertOk()
            ->assertJsonCount(1, 'data.flyers')
            ->assertJsonPath('data.flyers.0.title', 'Folheto atual')
            ->assertJsonPath('data.flyers.0.pdf_url', 'https://teste.trycloudflare.com/folhetos/1/visualizar');
    }

    public function test_offer_sync_imports_only_nova_odessa_flyers_and_public_products(): void
    {
        Http::fake([
            'www.svicente.com.br/*' => Http::response('<div id="Americana"><div class="img_desktop"><img src="/americana.jpg"></div></div><div id="NovaOdessa"><div class="img_desktop"><img src="/nova.jpg"></div></div>'),
            'www.superpaguemenos.com.br/*' => Http::response('<div class="li"><img data-src="//images.test/arroz.jpg"><form class="product-form" action="/arroz/p" data-json=\'{"item_id":123,"item_name":"Arroz Tipo 1 Marca Boa 5kg Oferta","item_brand":"Marca Boa","price":29.90}\'><input name="price" value="24.90"></form></div>'),
        ]);

        $result = app(OfferSyncService::class)->sync();

        $this->assertSame(['São Vicente' => 1, 'Pague Menos' => 1], $result);
        $this->assertDatabaseHas('offers', ['name' => 'Encarte de ofertas - Nova Odessa', 'city' => 'Nova Odessa']);
        $this->assertDatabaseHas('offers', ['external_id' => '123', 'price' => 24.90, 'original_price' => 29.90]);
        $this->assertDatabaseMissing('offers', ['image_url' => 'https://www.svicente.com.br/americana.jpg']);
    }

    public function test_offer_api_searches_active_offers(): void
    {
        $market = Supermarket::create(['name' => 'Mercado', 'slug' => 'mercado-ofertas', 'color' => '#177245', 'is_active' => true]);
        Offer::create(['supermarket_id' => $market->id, 'external_id' => '1', 'name' => 'Leite integral 1L', 'price' => 4.99, 'source_url' => 'https://example.com/leite', 'city' => 'Nova Odessa', 'is_active' => true]);
        Offer::create(['supermarket_id' => $market->id, 'external_id' => '2', 'name' => 'Arroz 5kg', 'price' => 20, 'source_url' => 'https://example.com/arroz', 'city' => 'Nova Odessa', 'is_active' => false]);

        $this->getJson('/api/ofertas?q=leite')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Leite integral 1L')
            ->assertJsonPath('data.0.supermarket.name', 'Mercado');
    }

    public function test_offer_api_marks_the_cheapest_matching_product(): void
    {
        $first = Supermarket::create(['name' => 'Loja A', 'slug' => 'loja-a', 'color' => '#177245', 'is_active' => true]);
        $second = Supermarket::create(['name' => 'Loja B', 'slug' => 'loja-b', 'color' => '#772245', 'is_active' => true]);
        foreach ([[$first, 'a', 8.90], [$second, 'b', 7.50]] as [$market, $id, $price]) {
            Offer::create(['supermarket_id' => $market->id, 'external_id' => $id, 'name' => 'Café 500g', 'match_key' => 'cafe 500g', 'price' => $price, 'source_url' => 'https://example.com', 'is_active' => true]);
        }

        $this->getJson('/api/ofertas?q=caf')->assertOk()
            ->assertJsonPath('data.0.price', 7.5)
            ->assertJsonPath('data.0.compared_stores', 2)
            ->assertJsonPath('data.0.is_cheapest', true)
            ->assertJsonPath('data.1.is_cheapest', false);
    }
}
