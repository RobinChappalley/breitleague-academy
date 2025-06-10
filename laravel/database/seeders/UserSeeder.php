<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Progression;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/users.json');
        $users = json_decode(file_get_contents($path), true);

        foreach ($users as $userData) {
            // Crée l'utilisateur
            $user = User::create([
                'id' => $userData['id'], // si tu veux forcer l'id (sinon Laravel l'auto-incrémente)
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make('123'),
                'pos_id' => $userData['pos_id'],
                'signup_year' => $userData['signup_year'],
                'avatar' => $userData['avatar'],
                'elo_score' => $userData['elo_score'],
                'battle_won' => $userData['battle_won'],
                'battle_lost' => $userData['battle_lost'],
                'number_available_slots' => $userData['number_available_slots'],
                'is_BS' => $userData['is_BS'],
            ]);

            // Crée la progression liée
            $user->progression()->create([
                'last_lesson_id' => 0,
                'last_checkpoint_id' => 0,
                'idofquestionscorrectlyanswered' => [],
            ]);
        }
    }
}
