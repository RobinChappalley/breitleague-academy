<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\CheckpointController;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\TheoryController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ChoiceController;
use App\Http\Controllers\Api\ProgressionController;
use App\Http\Controllers\Api\BattleController;
use App\Http\Controllers\Api\UserRewardController;
use App\Http\Controllers\Api\UserMissionController;
use Illuminate\Support\Facades\Auth;

Route::prefix('v1')->group(function () {
    Route::apiResource('pos', PosController::class)->parameters([
        'pos' => 'pos',
    ]);
    Route::apiResource('users', UserController::class)->parameters([
        'users' => 'user'
    ]);
    Route::apiResource('rewards', RewardController::class)->parameters([
        'rewards' => 'reward'
    ]);
    Route::apiResource('modules', ModuleController::class)->parameters([
        'modules' => 'module'
    ]);
    Route::apiResource('checkpoints', CheckpointController::class)->parameters([
        'checkpoints' => 'checkpoint'
    ]);
    Route::apiResource('missions', MissionController::class)->parameters([
        'missions' => 'mission'
    ]);
    Route::apiResource('lessons', LessonController::class)->parameters([
        'lessons' => 'lesson'
    ]);
    Route::apiResource('theories', TheoryController::class)->parameters([
        'theories' => 'theory'
    ]);
    Route::apiResource('questions', QuestionController::class)->parameters([
        'questions' => 'question'
    ]);
    Route::apiResource('choices', ChoiceController::class)->parameters([
        'choices' => 'choice'
    ]);
    Route::apiResource('progression', ProgressionController::class);
    Route::apiResource('battles', BattleController::class)->parameters([
        'battles' => 'battle'
    ]);
    Route::patch('battles/{battle}/status', [BattleController::class, 'updateStatus']);
    Route::apiResource('user-rewards', UserRewardController::class)->parameters([
        'user-rewards' => 'userReward'
    ]);
    Route::apiResource('user-missions', UserMissionController::class)->parameters([
        'user-missions' => 'userMission'
    ]);
});
