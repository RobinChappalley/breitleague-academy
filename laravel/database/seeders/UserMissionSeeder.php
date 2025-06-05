<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Mission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UserMissionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $missions = Mission::all();

        foreach ($users as $user) {
            // Donner à chaque user entre 1 et 3 missions
            $randomMissions = $missions->random(rand(1, 3));

            foreach ($randomMissions as $mission) {
                // Dates aléatoires entre aujourd'hui et +/- 10 jours
                $start = Carbon::now()->subDays(rand(0, 10));
                $end = (clone $start)->addDays(rand(3, 7));
                $completed = rand(0, 1) === 1;

                DB::table('user_missions')->insert([
                    'user_id' => $user->id,
                    'mission_id' => $mission->id,
                    'is_completed' => $completed,
                    'start_date' => $start,
                    'end_date' => $end,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
