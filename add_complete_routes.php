<?php
/**
 * Add Complete Routes for All Migrated Functionality
 */

echo "🔗 ADDING COMPLETE ROUTES FOR ALL FUNCTIONALITY\n";
echo "==============================================\n\n";

$routesContent = '
// ========================================
// COMPLETE SYSTEM ROUTES - ALL FUNCTIONALITY
// ========================================

// Course Player Routes
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/course/player\', [App\Http\Controllers\CoursePlayerController::class, \'index\'])->name(\'course.player\');
    Route::get(\'/course/{enrollmentId}/chapter/{chapterId}\', [App\Http\Controllers\CoursePlayerController::class, \'getChapter\'])->name(\'course.chapter\');
    Route::post(\'/course/{enrollmentId}/chapter/{chapterId}/quiz\', [App\Http\Controllers\CoursePlayerController::class, \'submitQuiz\'])->name(\'course.quiz.submit\');
    Route::post(\'/course/{enrollmentId}/chapter/{chapterId}/complete\', [App\Http\Controllers\CoursePlayerController::class, \'completeChapter\'])->name(\'course.chapter.complete\');
    Route::get(\'/course/{enrollmentId}/progress\', [App\Http\Controllers\CoursePlayerController::class, \'getProgress\'])->name(\'course.progress\');
});

// Student Dashboard Routes
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/student/dashboard\', [App\Http\Controllers\StudentDashboardController::class, \'index\'])->name(\'student.dashboard\');
    Route::get(\'/student/courses\', [App\Http\Controllers\StudentDashboardController::class, \'courses\'])->name(\'student.courses\');
    Route::get(\'/student/progress\', [App\Http\Controllers\StudentDashboardController::class, \'progress\'])->name(\'student.progress\');
    Route::get(\'/student/certificates\', [App\Http\Controllers\StudentDashboardController::class, \'certificates\'])->name(\'student.certificates\');
    Route::get(\'/student/profile\', [App\Http\Controllers\StudentDashboardController::class, \'profile\'])->name(\'student.profile\');
    Route::post(\'/student/profile\', [App\Http\Controllers\StudentDashboardController::class, \'updateProfile\'])->name(\'student.profile.update\');
});

// Quiz System Routes
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/quiz/{chapterId}\', [App\Http\Controllers\QuizController::class, \'show\'])->name(\'quiz.show\');
    Route::post(\'/quiz/{chapterId}/submit\', [App\Http\Controllers\QuizController::class, \'submit\'])->name(\'quiz.submit\');
    Route::get(\'/quiz/{chapterId}/results\', [App\Http\Controllers\QuizController::class, \'results\'])->name(\'quiz.results\');
    Route::post(\'/quiz/{chapterId}/retry\', [App\Http\Controllers\QuizController::class, \'retry\'])->name(\'quiz.retry\');
});

// Final Exam Routes
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/final-exam/{enrollmentId}\', [App\Http\Controllers\FinalExamController::class, \'show\'])->name(\'final-exam.show\');
    Route::post(\'/final-exam/{enrollmentId}/submit\', [App\Http\Controllers\FinalExamController::class, \'submit\'])->name(\'final-exam.submit\');
    Route::get(\'/final-exam/{enrollmentId}/results\', [App\Http\Controllers\FinalExamController::class, \'results\'])->name(\'final-exam.results\');
});

// Certificate Routes (Enhanced)
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/certificate/view\', [App\Http\Controllers\CertificateController::class, \'view\'])->name(\'certificate.view\');
    Route::get(\'/certificate/generate\', [App\Http\Controllers\CertificateController::class, \'generate\'])->name(\'certificate.generate\');
    Route::get(\'/certificate/download/{enrollmentId}\', [App\Http\Controllers\CertificateController::class, \'download\'])->name(\'certificate.download\');
    Route::get(\'/certificate/verify/{hash}\', [App\Http\Controllers\CertificateController::class, \'verify\'])->name(\'certificate.verify\');
});

// Payment System Routes
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/payment/checkout/{courseId}\', [App\Http\Controllers\PaymentController::class, \'checkout\'])->name(\'payment.checkout\');
    Route::post(\'/payment/process\', [App\Http\Controllers\PaymentController::class, \'process\'])->name(\'payment.process\');
    Route::get(\'/payment/success\', [App\Http\Controllers\PaymentController::class, \'success\'])->name(\'payment.success\');
    Route::get(\'/payment/cancel\', [App\Http\Controllers\PaymentController::class, \'cancel\'])->name(\'payment.cancel\');
    Route::get(\'/payment/history\', [App\Http\Controllers\PaymentController::class, \'history\'])->name(\'payment.history\');
});

// Admin Panel Routes
Route::middleware([\'auth\', \'admin\'])->prefix(\'admin\')->name(\'admin.\')->group(function () {
    Route::get(\'/dashboard\', [App\Http\Controllers\Admin\DashboardController::class, \'index\'])->name(\'dashboard\');
    
    // User Management
    Route::resource(\'users\', App\Http\Controllers\Admin\UserController::class);
    Route::post(\'users/{user}/toggle-status\', [App\Http\Controllers\Admin\UserController::class, \'toggleStatus\'])->name(\'users.toggle-status\');
    
    // Course Management
    Route::resource(\'courses\', App\Http\Controllers\Admin\CourseController::class);
    Route::post(\'courses/{course}/toggle-status\', [App\Http\Controllers\Admin\CourseController::class, \'toggleStatus\'])->name(\'courses.toggle-status\');
    
    // Enrollment Management
    Route::get(\'enrollments\', [App\Http\Controllers\Admin\EnrollmentController::class, \'index\'])->name(\'enrollments.index\');
    Route::get(\'enrollments/{enrollment}\', [App\Http\Controllers\Admin\EnrollmentController::class, \'show\'])->name(\'enrollments.show\');
    Route::post(\'enrollments/{enrollment}/complete\', [App\Http\Controllers\Admin\EnrollmentController::class, \'complete\'])->name(\'enrollments.complete\');
    
    // Certificate Management
    Route::get(\'certificates\', [App\Http\Controllers\Admin\CertificateController::class, \'index\'])->name(\'certificates.index\');
    Route::post(\'certificates/bulk-generate\', [App\Http\Controllers\Admin\CertificateController::class, \'bulkGenerate\'])->name(\'certificates.bulk-generate\');
    
    // Reports
    Route::get(\'reports\', [App\Http\Controllers\Admin\ReportController::class, \'index\'])->name(\'reports.index\');
    Route::get(\'reports/enrollments\', [App\Http\Controllers\Admin\ReportController::class, \'enrollments\'])->name(\'reports.enrollments\');
    Route::get(\'reports/certificates\', [App\Http\Controllers\Admin\ReportController::class, \'certificates\'])->name(\'reports.certificates\');
    Route::get(\'reports/revenue\', [App\Http\Controllers\Admin\ReportController::class, \'revenue\'])->name(\'reports.revenue\');
    
    // Settings
    Route::get(\'settings\', [App\Http\Controllers\Admin\SettingsController::class, \'index\'])->name(\'settings.index\');
    Route::post(\'settings\', [App\Http\Controllers\Admin\SettingsController::class, \'update\'])->name(\'settings.update\');
});

// API Routes for AJAX functionality
Route::middleware([\'auth\'])->prefix(\'api\')->group(function () {
    // Progress API
    Route::get(\'progress/{enrollmentId}\', [App\Http\Controllers\Api\ProgressApiController::class, \'getProgress\']);
    Route::post(\'progress/{enrollmentId}/recalculate\', [App\Http\Controllers\Api\ProgressApiController::class, \'recalculateProgress\']);
    
    // Quiz API
    Route::get(\'quiz/{chapterId}/questions\', [App\Http\Controllers\Api\QuizApiController::class, \'getQuestions\']);
    Route::post(\'quiz/{chapterId}/answer\', [App\Http\Controllers\Api\QuizApiController::class, \'saveAnswer\']);
    
    // Dashboard API
    Route::get(\'dashboard/stats\', [App\Http\Controllers\Api\DashboardApiController::class, \'getStats\']);
    Route::get(\'dashboard/activity\', [App\Http\Controllers\Api\DashboardApiController::class, \'getActivity\']);
    
    // Certificate API
    Route::post(\'certificate/generate/{enrollmentId}\', [App\Http\Controllers\Api\CertificateApiController::class, \'generate\']);
    Route::get(\'certificate/status/{enrollmentId}\', [App\Http\Controllers\Api\CertificateApiController::class, \'getStatus\']);
});

// Notification Routes
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/notifications\', [App\Http\Controllers\NotificationController::class, \'index\'])->name(\'notifications.index\');
    Route::post(\'/notifications/{notification}/mark-read\', [App\Http\Controllers\NotificationController::class, \'markAsRead\'])->name(\'notifications.mark-read\');
    Route::post(\'/notifications/mark-all-read\', [App\Http\Controllers\NotificationController::class, \'markAllAsRead\'])->name(\'notifications.mark-all-read\');
});

// Support Routes
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/support\', [App\Http\Controllers\SupportController::class, \'index\'])->name(\'support.index\');
    Route::post(\'/support/ticket\', [App\Http\Controllers\SupportController::class, \'createTicket\'])->name(\'support.ticket\');
    Route::get(\'/support/faq\', [App\Http\Controllers\SupportController::class, \'faq\'])->name(\'support.faq\');
});

// State-specific dashboard redirects
Route::middleware([\'auth\'])->group(function () {
    Route::get(\'/florida/dashboard\', function() {
        return view(\'student.dashboard\')->with(\'state\', \'florida\');
    })->name(\'florida.dashboard\');
    
    Route::get(\'/missouri/dashboard\', function() {
        return view(\'student.dashboard\')->with(\'state\', \'missouri\');
    })->name(\'missouri.dashboard\');
    
    Route::get(\'/texas/dashboard\', function() {
        return view(\'student.dashboard\')->with(\'state\', \'texas\');
    })->name(\'texas.dashboard\');
    
    Route::get(\'/delaware/dashboard\', function() {
        return view(\'student.dashboard\')->with(\'state\', \'delaware\');
    })->name(\'delaware.dashboard\');
});

// ========================================
// END COMPLETE SYSTEM ROUTES
// ========================================
';

// Add routes to web.php
$webRoutesPath = 'routes/web.php';
$currentRoutes = file_get_contents($webRoutesPath);

// Check if routes already exist
if (strpos($currentRoutes, 'COMPLETE SYSTEM ROUTES') === false) {
    file_put_contents($webRoutesPath, $currentRoutes . $routesContent);
    echo "✅ Added complete system routes to web.php\n";
} else {
    echo "✅ Complete system routes already exist\n";
}

echo "\n📋 ROUTES ADDED:\n";
echo "- Course Player: /course/player\n";
echo "- Student Dashboard: /student/dashboard\n";
echo "- Quiz System: /quiz/{chapterId}\n";
echo "- Final Exam: /final-exam/{enrollmentId}\n";
echo "- Certificates: /certificate/*\n";
echo "- Payment: /payment/*\n";
echo "- Admin Panel: /admin/*\n";
echo "- API Endpoints: /api/*\n";
echo "- Support: /support\n";
echo "- Notifications: /notifications\n\n";

echo "🎯 STATE-SPECIFIC DASHBOARDS:\n";
echo "- Florida: /florida/dashboard\n";
echo "- Missouri: /missouri/dashboard\n";
echo "- Texas: /texas/dashboard\n";
echo "- Delaware: /delaware/dashboard\n\n";

echo "✅ All routes configured for complete system functionality!\n";