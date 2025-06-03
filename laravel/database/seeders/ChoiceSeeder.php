<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;



class ChoiceSeeder extends Seeder
{
    public function run()
    {
        $choices = json_decode(file_get_contents(database_path('seeders/data/choices.json')), true);

        foreach ($choices as $choice) {
            DB::table('choices')->insert([
                'id' => $choice['id'],
                'question_id' => $choice['question_id'],
                'text_answer' => $choice['text_answer'],
                'img_answer' => $choice['img_answer']
            ]);
        }
    }
}
