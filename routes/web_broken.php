<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Simple test route - should work without any dependencies
Route::get('/test-basic', function () {
    return 'Basic routing is working! Time: ' . date('Y-m-d H:i:s');
});

// Basic Laravel routes
Route::get('/', function () {
    return redirect('/dashboard');
});

// Authentication routes
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard route
Route::get('/dashboard', function () {
    return '<h1>Dashboard</h1><p>Basic routing working</p>';
});

// ========================================
// STATE-SEPARATED ROUTING SYSTEM - CLEAN
// ========================================

// Florida Routes
Route::prefix('florida')->group(function() {
    Route::get('/', function() {
        return '<h1>✅ Florida Traffic School</h1><p>State routing working!</p><p>Time: ' . now() . '</p>';
    })->name('florida.dashboard');
    
    Route::get('/test-controller', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Florida\CoursePlayerController();
            return '<h1>✅ Florida Controller Test</h1><p>Controller loaded: ' . get_class($controller) . '</p>';
        } catch (Exception $e) {
            return '<h1>❌ Controller Error</h1><p>' . $e->getMessage() . '</p>';
        }
    })->name('florida.test-controller');
});

// Missouri Routes  
Route::prefix('missouri')->group(function() {
    Route::get('/', function() {
        return '<h1>✅ Missouri Traffic School</h1><p>State routing working!</p><p>Time: ' . now() . '</p>';
    })->name('missouri.dashboard');
});

// Texas Routes
Route::prefix('texas')->group(function() {
    Route::get('/', function() {
        return '<h1>✅ Texas Traffic School</h1><p>State routing working!</p><p>Time: ' . now() . '</p>';
    })->name('texas.dashboard');
});

// Delaware Routes
Route::prefix('delaware')->group(function() {
    Route::get('/', function() {
        return '<h1>✅ Delaware Traffic School</h1><p>State routing working!</p><p>Time: ' . now() . '</p>';
    })->name('delaware.dashboard');
});

// Admin State Routes
Route::prefix('admin')->group(function() {
    Route::get('/', function() {
        return '<h1>✅ Admin Dashboard</h1><p>Admin routing working!</p><p>Time: ' . now() . '</p>';
    })->name('admin.dashboard');
});

// ========================================
// END STATE-SEPARATED ROUTING SYSTEM  
// ========================================