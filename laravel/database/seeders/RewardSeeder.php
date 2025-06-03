<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RewardSeeder extends Seeder
{
    public function run()
    {
        $rewards = json_decode(file_get_contents(database_path('seeders/data/rewards.json')), true);

        foreach ($rewards as $reward) {
            DB::table('rewards')->insert([
                'id' => $reward['id'],
                'photo_name' => $reward['photo_name'],
                'model' => $reward['model'],
                'date' => $reward['date'],
                'size' => $reward['size'],
                'colors' => implode(', ', $reward['colors']),
                'bracelet' => $reward['bracelet'],
                'description' => $reward['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
