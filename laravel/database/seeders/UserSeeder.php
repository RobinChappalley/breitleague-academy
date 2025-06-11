<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Progression;
use Illuminate\Support\Facades\Hash;
use App\Models\Question;
use App\Models\Lesson;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/users.json');
        $users = json_decode(file_get_contents($path), true);

        $allQuestionIds = Question::pluck('id')->toArray();

        $lesson1Questions = Question::whereHas('theory.lesson', function ($query) {
            $query->where('module_id', 1)->where('id', 1);
        })->pluck('id')->toArray();

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

            //préparer la progression en fonction de is_BS
            $lastCheckpointId = 0;
            $lastLessonId = 0;
            $answeredQuestions = [];

            if ($userData['is_BS']) {
                // cas général pour les is_BS == true
                $lastCheckpointId = 3;
                $lastLessonId = 11;
                $answeredQuestions = $allQuestionIds;
            }

            if ($userData['id'] == 12) {
                // cas spécial user 12 → toutes les questions de lesson 1
                $lastCheckpointId = 0;
                $lastLessonId = 1;
                $answeredQuestions = $lesson1Questions;
            }

            if ($userData['id'] == 13) {
                // cas spécial user 13 → moitié des questions de lesson 1
                $lastCheckpointId = 0;
                $lastLessonId = 1;
                $halfCount = (int) ceil(count($lesson1Questions) / 2);
                $answeredQuestions = array_slice($lesson1Questions, 0, $halfCount);
            }

            //Crée la progression liée
            $user->progression()->create([
                'last_lesson_id' => $lastLessonId,
                'last_checkpoint_id' => $lastCheckpointId,
                'idofquestionscorrectlyanswered' => $answeredQuestions,
            ]);
            // Attache le reward avec l'id 1
            $user->rewards()->attach(1, [
                'is_favourite' => false,
                'acquired_at' => now(),
            ]);
        }
    }
}
