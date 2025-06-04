<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\ModuleController;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
