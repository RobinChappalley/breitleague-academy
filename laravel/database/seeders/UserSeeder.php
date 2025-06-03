<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/users.json');
        $users = json_decode(file_get_contents($path), true);

        foreach ($users as $user) {
            DB::table('users')->insert([
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']), // assume plain text in JSON
                'POS_id' => $user['POS_id'],
                'signup_year' => $user['signup_year'],
                'avatar' => $user['avatar'],
                'elo_score' => $user['elo_score'],
                'battle_won' => $user['battle_won'],
                'battle_lost' => $user['battle_lost'],
                'number_available_slots' => $user['number_available_slots'],
                'is_BS' => $user['is_BS']
            ]);
        }
    }
}
