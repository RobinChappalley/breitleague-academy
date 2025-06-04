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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
