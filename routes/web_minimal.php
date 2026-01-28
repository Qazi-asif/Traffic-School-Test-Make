<?php

use Illuminate\Support\Facades\Route;

// Minimal test route
Route::get('/test-minimal', function () {
    return 'Minimal routing works!';
});

// Florida test route
Route::get('/florida', function () {
    return '<h1>✅ Florida Traffic School</h1><p>Minimal routing working!</p>';
});

// Missouri test route
Route::get('/missouri', function () {
    return '<h1>✅ Missouri Traffic School</h1><p>Minimal routing working!</p>';
});

// Texas test route
Route::get('/texas', function () {
    return '<h1>✅ Texas Traffic School</h1><p>Minimal routing working!</p>';
});

// Delaware test route
Route::get('/delaware', function () {
    return '<h1>✅ Delaware Traffic School</h1><p>Minimal routing working!</p>';
});