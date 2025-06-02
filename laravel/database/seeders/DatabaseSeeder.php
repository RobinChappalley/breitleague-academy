<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Optionnel : créer un utilisateur test
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Appel des seeders personnalisés
        $this->call([
            TheorySeeder::class,
            QuestionSeeder::class,
            ChoiceSeeder::class,
        ]);
    }
}
