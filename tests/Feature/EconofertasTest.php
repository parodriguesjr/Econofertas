<?php

namespace Tests\Feature;

use App\Models\Flyer;
use App\Models\Supermarket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_api_lists_markets_and_only_available_flyers(): void
    {
        $market = Supermarket::create(['name' => 'Mercado Mobile', 'slug' => 'mobile', 'color' => '#177245', 'is_active' => true]);
        Flyer::create(['supermarket_id' => $market->id, 'title' => 'Folheto atual', 'file_path' => 'flyers/current.pdf', 'is_published' => true]);
        Flyer::create(['supermarket_id' => $market->id, 'title' => 'Rascunho', 'file_path' => 'flyers/draft.pdf', 'is_published' => false]);

        $this->getJson('/api/supermercados')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'mobile')
            ->assertJsonPath('data.0.flyers_count', 1);

        $this->getJson('/api/supermercados/mobile')
            ->assertOk()
            ->assertJsonCount(1, 'data.flyers')
            ->assertJsonPath('data.flyers.0.title', 'Folheto atual');
    }
}
