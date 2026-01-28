<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\Missouri\CoursePlayerController;
use App\Http\Controllers\Student\Missouri\QuizController;
use App\Http\Controllers\Student\Missouri\CertificateController;

// Missouri Student Routes
Route::get('/', [CoursePlayerController::class, 'index'])->name('missouri.dashboard');
Route::get('/courses', [CoursePlayerController::class, 'index'])->name('missouri.courses');
Route::get('/course-player/{id}', [CoursePlayerController::class, 'show'])->name('missouri.course-player');
Route::post('/course/{id}/start', [CoursePlayerController::class, 'startCourse'])->name('missouri.course.start');
Route::post('/course/{courseId}/chapter/{chapterId}/complete', [CoursePlayerController::class, 'completeChapter'])->name('missouri.chapter.complete');

Route::get('/quiz/{id}', [QuizController::class, 'show'])->name('missouri.quiz');
Route::post('/quiz/{id}/submit', [QuizController::class, 'submit'])->name('missouri.quiz.submit');

Route::get('/certificates', [CertificateController::class, 'index'])->name('missouri.certificates');
Route::get('/certificate/{id}/download', [CertificateController::class, 'download'])->name('missouri.certificate.download');