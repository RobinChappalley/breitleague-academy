<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonSeeder extends Seeder
{
    public function run()
    {
        $lessons = json_decode(file_get_contents(database_path('seeders/data/lessons.json')), true);

        foreach ($lessons as $lesson) {
            DB::table('lessons')->insert([
                'id' => $lesson['id'],
                'title' => $lesson['title'],
                'description' => $lesson['description'],
                'module_id' => $lesson['module_id'],
                'order_position' => $lesson['order_position'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
