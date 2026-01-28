<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\Texas\CoursePlayerController;
use App\Http\Controllers\Student\Texas\QuizController;
use App\Http\Controllers\Student\Texas\CertificateController;

// Texas Student Routes
Route::get('/', [CoursePlayerController::class, 'index'])->name('texas.dashboard');
Route::get('/courses', [CoursePlayerController::class, 'index'])->name('texas.courses');
Route::get('/course-player/{id}', [CoursePlayerController::class, 'show'])->name('texas.course-player');
Route::post('/course/{id}/start', [CoursePlayerController::class, 'startCourse'])->name('texas.course.start');

Route::get('/quiz/{id}', [QuizController::class, 'show'])->name('texas.quiz');
Route::post('/quiz/{id}/submit', [QuizController::class, 'submit'])->name('texas.quiz.submit');

Route::get('/certificates', [CertificateController::class, 'index'])->name('texas.certificates');