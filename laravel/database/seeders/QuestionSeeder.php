<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;



class QuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = json_decode(file_get_contents(database_path('seeders/questions.json')), true);

        foreach ($questions as $question) {
            DB::table('questions')->insert([
                'id' => $question['id'],
                'content_default' => $question['content_default'],
                'content_if_TF' => $question['content_if_TF'],
                'content_if_blank' => $question['content_if_blank'],
                'isMatchable' => $question['isMatchable'],
                'checkpoint_id' => $question['checkpoint_id'],
                'theory_id' => $question['theory_id'],
                'position_order' => $question['position_order'],
                'correct_answer_text' => json_encode($question['correct_answer_text'])
            ]);
        }
    }
}
