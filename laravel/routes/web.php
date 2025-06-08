<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;

// Main route - Using Vue.js template
Route::get('/', function () {
    return view('vue');
});

// Original Laravel welcome page (keeping for reference)
Route::get('/welcome', function () {
    return view('welcome');
});
Route::middleware('auth:sanctum')->get('api/user', function () {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }

    return response()->json([
        'id' => $user->id,
        'username' => $user->username,
        'email' => $user->email,
    ]);
});

Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
        'message' => 'Logged out successfully',
    ]);
})->middleware('auth');
