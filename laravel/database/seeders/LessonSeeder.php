<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;



class LessonSeeder extends Seeder
{
    public function run()
    {
        $lessons = json_decode(file_get_contents(database_path('seeders/lessons.json')), true);

        foreach ($lessons as $lesson) {
            DB::table('lessons')->insert([
                'id' => $lesson['id'],
                'lesson_id' => $lesson['lesson_id'],
                'content' => $lesson['content'],
                'position_order' => $lesson['position_order']
            ]);
        }
    }
}
