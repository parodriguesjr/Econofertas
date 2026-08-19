<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supermarket;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(['email' => 'admin@econofertas.local'], [
            'name' => 'Administrador',
            'password' => 'admin123',
        ]);

        collect([
            ['name' => 'Mercado Central', 'color' => '#177245', 'description' => 'Ofertas frescas para toda a família.'],
            ['name' => 'Super Econômico', 'color' => '#D7432F', 'description' => 'Preço baixo perto de você.'],
            ['name' => 'Bom Vizinho', 'color' => '#1769AA', 'description' => 'Tudo para a sua casa.'],
        ])->each(fn ($market) => Supermarket::firstOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($market['name'])],
            $market + ['is_active' => true]
        ));
    }
}
