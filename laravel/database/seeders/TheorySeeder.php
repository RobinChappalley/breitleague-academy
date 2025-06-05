<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TheorySeeder extends Seeder
{
    public function run()
    {
        $theories = json_decode(file_get_contents(database_path('seeders/data/theories.json')), true);

        foreach ($theories as $theory) {
            DB::table('theories')->insert([
                'id' => $theory['id'],
                'lesson_id' => $theory['lesson_id'],
                'content' => $theory['content'],
                'position_order' => $theory['position_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
