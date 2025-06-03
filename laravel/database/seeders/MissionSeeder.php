<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        $missions = json_decode(file_get_contents(database_path('seeders/data/missions.json')), true);

        foreach ($missions as $mission) {
            DB::table('missions')->insert([
                'id' => $mission['id'],
                'description' => $mission['description'],
                'reward_id' => $mission['reward_id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
