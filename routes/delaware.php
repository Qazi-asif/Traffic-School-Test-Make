<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\Delaware\CoursePlayerController;
use App\Http\Controllers\Student\Delaware\QuizController;
use App\Http\Controllers\Student\Delaware\CertificateController;

// Delaware Student Routes
Route::get('/', [CoursePlayerController::class, 'index'])->name('delaware.dashboard');
Route::get('/courses', [CoursePlayerController::class, 'index'])->name('delaware.courses');
Route::get('/course-player/{id}', [CoursePlayerController::class, 'show'])->name('delaware.course-player');

Route::get('/quiz/{id}', [QuizController::class, 'show'])->name('delaware.quiz');
Route::get('/certificates', [CertificateController::class, 'index'])->name('delaware.certificates');