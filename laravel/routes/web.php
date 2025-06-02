<?php

use Illuminate\Support\Facades\Route;

// Main route - Using Vue.js template
Route::get('/', function () {
    return view('vue');
});

// Original Laravel welcome page (keeping for reference)
Route::get('/welcome', function () {
    return view('welcome');
});
