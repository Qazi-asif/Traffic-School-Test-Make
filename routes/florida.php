<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\Florida\CoursePlayerController;
use App\Http\Controllers\Student\Florida\QuizController;
use App\Http\Controllers\Student\Florida\CertificateController;

// Florida Student Routes
Route::get('/', [CoursePlayerController::class, 'index'])->name('florida.dashboard');
Route::get('/courses', [CoursePlayerController::class, 'index'])->name('florida.courses');
Route::get('/course-player/{id}', [CoursePlayerController::class, 'show'])->name('florida.course-player');
Route::post('/course/{id}/start', [CoursePlayerController::class, 'startCourse'])->name('florida.course.start');
Route::post('/course/{courseId}/chapter/{chapterId}/complete', [CoursePlayerController::class, 'completeChapter'])->name('florida.chapter.complete');
Route::get('/course/{courseId}/chapter/{chapterId}/next', [CoursePlayerController::class, 'nextChapter'])->name('florida.chapter.next');

Route::get('/quiz/{id}', [QuizController::class, 'show'])->name('florida.quiz');
Route::post('/quiz/{id}/submit', [QuizController::class, 'submit'])->name('florida.quiz.submit');
Route::get('/quiz/{id}/results', [QuizController::class, 'results'])->name('florida.quiz.results');
Route::post('/quiz/{id}/retry', [QuizController::class, 'retry'])->name('florida.quiz.retry');

Route::get('/certificates', [CertificateController::class, 'index'])->name('florida.certificates');
Route::get('/certificate/{id}', [CertificateController::class, 'show'])->name('florida.certificate.show');
Route::get('/certificate/{id}/download', [CertificateController::class, 'download'])->name('florida.certificate.download');