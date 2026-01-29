<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FloridaSoapProxyController;
use App\Http\Controllers\SecurityVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// CSRF Token Refresh API
Route::get('/api/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
});

// Public quiz result endpoint (no auth required)
Route::get('/chapters/{chapterId}/quiz-result', function ($chapterId) {
    try {
        $enrollmentId = request('enrollment_id');
        
        if (!$enrollmentId) {
            return response()->json(['quiz_result' => null]);
        }
        
        // Get user_id from enrollment
        $enrollment = \DB::table('user_course_enrollments')->where('id', $enrollmentId)->first();
        if (!$enrollment) {
            return response()->json(['quiz_result' => null]);
        }
        
        $result = \DB::table('chapter_quiz_results')
            ->where('user_id', $enrollment->user_id)
            ->where('chapter_id', $chapterId)
            ->orderBy('created_at', 'desc')
            ->first();
            
        return response()->json(['quiz_result' => $result]);
        
    } catch (\Exception $e) {
        \Log::error('API: Quiz result error: ' . $e->getMessage());
        return response()->json(['quiz_result' => null]);
    }
});

// Public chapter quiz results endpoint (no auth required)
Route::post('/chapter-quiz-results', function (\Illuminate\Http\Request $request) {
    try {
        $data = $request->all();
        
        // Get user_id from enrollment
        $enrollment = \DB::table('user_course_enrollments')->where('id', $data['enrollment_id'])->first();
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        \DB::table('chapter_quiz_results')->insert([
            'user_id' => $enrollment->user_id,
            'chapter_id' => $data['chapter_id'],
            'total_questions' => $data['total_questions'],
            'correct_answers' => $data['correct_answers'],
            'wrong_answers' => $data['wrong_answers'],
            'percentage' => $data['percentage'],
            'answers' => json_encode($data['answers']),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Get quiz average
        $quizAverage = \DB::table('chapter_quiz_results')
            ->where('user_id', $enrollment->user_id)
            ->avg('percentage');
        
        return response()->json([
            'success' => true,
            'quiz_average' => round($quizAverage, 2)
        ]);
    } catch (\Exception $e) {
        \Log::error('Quiz results error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Public security endpoints (no auth required)

// CSRF Test Route
Route::get('/test-csrf', function () {
    return view('test-csrf');
});

Route::post('/test-csrf', function (Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'required|string|max:1000',
    ]);
    
    return redirect('/test-csrf')->with('success', 'Form submitted successfully! CSRF protection is working.');
});

// Multi-State Course Player Routes
Route::middleware(['auth'])->group(function () {
    // Main course player route
    Route::get('/course-player/{enrollmentId}', [App\Http\Controllers\CoursePlayerController::class, 'index'])->name('course.player');
    
    // Chapter and quiz routes
    Route::get('/web/enrollments/{enrollmentId}/chapters/{chapterId}', [App\Http\Controllers\CoursePlayerController::class, 'getChapter'])->name('course.chapter');
    Route::post('/web/enrollments/{enrollmentId}/chapters/{chapterId}/quiz', [App\Http\Controllers\CoursePlayerController::class, 'submitQuiz'])->name('course.quiz.submit');
    Route::post('/web/enrollments/{enrollmentId}/complete-chapter/{chapterId}', [App\Http\Controllers\CoursePlayerController::class, 'completeChapter'])->name('course.chapter.complete');
    Route::get('/web/enrollments/{enrollmentId}/progress', [App\Http\Controllers\CoursePlayerController::class, 'getProgress'])->name('course.progress');
    
    // Enrollment and course data routes
    Route::get('/web/enrollments/{enrollmentId}', function($enrollmentId) {
        $enrollment = \App\Models\UserCourseEnrollment::with(['user'])
            ->where('id', $enrollmentId)
            ->where('user_id', auth()->id())
            ->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        // Get course data based on course_table
        $course = null;
        switch ($enrollment->course_table) {
            case 'florida_courses':
                $course = \App\Models\FloridaCourse::find($enrollment->course_id);
                break;
            case 'missouri_courses':
                $course = \App\Models\Missouri\Course::find($enrollment->course_id);
                break;
            case 'texas_courses':
                $course = \App\Models\Texas\Course::find($enrollment->course_id);
                break;
            case 'delaware_courses':
                $course = \App\Models\Delaware\Course::find($enrollment->course_id);
                break;
            default:
                $course = \App\Models\Course::find($enrollment->course_id);
                break;
        }
        
        $enrollment->course = $course;
        
        return response()->json($enrollment);
    });
    
    // Student route for chapters with enrollment progress
    Route::get('/web/enrollments/{enrollmentId}/chapters', function($enrollmentId) {
        $enrollment = \App\Models\UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', auth()->id())
            ->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        // Get chapters with progress
        $chapters = \App\Models\Chapter::where('course_id', $enrollment->course_id)
            ->where('course_table', $enrollment->course_table ?? 'courses')
            ->where('is_active', true)
            ->orderBy('order_index')
            ->get();
        
        // Add progress information
        $progress = \DB::table('user_course_progress')
            ->where('enrollment_id', $enrollment->id)
            ->get()
            ->keyBy('chapter_id');
        
        foreach ($chapters as $chapter) {
            $chapterProgress = $progress->get($chapter->id);
            $chapter->is_completed = $chapterProgress ? $chapterProgress->is_completed : false;
            $chapter->progress_percentage = $chapterProgress ? $chapterProgress->progress_percentage : 0;
            $chapter->started_at = $chapterProgress ? $chapterProgress->started_at : null;
            $chapter->completed_at = $chapterProgress ? $chapterProgress->completed_at : null;
        }
        
        return response()->json($chapters);
    });
    
    // Multi-state final exam routes
    Route::get('/web/enrollments/{enrollmentId}/final-exam/questions', [App\Http\Controllers\MultiStateFinalExamController::class, 'getExamQuestions'])->name('final-exam.questions');
    Route::post('/web/enrollments/{enrollmentId}/final-exam/submit', [App\Http\Controllers\MultiStateFinalExamController::class, 'submitExam'])->name('final-exam.submit');
    
    // Chapter questions API
    Route::get('/api/chapters/{chapterId}/questions', function($chapterId) {
        $questions = \App\Models\ChapterQuestion::where('chapter_id', $chapterId)
            ->where('is_active', true)
            ->orderBy('order_index')
            ->get();
        
        return response()->json($questions);
    });
});

// Florida SOAP Proxy - allows requests from any IP to reach Florida's server
Route::post('/api/florida-soap-proxy', [FloridaSoapProxyController::class, 'proxy']);

// Maintenance Mode Control Routes (Admin Only - Keep Secret)
Route::prefix('admin-maintenance-cbfbvib4767436667gdgdggdgfgfdfghdgh')->group(function () {
    Route::get('/', [App\Http\Controllers\MaintenanceController::class, 'index'])->name('maintenance.control');
    Route::match(['GET', 'POST'], '/enable', [App\Http\Controllers\MaintenanceController::class, 'enable'])->name('maintenance.enable');
    Route::match(['GET', 'POST'], '/disable', [App\Http\Controllers\MaintenanceController::class, 'disable'])->name('maintenance.disable');
});

// Direct PHP file execution route (bypasses Laravel entirely)
Route::get('/maintenance-direct-cbfbvib4767436667gdgdggdgfgfdfghdgh', function() {
    // Execute the original PHP file directly
    $phpFile = public_path('maintenancecbfbvib4767436667gdgdggdgfgfdfghdgh.php');
    
    if (file_exists($phpFile)) {
        // Capture the output of the PHP file
        ob_start();
        include $phpFile;
        $content = ob_get_clean();
        
        return response($content)->header('Content-Type', 'text/html');
    }
    
    return response('Maintenance file not found', 404);
});

// Test route for timer system
Route::get('/test-timer', function () {
    // Check if user is authenticated
    if (!auth()->check()) {
        return redirect('/login')->with('message', 'Please log in to test the timer system.');
    }
    
    return view('test-timer');
})->middleware('auth');

// Test route for middleware blocking (no auth required)
Route::get('/test-admin-panel', function () {
    return response()->json([
        'message' => 'Admin panel test route - should be blocked if middleware works',
        'timestamp' => now()
    ]);
})->middleware(\App\Http\Middleware\DirectBlockMiddleware::class);

// Create test timer configuration
Route::get('/create-test-timer', function () {
    try {
        // Create a test timer for chapter 1
        $timer = \App\Models\CourseTimer::updateOrCreate(
            [
                'chapter_id' => 1,
                'chapter_type' => 'chapters',
            ],
            [
                'required_time_minutes' => 2, // 2 minutes for testing
                'is_enabled' => true,
                'allow_pause' => false,
                'bypass_for_admin' => false,
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Test timer created successfully',
            'timer' => $timer
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
})->middleware('auth');

// File serving route with different pattern
Route::get('files/{filename}', function ($filename) {
    $path = storage_path('app/public/course-media/'.$filename);

    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');

// Direct file serving route
Route::get('storage/course-media/1761175955_indian-man-7061278_640__1_.jpg', function () {
    $path = storage_path('app/public/course-media/1761175955_indian-man-7061278_640__1_.jpg');

    return response()->file($path);
});

// Debug route to test file access
Route::get('test-file', function () {
    $filename = '1761175955_indian-man-7061278_640__1_.jpg';
    $path = storage_path('app/public/course-media/'.$filename);

    return response()->json([
        'filename' => $filename,
        'path' => $path,
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'size' => file_exists($path) ? filesize($path) : 0,
        'storage_path' => storage_path('app/public/course-media/'),
        'files_in_dir' => scandir(storage_path('app/public/course-media/')),
    ]);
});

// Storage route for serving files
Route::get('storage/course-media/{filename}', function ($filename) {
    $path = storage_path('app/public/course-media/'.$filename);

    \Log::info('Storage route hit', ['filename' => $filename, 'path' => $path, 'exists' => file_exists($path)]);

    if (! file_exists($path)) {
        \Log::error('File not found', ['path' => $path]);
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');

Route::get('/', function () {
    return redirect('/dashboard');
});

// Working test pages (no middleware for testing)
Route::get('/working-course-creation', function () {
    return view('working-course-creation');
})->name('working.course.creation');

Route::get('/working-docx-upload', function () {
    return view('working-docx-upload');
})->name('working.docx.upload');

Route::get('/test-docx-import', function () {
    return view('test-docx-import');
})->name('test.docx.import');

Route::get('/docx-status', function () {
    return view('docx-status');
})->name('docx.status');

// Include DICDS routes
Route::prefix('dicds')->group(base_path('routes/dicds.php'));

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [App\Http\Controllers\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [App\Http\Controllers\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\ForgotPasswordController::class, 'reset'])->name('password.update');

// Test email route (remove after testing)
Route::get('/test-email', function() {
    try {
        \Mail::raw('Test email from Laravel', function($message) {
            $message->to('test@example.com')->subject('Test Email');
        });
        return 'Email sent successfully!';
    } catch (\Exception $e) {
        return 'Email failed: ' . $e->getMessage();
    }
});

// Test route to check authentication status
Route::get('/auth-test', function () {
    \Log::info('Auth test route accessed', [
        'is_authenticated' => auth()->check(),
        'user' => auth()->user(),
        'session_token' => session('jwt_token'),
    ]);

    return response()->json([
        'is_authenticated' => auth()->check(),
        'user' => auth()->user(),
        'session_token' => session('jwt_token'),
        'session_id' => session()->getId(),
    ]);
});

// Test route with built-in auth middleware
Route::get('/auth-test-middleware', function () {
    return response()->json([
        'message' => 'You are authenticated!',
        'user' => auth()->user(),
    ]);
})->middleware('auth');

// Test route for module blocking (no auth required)
Route::get('/test-admin-panel', function () {
    return response('This should be blocked if admin_panel module is disabled');
})->middleware([\App\Http\Middleware\DirectBlockMiddleware::class]);

Route::get('/dashboard', function () {
    $user = Auth::user();
    
    if (!$user) {
        return redirect('/login');
    }
    
    // Keep users in the unified dashboard - no state redirects
    return view('dashboard');
})->middleware('auth');

Route::get('/courses', function () {
    return view('courses');
})->middleware('auth');

Route::get('/course-details/{table}/{courseId}', [App\Http\Controllers\CourseController::class, 'showDetails'])->middleware('auth')->name('course.details');

Route::get('/api/courses/public', [App\Http\Controllers\CourseController::class, 'publicIndex'])->middleware('auth');
Route::post('/api/check-enrollment', [App\Http\Controllers\EnrollmentController::class, 'checkEnrollment'])->middleware('auth');

Route::get('/certificates', function () {
    return view('certificates');
})->middleware('auth');

Route::get('/generate-certificates', function () {
    $user = auth()->user();
    $enrollments = \App\Models\UserCourseEnrollment::where('user_id', $user->id)
        ->where('status', 'completed')
        ->with('course')
        ->get()
        ->filter(function ($enrollment) {
            return $enrollment->course !== null;
        })
        ->values();

    return view('certificates.select', compact('enrollments'));
})->middleware('auth');

Route::get('/generate-certificate/{enrollment_id}', function ($enrollment_id) {
    $user = auth()->user();
    $enrollment = \App\Models\UserCourseEnrollment::where('user_id', $user->id)
        ->where('id', $enrollment_id)
        ->with('course')
        ->firstOrFail();

    // Check if course exists
    if (!$enrollment->course) {
        return redirect('/generate-certificates')
            ->with('error', 'Course not found for this enrollment.');
    }
    // Check if survey is required and completed
    $surveyService = app(\App\Services\SurveyService::class);
    if (! $surveyService->hasCompletedRequiredSurvey($enrollment)) {
        return redirect()->route('survey.show', $enrollment->id)
            ->with('message', 'Please complete the survey before receiving your certificate.');
    }

    $params = [
        'student_name' => $user->name,
        'completion_date' => $enrollment->completed_at ? 
            (is_string($enrollment->completed_at) ? 
                \Carbon\Carbon::parse($enrollment->completed_at)->format('m/d/Y') : 
                $enrollment->completed_at->format('m/d/Y')
            ) : now()->format('m/d/Y'),
        'score' => '95%',
        'course_name' => $enrollment->course?->title,
        'enrollment_id' => $enrollment->id,
    ];

    $review = \App\Models\Review::where('user_id', $user->id)
        ->where('enrollment_id', $enrollment_id)
        ->first();

    if ($review) {
        return redirect('/certificate?'.http_build_query($params));
    }

    return redirect('/review-course?'.http_build_query($params));
})->middleware('auth');

Route::get('/review-course', [App\Http\Controllers\ReviewController::class, 'show'])->middleware('auth')->name('review-course');
Route::post('/submit-review', [App\Http\Controllers\ReviewController::class, 'store'])->middleware('auth')->name('submit-review');

Route::get('/certificates/verify/{hash}', function ($hash) {
    $certificate = \App\Models\FloridaCertificate::where('verification_hash', $hash)->first();

    if (! $certificate) {
        abort(404, 'Certificate not found');
    }

    return view('certificates.verify', compact('certificate'));
});

// Serve storage files
Route::get('/files/{path}', function ($path) {
    $filePath = storage_path('app/public/'.$path);

    if (! file_exists($filePath)) {
        // Try course-media subdirectory
        $filePath = storage_path('app/public/course-media/'.$path);
        if (! file_exists($filePath)) {
            abort(404);
        }
    }

    return response()->file($filePath);
})->where('path', '.*');

Route::get('/my-certificates', function () {
    return view('my-certificates');
})->middleware('auth')->name('my-certificates');

Route::get('/open-ticket', function () {
    return view('open-ticket');
})->middleware('auth')->name('open-ticket');

Route::get('/my-enrollments', function () {
    return view('my-enrollments');
})->middleware('auth')->name('my-enrollments');

Route::get('/course-player/{enrollmentId}', function ($enrollmentId) {
    $enrollment = \App\Models\UserCourseEnrollment::where('id', $enrollmentId)
        ->where('user_id', auth()->id())
        ->first();

    if (! $enrollment) {
        return redirect('/dashboard')->with('error', 'Enrollment not found');
    }

    if ($enrollment->access_revoked) {
        return redirect('/dashboard')->with('error', 'Access to this course has been revoked after certificate download');
    }

    // Check payment status - redirect to payment if not paid
    if ($enrollment->payment_status !== 'paid') {
        return redirect()->route('payment.show', [
            'course_id' => $enrollment->course_id,
            'table' => $enrollment->course_table ?? 'florida_courses'
        ])->with('info', 'Please complete payment to access the course.');
    }

    // If course is completed, redirect to certificate generation (UNLESS user is admin)
    if ($enrollment->status === 'completed' && $enrollment->completed_at && !auth()->user()->isAdmin()) {
        return redirect('/generate-certificates')->with('success', 'Course completed! Generate your certificate below.');
    }
    return view('course-player');
})->middleware('auth')->name('course-player');

Route::get('/course-player', function () {
    $enrollmentId = request()->get('enrollmentId');

    if ($enrollmentId) {
        $enrollment = \App\Models\UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $enrollment) {
            return redirect('/dashboard')->with('error', 'Enrollment not found');
        }

        if ($enrollment->access_revoked) {
            return redirect('/dashboard')->with('error', 'Access to this course has been revoked after certificate download');
        }

        // Check payment status - redirect to payment if not paid
        if ($enrollment->payment_status !== 'paid') {
            return redirect()->route('payment.show', [
                'course_id' => $enrollment->course_id,
                'table' => $enrollment->course_table ?? 'florida_courses'
            ])->with('info', 'Please complete payment to access the course.');
        }

        // If course is completed, redirect to certificate generation (UNLESS user is admin)
        if ($enrollment->status === 'completed' && $enrollment->completed_at && !auth()->user()->isAdmin()) {
            return redirect('/generate-certificates')->with('success', 'Course completed! Generate your certificate below.');
        }
    }

    return view('course-player');
})->middleware('auth');

Route::get('/profile', function () {
    return view('profile');
})->middleware('auth');

Route::get('/account-security', function () {
    return view('admin.account-security');
})->middleware('auth');

Route::get('/my-payments', function () {
    return view('my-payments');
})->middleware('auth');

// Public invoice routes for users
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'showPublic'])->name('invoice.show');
    Route::get('/invoices/{invoice}/download', [App\Http\Controllers\InvoiceController::class, 'downloadPublic'])->name('invoice.download');
});

// Booklet routes for users
Route::middleware(['auth'])->prefix('booklets')->name('booklets.')->group(function () {
    Route::get('/', [App\Http\Controllers\BookletOrderController::class, 'index'])->name('index');
    Route::get('/order/{enrollment}', [App\Http\Controllers\BookletOrderController::class, 'create'])->name('create');
    Route::post('/order/{enrollment}', [App\Http\Controllers\BookletOrderController::class, 'store'])->name('store');
    Route::get('/{order}', [App\Http\Controllers\BookletOrderController::class, 'show'])->name('show');
    Route::get('/{order}/download', [App\Http\Controllers\BookletOrderController::class, 'download'])->name('download');
});

Route::get('/create-course', function () {
    return view('create-course');
})->middleware('auth', 'role:super-admin,admin');

// Web routes for course operations (using session auth)
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::post('/web/courses', [App\Http\Controllers\CourseController::class, 'storeWeb']);
    Route::match(['PUT', 'POST'], '/web/courses/{course}', [App\Http\Controllers\CourseController::class, 'updateWeb']);
    Route::delete('/web/courses/{course}', [App\Http\Controllers\CourseController::class, 'destroyWeb']);
    Route::post('/api/courses/copy', [App\Http\Controllers\CourseController::class, 'copy']);
    Route::post('/web/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'storeWeb']);
    Route::match(['PUT', 'POST'], '/web/chapters/{chapter}', [App\Http\Controllers\ChapterController::class, 'updateWeb']);
    Route::delete('/web/chapters/{chapter}', [App\Http\Controllers\ChapterController::class, 'destroyWeb']);
    Route::post('/api/upload-tinymce-image', [App\Http\Controllers\ChapterController::class, 'uploadTinyMceImage']);
    Route::post('/api/import-docx', [App\Http\Controllers\ChapterController::class, 'importDocx']);
    Route::post('/api/import-docx-images', [App\Http\Controllers\ChapterController::class, 'importDocxImages']);
});

// Payment routes
Route::middleware('auth')->group(function () {
    Route::get('/payment', [App\Http\Controllers\PaymentPageController::class, 'create'])->name('payment.create');
    Route::post('/payment/stripe', [App\Http\Controllers\PaymentPageController::class, 'processStripe'])->name('payment.stripe');
    Route::post('/payment/authorizenet', [App\Http\Controllers\PaymentPageController::class, 'processAuthorizenet'])->name('payment.authorizenet');
    Route::post('/payment/paypal', [App\Http\Controllers\PaymentPageController::class, 'processPaypal'])->name('payment.paypal');
    Route::post('/payment/dummy', [App\Http\Controllers\PaymentPageController::class, 'processDummy'])->name('payment.dummy');
    Route::get('/payment/success', [App\Http\Controllers\PaymentPageController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [App\Http\Controllers\PaymentPageController::class, 'cancel'])->name('payment.cancel');
});

// Web routes for enrollments (using session auth)
Route::middleware('auth')->group(function () {
    Route::post('/web/enrollments', [App\Http\Controllers\EnrollmentController::class, 'storeWeb']);
    Route::get('/web/my-enrollments', [App\Http\Controllers\EnrollmentController::class, 'myEnrollmentsWeb']);
    Route::post('/web/enrollments/{enrollment}/cancel', [App\Http\Controllers\EnrollmentController::class, 'cancelEnrollmentWeb']);
});

// Web routes for user profile (using session auth)
Route::middleware('auth')->group(function () {
    Route::get('/web/user', [App\Http\Controllers\AuthController::class, 'userWeb']);
    Route::put('/web/user', [App\Http\Controllers\AuthController::class, 'updateProfileWeb']);
    Route::get('/web/enrollments/{enrollment}', [App\Http\Controllers\EnrollmentController::class, 'showWeb']);
    Route::get('/web/enrollments/{enrollment}/feedback', [App\Http\Controllers\EnrollmentController::class, 'getFeedback']);
    Route::get('/web/courses', [App\Http\Controllers\CourseController::class, 'indexWeb']);
    Route::get('/web/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'indexWeb']);
    Route::match(['GET', 'POST'], '/web/enrollments/{enrollment}/complete-chapter/{chapter}', [App\Http\Controllers\ProgressController::class, 'completeChapterWeb']);
    Route::get('/web/my-payments', [App\Http\Controllers\PaymentController::class, 'myPaymentsWeb']);
    Route::post('/web/payments/retry', [App\Http\Controllers\PaymentController::class, 'retryPayment']);
    Route::post('/web/payments/cancel', [App\Http\Controllers\PaymentController::class, 'cancelPendingPayment']);
});

// Web routes for admin (using session auth)
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/web/users', [App\Http\Controllers\UserController::class, 'indexWeb']);
    Route::post('/web/users', [App\Http\Controllers\UserController::class, 'storeWeb']);
    Route::put('/web/users/{user}', [App\Http\Controllers\UserController::class, 'updateWeb']);
    Route::delete('/web/users/{user}', [App\Http\Controllers\UserController::class, 'destroyWeb']);
    Route::get('/web/enrollments', [App\Http\Controllers\EnrollmentController::class, 'indexWeb']);
    Route::get('/web/admin/reports', [App\Http\Controllers\ReportController::class, 'indexWeb']);
    Route::get('/web/admin/reports/generate', [App\Http\Controllers\ReportController::class, 'generateWeb']);
    Route::get('/web/admin/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'getStatsWeb']);

    // Admin payments CRUD
    Route::get('/web/admin/payments', [App\Http\Controllers\PaymentController::class, 'index']);
    Route::post('/web/admin/payments', [App\Http\Controllers\PaymentController::class, 'store']);
    Route::get('/web/admin/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'show']);
    Route::put('/web/admin/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'update']);
    Route::delete('/web/admin/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'destroy']);
    Route::post('/web/admin/payments/{payment}/refund', [App\Http\Controllers\PaymentController::class, 'refund']);
    Route::get('/web/admin/payments/{payment}/pdf', [App\Http\Controllers\PaymentController::class, 'downloadPDF']);
    Route::post('/web/admin/payments/{payment}/email', [App\Http\Controllers\PaymentController::class, 'emailReceipt']);

    // Missouri Form 4444 Admin Routes
    Route::get('/admin/missouri-forms', function () {
        return view('admin.missouri-forms');
    })->name('admin.missouri.forms');

    // Admin invoices CRUD
    Route::get('/web/admin/invoices', [App\Http\Controllers\InvoiceController::class, 'index']);
    Route::post('/web/admin/invoices', [App\Http\Controllers\InvoiceController::class, 'store']);
    Route::get('/web/admin/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'show']);
    Route::put('/web/admin/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'update']);
    Route::delete('/web/admin/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'destroy']);
    Route::post('/web/admin/invoices/{invoice}/send', [App\Http\Controllers\InvoiceController::class, 'send']);
    Route::get('/web/admin/invoices/{invoice}/download', [App\Http\Controllers\InvoiceController::class, 'download']);
    Route::post('/web/admin/invoices/{invoice}/email', [App\Http\Controllers\InvoiceController::class, 'emailInvoice']);

    // Admin certificates CRUD
    Route::get('/web/admin/certificates', [App\Http\Controllers\CertificateController::class, 'index']);
    Route::post('/web/admin/certificates', [App\Http\Controllers\CertificateController::class, 'store']);
    Route::get('/web/admin/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'show']);
    Route::put('/web/admin/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'update']);
    Route::delete('/web/admin/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'destroy']);
    Route::post('/web/admin/certificates/{certificate}/submit-to-state', [App\Http\Controllers\CertificateController::class, 'submitToState']);
    Route::get('/web/admin/certificates/{certificate}/download', [App\Http\Controllers\CertificateController::class, 'download']);
    Route::post('/web/admin/certificates/{certificate}/email', [App\Http\Controllers\CertificateController::class, 'emailCertificate']);

    // Certificate Management Routes
    Route::get('/admin/certificates', [App\Http\Controllers\CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/admin/certificates/{id}', [App\Http\Controllers\CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/admin/certificates/{id}/download', [App\Http\Controllers\CertificateController::class, 'download'])->name('certificates.download');
    Route::post('/admin/certificates/generate', [App\Http\Controllers\CertificateController::class, 'generate'])->name('certificates.generate');

    // State Integration Web Routes
    Route::get('/web/admin/state-configurations', [App\Http\Controllers\StateConfigurationController::class, 'index']);
    Route::post('/web/admin/state-configurations', [App\Http\Controllers\StateConfigurationController::class, 'store']);
    Route::get('/web/admin/state-configurations/{stateCode}/test-connection', [App\Http\Controllers\StateConfigurationController::class, 'testConnection']);
    Route::delete('/web/admin/state-configurations/{stateConfiguration}', [App\Http\Controllers\StateConfigurationController::class, 'destroy']);
    Route::get('/web/admin/courts/states', [App\Http\Controllers\CountyController::class, 'index']);
    Route::post('/web/admin/courts/states', [App\Http\Controllers\CountyController::class, 'storeState']);
    Route::delete('/web/admin/courts/states/{state}', [App\Http\Controllers\CountyController::class, 'deleteState']);
    Route::get('/web/admin/courts/{state}/counties', [App\Http\Controllers\CountyController::class, 'getCounties']);
    Route::post('/web/admin/courts/{state}/counties', [App\Http\Controllers\CountyController::class, 'storeCounty']);
    Route::delete('/web/admin/courts/{state}/counties/{county}', [App\Http\Controllers\CountyController::class, 'deleteCounty']);
    Route::get('/web/admin/courts/{state}/{county}', [App\Http\Controllers\CountyController::class, 'getCourts']);
    Route::post('/web/admin/courts', [App\Http\Controllers\CountyController::class, 'storeCourt']);
    Route::put('/web/admin/courts/{id}', [App\Http\Controllers\CountyController::class, 'updateCourt']);
    
    // Timer Violation Admin Routes
    Route::get('/web/admin/timer-violations', [App\Http\Controllers\Admin\TimerViolationController::class, 'index'])->name('admin.timer-violations.index');
    Route::get('/web/admin/timer-violations/stats', [App\Http\Controllers\Admin\TimerViolationController::class, 'stats'])->name('admin.timer-violations.stats');
    Route::get('/web/admin/timer-violations/{id}', [App\Http\Controllers\Admin\TimerViolationController::class, 'show'])->name('admin.timer-violations.show');
    Route::get('/web/admin/timer-violations/session/{sessionId}', [App\Http\Controllers\Admin\TimerViolationController::class, 'sessionViolations'])->name('admin.timer-violations.session');
    Route::delete('/web/admin/courts/{id}', [App\Http\Controllers\CountyController::class, 'deleteCourt']);

    Route::get('/web/admin/submission-queue/stats', [App\Http\Controllers\StateSubmissionController::class, 'stats']);
    Route::post('/web/admin/submission-queue/process-pending', [App\Http\Controllers\StateSubmissionController::class, 'processPending']);
    Route::get('/web/admin/submission-queue', [App\Http\Controllers\StateSubmissionController::class, 'index']);
    Route::post('/web/admin/submission-queue/{id}/retry', [App\Http\Controllers\StateSubmissionController::class, 'retry']);

    // Email Templates Web Routes
    Route::get('/web/admin/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'index']);
    Route::post('/web/admin/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'store']);
    Route::get('/web/admin/email-templates/{emailTemplate}', [App\Http\Controllers\EmailTemplateController::class, 'show']);
    Route::put('/web/admin/email-templates/{emailTemplate}', [App\Http\Controllers\EmailTemplateController::class, 'update']);
    Route::delete('/web/admin/email-templates/{emailTemplate}', [App\Http\Controllers\EmailTemplateController::class, 'destroy']);
    Route::post('/web/admin/email-templates/{emailTemplate}/test', [App\Http\Controllers\EmailTemplateController::class, 'test']);

    Route::get('/web/admin/email-logs', [App\Http\Controllers\EmailLogController::class, 'index']);
    Route::get('/web/admin/email-logs/stats', [App\Http\Controllers\EmailLogController::class, 'stats']);
    
    // Coupon Management Routes
    Route::get('/admin/coupons', [App\Http\Controllers\CouponController::class, 'index']);
    Route::post('/admin/coupons', [App\Http\Controllers\CouponController::class, 'store']);
    Route::put('/admin/coupons/{coupon}', [App\Http\Controllers\CouponController::class, 'update']);
    Route::delete('/admin/coupons/{coupon}', [App\Http\Controllers\CouponController::class, 'destroy']);
});

// Public certificate verification
Route::get('/certificates/{verificationHash}/verify', [App\Http\Controllers\CertificateController::class, 'verify']);

// Progress API Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/api/progress/{enrollmentId}', [App\Http\Controllers\ProgressApiController::class, 'getProgress']);
});

// API Routes for certificates
Route::middleware(['auth'])->group(function () {
    Route::get('/api/certificates', [App\Http\Controllers\CertificateController::class, 'index']);
    Route::post('/api/certificates/generate', [App\Http\Controllers\CertificateController::class, 'generate']);
    Route::get('/certificate/generate', [App\Http\Controllers\CertificateController::class, 'generate'])->name('certificate.generate');
    Route::get('/certificate/view', [App\Http\Controllers\CertificateController::class, 'view'])->name('certificate.view');
});

// Coupon API Routes (public for payment page)
Route::post('/api/coupons/apply', [App\Http\Controllers\CouponController::class, 'apply']);
Route::post('/api/coupons/use', [App\Http\Controllers\CouponController::class, 'use']);
Route::post('/api/content-access-log', [App\Http\Controllers\ContentAccessLogController::class, 'log'])->middleware('auth');

Route::get('/admin/florida-courses', function () {
    return view('admin.florida-courses');
});

Route::get('/admin/florida-certificates', function () {
    return view('admin.florida-certificates');
});

Route::get('/admin/dicds-orders', function () {
    return view('admin.dicds-orders');
});

Route::get('/admin/florida-dashboard', function () {
    return view('admin.florida-dashboard');
});

Route::get('/admin/certificate-inventory', function () {
    return view('admin.certificate-inventory');
});

Route::get('/admin/compliance-reports', function () {
    return view('admin.compliance-reports');
});

Route::get('/admin/florida-payments', function () {
    return view('admin.florida-payments');
});

Route::get('/admin/fee-remittances', function () {
    return view('admin.fee-remittances');
});

Route::get('/admin/pricing-rules', function () {
    return view('admin.pricing-rules');
});

// New Modules Routes
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/flhsmv/submissions', [App\Http\Controllers\FlhsmvController::class, 'listSubmissions']);
    Route::get('/admin/payments/transactions', function () {
        return view('admin.payments.transactions');
    });
    Route::get('/admin/payments/stripe', function () {
        return view('admin.payments.stripe');
    });
    Route::get('/admin/payments/paypal', function () {
        return view('admin.payments.paypal');
    });
    Route::get('/admin/course-timers', function () {
        return view('admin.course-timers');
    });
    Route::post('/api/courses/toggle-strict-duration', [App\Http\Controllers\CourseController::class, 'toggleStrictDuration']);

    // State Stamps Admin routes
    Route::get('/admin/state-stamps', [App\Http\Controllers\StateStampController::class, 'index']);
    Route::post('/admin/state-stamps', [App\Http\Controllers\StateStampController::class, 'store']);
    Route::put('/admin/state-stamps/{id}', [App\Http\Controllers\StateStampController::class, 'update']);
    Route::delete('/admin/state-stamps/{id}', [App\Http\Controllers\StateStampController::class, 'destroy']);

    Route::get('/admin/support/tickets', [App\Http\Controllers\SupportTicketController::class, 'index']);
    Route::get('/admin/support/recipients', [App\Http\Controllers\TicketRecipientController::class, 'index'])->name('ticket-recipients.index');
    Route::post('/admin/support/recipients', [App\Http\Controllers\TicketRecipientController::class, 'store'])->name('ticket-recipients.store');
    Route::delete('/admin/support/recipients/{recipient}', [App\Http\Controllers\TicketRecipientController::class, 'destroy'])->name('ticket-recipients.destroy');
    Route::patch('/admin/support/recipients/{recipient}/toggle', [App\Http\Controllers\TicketRecipientController::class, 'toggle'])->name('ticket-recipients.toggle');
});

// Support Ticket API Routes (for authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/support/tickets', [App\Http\Controllers\SupportTicketController::class, 'index']);
    Route::post('/api/support/tickets', [App\Http\Controllers\SupportTicketController::class, 'store']);
    Route::get('/api/support/tickets/{id}', [App\Http\Controllers\SupportTicketController::class, 'show']);
    Route::post('/api/support/tickets/{id}/reply', [App\Http\Controllers\SupportTicketController::class, 'reply']);
    Route::get('/api/support/tickets/{id}/replies', [App\Http\Controllers\SupportTicketController::class, 'getReplies']);
    Route::put('/api/support/tickets/{id}/status', [App\Http\Controllers\SupportTicketController::class, 'updateStatus']);
});

Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/user-access', [App\Http\Controllers\UserAccessController::class, 'index'])->name('user-access.index');
    Route::patch('/admin/user-access/{user}/unlock', [App\Http\Controllers\UserAccessController::class, 'unlock'])->name('user-access.unlock');
    Route::get('/admin/faqs', [App\Http\Controllers\FaqController::class, 'index']);
    Route::get('/admin/counties', function () {
        return view('admin.counties');
    });
    Route::get('/admin/question-banks', function () {
        return view('admin.question-banks');
    });
});

Route::get('/admin/florida-email-templates', function () {
    return view('admin.florida-email-templates');
});

Route::get('/admin/dicds-submissions', function () {
    return view('admin.dicds-submissions');
});

// Quiz Maintenance Tool
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/quiz-maintenance', [App\Http\Controllers\Admin\QuizMaintenanceController::class, 'index'])
        ->name('admin.quiz-maintenance.index');
    Route::post('/admin/quiz-maintenance/diagnose', [App\Http\Controllers\Admin\QuizMaintenanceController::class, 'diagnose'])
        ->name('admin.quiz-maintenance.diagnose');
    Route::post('/admin/quiz-maintenance/fix', [App\Http\Controllers\Admin\QuizMaintenanceController::class, 'fix'])
        ->name('admin.quiz-maintenance.fix');
});

// Security Questions Management
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::resource('/admin/security-questions', App\Http\Controllers\Admin\SecurityQuestionController::class)->names([
        'index' => 'admin.security-questions.index',
        'create' => 'admin.security-questions.create',
        'store' => 'admin.security-questions.store',
        'show' => 'admin.security-questions.show',
        'edit' => 'admin.security-questions.edit',
        'update' => 'admin.security-questions.update',
        'destroy' => 'admin.security-questions.destroy',
    ]);
    Route::post('/admin/security-questions/{securityQuestion}/toggle', [App\Http\Controllers\Admin\SecurityQuestionController::class, 'toggleActive'])->name('admin.security-questions.toggle');
    Route::post('/admin/security-questions/reorder', [App\Http\Controllers\Admin\SecurityQuestionController::class, 'reorder'])->name('admin.security-questions.reorder');
});

// Final Exam Questions Management
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::resource('/admin/final-exam-questions', App\Http\Controllers\Admin\FinalExamQuestionController::class)->names([
        'index' => 'admin.final-exam-questions.index',
        'create' => 'admin.final-exam-questions.create',
        'store' => 'admin.final-exam-questions.store',
        'show' => 'admin.final-exam-questions.show',
        'edit' => 'admin.final-exam-questions.edit',
        'update' => 'admin.final-exam-questions.update',
        'destroy' => 'admin.final-exam-questions.destroy',
    ]);
    Route::post('/admin/final-exam-questions/import', [App\Http\Controllers\Admin\FinalExamQuestionController::class, 'import'])->name('admin.final-exam-questions.import');
});

// Free Response Quiz Management
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::resource('/admin/free-response-quiz', App\Http\Controllers\Admin\FreeResponseQuizController::class)->names([
        'index' => 'admin.free-response-quiz.index',
        'create' => 'admin.free-response-quiz.create',
        'store' => 'admin.free-response-quiz.store',
        'show' => 'admin.free-response-quiz.show',
        'edit' => 'admin.free-response-quiz.edit',
        'update' => 'admin.free-response-quiz.update',
        'destroy' => 'admin.free-response-quiz.destroy',
    ]);
    Route::post('/admin/free-response-quiz/{id}/toggle', [App\Http\Controllers\Admin\FreeResponseQuizController::class, 'toggleActive'])->name('admin.free-response-quiz.toggle');
    Route::get('/admin/free-response-quiz/{id}/sample-answer', function($id) {
        $question = DB::table('free_response_questions')->where('id', $id)->first();
        return response()->json([
            'sample_answer' => $question->sample_answer ?? 'No sample answer provided.',
            'grading_rubric' => $question->grading_rubric ?? null
        ]);
    })->name('admin.free-response-quiz.sample-answer');

    // Free Response Quiz Placement Management
    Route::resource('/admin/free-response-quiz-placements', App\Http\Controllers\Admin\FreeResponseQuizPlacementController::class)->names([
        'index' => 'admin.free-response-quiz-placements.index',
        'create' => 'admin.free-response-quiz-placements.create',
        'store' => 'admin.free-response-quiz-placements.store',
        'show' => 'admin.free-response-quiz-placements.show',
        'edit' => 'admin.free-response-quiz-placements.edit',
        'update' => 'admin.free-response-quiz-placements.update',
        'destroy' => 'admin.free-response-quiz-placements.destroy',
    ]);
    Route::post('/admin/free-response-quiz-placements/{id}/toggle', [App\Http\Controllers\Admin\FreeResponseQuizPlacementController::class, 'toggleActive'])->name('admin.free-response-quiz-placements.toggle');

    // Quiz Random Selection Management (for normal quizzes)
    Route::prefix('admin/quiz-random-selection')->name('admin.quiz-random-selection.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\QuizRandomSelectionController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\Admin\QuizRandomSelectionController::class, 'update'])->name('update');
        Route::get('/stats', [App\Http\Controllers\Admin\QuizRandomSelectionController::class, 'getStats'])->name('stats');
    });

    // Final Exam Settings Management
    Route::prefix('admin/final-exam-settings')->name('admin.final-exam-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\FinalExamSettingsController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\Admin\FinalExamSettingsController::class, 'update'])->name('update');
    });

    // Chapter Quiz Settings Management
    Route::prefix('admin/chapter-quiz-settings')->name('admin.chapter-quiz-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ChapterQuizSettingsController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\Admin\ChapterQuizSettingsController::class, 'update'])->name('update');
    });
});

// Student Free Response Quiz Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/free-response-quiz', [App\Http\Controllers\StudentFreeResponseQuizController::class, 'show'])->name('free-response-quiz.show');
    Route::post('/free-response-quiz/submit', [App\Http\Controllers\StudentFreeResponseQuizController::class, 'submit'])->name('free-response-quiz.submit');
    
    // API endpoint for course player
    Route::get('/api/free-response-questions', [App\Http\Controllers\StudentFreeResponseQuizController::class, 'getQuestionsApi'])->name('api.free-response-questions');
    
    // Student feedback view
    Route::get('/student/feedback', [App\Http\Controllers\StudentFeedbackController::class, 'show'])->name('student.feedback.show');
    
    // Student break system
    Route::get('/student/break/check', [App\Http\Controllers\StudentBreakController::class, 'checkBreakRequired'])->name('student.break.check');
    Route::get('/student/break/{sessionId}', [App\Http\Controllers\StudentBreakController::class, 'show'])->name('student.break.show');
    Route::get('/student/break/{sessionId}/status', [App\Http\Controllers\StudentBreakController::class, 'status'])->name('student.break.status');
    Route::post('/student/break/{sessionId}/complete', [App\Http\Controllers\StudentBreakController::class, 'complete'])->name('student.break.complete');
    Route::post('/student/break/{sessionId}/skip', [App\Http\Controllers\StudentBreakController::class, 'skip'])->name('student.break.skip');
    
    // Debug route to check quiz data
    Route::get('/debug/quiz-data/{enrollmentId}', function($enrollmentId) {
        $enrollment = DB::table('user_course_enrollments')->where('id', $enrollmentId)->first();
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found']);
        }
        
        // Check chapter quiz results
        $chapterQuizzes = DB::table('chapter_quiz_results')
            ->where('user_id', $enrollment->user_id)
            ->get();
        
        // Check quiz attempts
        $quizAttempts = DB::table('quiz_attempts')
            ->where('enrollment_id', $enrollmentId)
            ->get();
        
        // Check questions
        $questionsCount = DB::table('questions')->count();
        
        $result = [
            'enrollment' => $enrollment,
            'chapter_quiz_results' => $chapterQuizzes,
            'quiz_attempts' => $quizAttempts->map(function($attempt) {
                $attempt->questions_decoded = json_decode($attempt->questions_attempted, true);
                return $attempt;
            }),
            'total_questions_in_db' => $questionsCount
        ];
        
        return response()->json($result, JSON_PRETTY_PRINT);
    })->name('debug.quiz-data');
});

// Student Feedback Management Routes
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/student-feedback', [App\Http\Controllers\Admin\StudentFeedbackController::class, 'index'])->name('admin.student-feedback.index');
    Route::get('/admin/student-feedback/{enrollmentId}', [App\Http\Controllers\Admin\StudentFeedbackController::class, 'show'])->name('admin.student-feedback.show');
    Route::post('/admin/student-feedback/{enrollmentId}', [App\Http\Controllers\Admin\StudentFeedbackController::class, 'storeFeedback'])->name('admin.student-feedback.store');
    Route::post('/admin/student-feedback/grade-answer/{answerId}', [App\Http\Controllers\Admin\StudentFeedbackController::class, 'gradeFreeResponse'])->name('admin.student-feedback.grade-answer');
    Route::post('/admin/student-feedback/quiz-feedback/{enrollmentId}/{chapterId}', [App\Http\Controllers\Admin\StudentFeedbackController::class, 'storeQuizFeedback'])->name('admin.student-feedback.quiz-feedback');
    Route::post('/admin/student-feedback/question-feedback/{questionId}', [App\Http\Controllers\Admin\StudentFeedbackController::class, 'saveQuestionFeedback'])->name('admin.student-feedback.question-feedback');
});

// Chapter Breaks Management Routes
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/{courseType}/{courseId}/chapter-breaks', [App\Http\Controllers\Admin\ChapterBreakController::class, 'index'])->name('admin.chapter-breaks.index');
    Route::get('/admin/{courseType}/{courseId}/chapter-breaks/create', [App\Http\Controllers\Admin\ChapterBreakController::class, 'create'])->name('admin.chapter-breaks.create');
    Route::post('/admin/{courseType}/{courseId}/chapter-breaks', [App\Http\Controllers\Admin\ChapterBreakController::class, 'store'])->name('admin.chapter-breaks.store');
    Route::get('/admin/{courseType}/{courseId}/chapter-breaks/{breakId}/edit', [App\Http\Controllers\Admin\ChapterBreakController::class, 'edit'])->name('admin.chapter-breaks.edit');
    Route::put('/admin/{courseType}/{courseId}/chapter-breaks/{breakId}', [App\Http\Controllers\Admin\ChapterBreakController::class, 'update'])->name('admin.chapter-breaks.update');
    Route::delete('/admin/{courseType}/{courseId}/chapter-breaks/{breakId}', [App\Http\Controllers\Admin\ChapterBreakController::class, 'destroy'])->name('admin.chapter-breaks.destroy');
    Route::patch('/admin/{courseType}/{courseId}/chapter-breaks/{breakId}/toggle', [App\Http\Controllers\Admin\ChapterBreakController::class, 'toggleActive'])->name('admin.chapter-breaks.toggle');
});

// Final Exam Results & Grading Routes
Route::middleware(['auth'])->group(function () {
    // Student routes
    Route::get('/final-exam/result/{resultId}', [App\Http\Controllers\FinalExamResultController::class, 'show'])->name('final-exam.result');
    Route::post('/final-exam/result/{resultId}/feedback', [App\Http\Controllers\FinalExamResultController::class, 'submitFeedback'])->name('final-exam.submit-feedback');
    Route::post('/final-exam/process-completion', [App\Http\Controllers\FinalExamResultController::class, 'processExamCompletion'])->name('final-exam.process-completion');
});

// Admin Final Exam Grading Routes
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/final-exam-grading', [App\Http\Controllers\Admin\FinalExamGradingController::class, 'index'])->name('admin.final-exam-grading.index');
    Route::get('/admin/final-exam-grading/{resultId}', [App\Http\Controllers\Admin\FinalExamGradingController::class, 'show'])->name('admin.final-exam-grading.show');
    Route::put('/admin/final-exam-grading/{resultId}', [App\Http\Controllers\Admin\FinalExamGradingController::class, 'updateGrading'])->name('admin.final-exam-grading.update');
    Route::post('/admin/final-exam-grading/bulk-update', [App\Http\Controllers\Admin\FinalExamGradingController::class, 'bulkUpdate'])->name('admin.final-exam-grading.bulk-update');
    Route::post('/admin/final-exam-grading/{resultId}/quick-grade', [App\Http\Controllers\Admin\FinalExamGradingController::class, 'quickGrade'])->name('admin.final-exam-grading.quick-grade');
});

Route::get('/admin/certificate-lookup', function () {
    return view('admin.certificate-lookup');
});

Route::get('/admin/school-activity', function () {
    return view('admin.school-activity');
});

Route::get('/admin/web-service-info', function () {
    return view('admin.web-service-info');
});

Route::get('/admin/legal-documents', function () {
    return view('admin.legal-documents');
});

Route::get('/admin/copyright-protection', function () {
    return view('admin.copyright-protection');
});

Route::get('/admin/user-consents', function () {
    return view('admin.user-consents');
});

// Web routes for DICDS order management
Route::middleware(['auth', 'role:super-admin,admin,user'])->group(function () {
    Route::get('/web/dicds-orders', [App\Http\Controllers\FloridaApprovalController::class, 'indexWeb']);
    Route::post('/web/dicds-orders', [App\Http\Controllers\DicdsOrderController::class, 'storeWeb']);
    Route::put('/web/dicds-orders/{id}/amend', [App\Http\Controllers\DicdsOrderAmendmentController::class, 'amendWeb']);
    Route::post('/web/dicds-orders/{id}/generate-receipt', [App\Http\Controllers\DicdsReceiptController::class, 'generateWeb']);
    Route::put('/web/dicds-orders/{id}/update-approval', [App\Http\Controllers\FloridaApprovalController::class, 'updateApprovalWeb']);
    Route::get('/web/florida-schools', [App\Http\Controllers\FloridaSchoolController::class, 'indexWeb']);
    Route::get('/web/florida-courses', [App\Http\Controllers\FloridaCourseController::class, 'indexWeb']);
});

// TEMPORARY: Course creation routes without role middleware to fix 500 errors
Route::middleware(['auth'])->group(function () {
    Route::get('/api/florida-courses', [App\Http\Controllers\FloridaCourseController::class, 'indexWeb']);
    Route::post('/api/florida-courses', [App\Http\Controllers\FloridaCourseController::class, 'storeWeb']);
    Route::put('/api/florida-courses/{id}', [App\Http\Controllers\FloridaCourseController::class, 'updateWeb']);
    Route::delete('/api/florida-courses/{id}', [App\Http\Controllers\FloridaCourseController::class, 'destroyWeb']);
    Route::post('/api/florida-courses/copy', [App\Http\Controllers\FloridaCourseController::class, 'copy']);
});

Route::middleware(['auth', 'role:super-admin,admin,user'])->group(function () {

    Route::get('/api/florida-certificates', function () {
        $certificates = \App\Models\FloridaCertificate::orderBy('created_at', 'desc')->get();

        return response()->json($certificates);
    });
    // DEBUG: Test middleware blocking
    Route::get('/test-admin-block', function () {
        return response()->json([
            'message' => 'If you see this, middleware is NOT working',
            'path' => request()->path(),
            'time' => now()
        ]);
    });

    // DEBUG: Test actual admin routes
    Route::get('/test-dashboard', function () {
        return response()->json([
            'message' => 'Dashboard route test - should be blocked if middleware works',
            'path' => request()->path(),
            'time' => now()
        ]);
    });

    // Dashboard API endpoints
    Route::get('/api/admin/dashboard/stats', function () {
        return response()->json([
            'total_users' => \App\Models\User::count(),
            'active_enrollments' => \App\Models\UserCourseEnrollment::where('status', 'active')->count(),
            'completed_courses' => \App\Models\UserCourseEnrollment::where('status', 'completed')->count(),
            'total_revenue' => \App\Models\Payment::where('status', 'completed')->sum('amount'),
            'monthly_revenue' => \App\Models\Payment::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'pending_payments' => \App\Models\Payment::where('status', 'pending')->count(),
            'failed_transmissions' => \App\Models\StateTransmission::where('status', 'error')->count(),
        ]);
    });

    Route::get('/api/admin/dashboard/charts', function () {
        $enrollmentData = [];
        $revenueData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $enrollmentData[] = [
                'date' => $date->format('M j'),
                'count' => \App\Models\UserCourseEnrollment::whereDate('created_at', $date)->count()
            ];
            $revenueData[] = [
                'date' => $date->format('M j'),
                'amount' => \App\Models\Payment::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('amount')
            ];
        }

        return response()->json([
            'enrollments' => $enrollmentData,
            'revenue' => $revenueData
        ]);
    });

    Route::get('/api/admin/dashboard/recent-activity', function () {
        $recentEnrollments = \App\Models\UserCourseEnrollment::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $recentPayments = \App\Models\Payment::with('user')
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'enrollments' => $recentEnrollments,
            'payments' => $recentPayments
        ]);
    });

    Route::get('/api/announcements/active', function () {
        $announcements = \App\Models\Announcement::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($announcements);
    });

    Route::get('/api/admin/florida-dashboard/stats', function () {
        $inventory = [
            ['course_type' => 'BDI', 'total_ordered' => 0, 'total_used' => 0, 'available_count' => 0],
            ['course_type' => 'ADI', 'total_ordered' => 0, 'total_used' => 0, 'available_count' => 0],
            ['course_type' => 'TLSAE', 'total_ordered' => 0, 'total_used' => 0, 'available_count' => 0],
        ];

        return response()->json([
            'available' => 0,
            'used_this_month' => \App\Models\FloridaCertificate::whereMonth('completion_date', now()->month)->count(),
            'pending' => 0,
            'failed' => 0,
            'inventory' => $inventory,
            'recent_submissions' => \App\Models\FloridaCertificate::orderBy('created_at', 'desc')->limit(5)->get(),
        ]);
    });

    Route::get('/api/admin/certificate-inventory', function () {
        return response()->json(\App\Models\CertificateInventory::all());
    });

    Route::get('/api/admin/florida-reports', function () {
        return response()->json(\App\Models\FloridaComplianceReport::with('generator')->orderBy('created_at', 'desc')->get());
    });

    Route::post('/api/admin/florida-reports/generate', function (Illuminate\Http\Request $request) {
        $report = \App\Models\FloridaComplianceReport::create([
            'report_type' => $request->report_type,
            'report_date' => now(),
            'data_range_start' => $request->data_range_start,
            'data_range_end' => $request->data_range_end,
            'generated_by' => auth()->id(),
        ]);

        return response()->json($report);
    });

    Route::get('/api/admin/florida-reports/{id}/download', function ($id) {
        $report = \App\Models\FloridaComplianceReport::findOrFail($id);

        return response()->json(['message' => 'Download functionality to be implemented']);
    });

    Route::get('/api/florida-payments', function () {
        $payments = \App\Models\FloridaPayment::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'payments' => $payments,
            'total_revenue' => $payments->sum('total_amount'),
            'florida_fees' => $payments->sum('florida_assessment_fee'),
            'pending_remittance' => $payments->where('florida_fee_remitted', false)->sum('florida_assessment_fee'),
        ]);
    });

    Route::get('/api/florida-remittances', function () {
        return response()->json(\App\Models\FloridaFeeRemittance::with('submitter')->orderBy('created_at', 'desc')->get());
    });

    Route::post('/api/florida-remittances', function (Illuminate\Http\Request $request) {
        $remittance = \App\Models\FloridaFeeRemittance::create([
            'remittance_date' => $request->remittance_date,
            'total_assessment_fees' => $request->total_assessment_fees,
            'total_courses' => $request->total_courses,
            'payment_method' => $request->payment_method,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return response()->json($remittance);
    });

    Route::post('/api/florida-remittances/{id}/submit', function (Illuminate\Http\Request $request, $id) {
        $remittance = \App\Models\FloridaFeeRemittance::findOrFail($id);
        $remittance->update([
            'florida_reference_number' => $request->florida_reference_number,
            'processed_by_florida' => true,
            'processed_at' => now(),
        ]);

        return response()->json($remittance);
    });

    Route::get('/api/pricing-rules', function () {
        return response()->json(\App\Models\FloridaPricingRule::where('is_active', true)->get());
    });

    Route::post('/api/pricing-rules', function (Illuminate\Http\Request $request) {
        $rule = \App\Models\FloridaPricingRule::create($request->all());

        return response()->json($rule);
    });

    Route::get('/api/florida-email-templates', function () {
        return response()->json(\App\Models\FloridaEmailTemplate::orderBy('created_at', 'desc')->get());
    });

    Route::post('/api/florida-email-templates', function (Illuminate\Http\Request $request) {
        $template = \App\Models\FloridaEmailTemplate::create(array_merge($request->all(), ['created_by' => auth()->id()]));

        return response()->json($template);
    });

    Route::post('/api/florida-email-templates/{id}/test', function (Illuminate\Http\Request $request, $id) {
        $template = \App\Models\FloridaEmailTemplate::findOrFail($id);
        \App\Services\FloridaMailService::send($request->email, $template->subject, $template->content);

        return response()->json(['message' => 'Test email sent']);
    });
    Route::get('/api/florida-certificates/{id}/view', function ($id) {
        $certificate = \App\Models\FloridaCertificate::findOrFail($id);

        return view('certificates.florida-certificate', compact('certificate'));
    });
    Route::get('/api/florida-certificates/{id}/download', function ($id) {
        $certificate = \App\Models\FloridaCertificate::findOrFail($id);
        $html = view('certificates.florida-certificate', compact('certificate'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="certificate-'.$certificate->dicds_certificate_number.'.html"');
    });
    Route::get('/api/email-logs-stats', function () {
        return response()->json([
            'total_sent' => 0,
            'total_failed' => 0,
            'total_pending' => 0,
        ]);
    });
    Route::get('/api/email-logs', function () {
        return response()->json([]);
    });
    Route::post('/api/notifications/send', function (Request $request) {
        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Create notification record
        $notification = \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'is_read' => false,
            'sent_at' => now(),
        ]);

        return response()->json([
            'message' => 'Notification sent successfully',
            'user' => $user->name,
            'type' => $request->type,
            'notification_id' => $notification->id,
        ]);
    });

    Route::get('/api/notifications/pending', function (Request $request) {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['notifications' => []]);
        }

        // Get unread notifications for the user
        $notifications = \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->where('sent_at', '!=', null)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Mark as read
        $notifications->each(function ($notif) {
            $notif->markAsRead();
        });

        return response()->json([
            'notifications' => $notifications->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'type' => $notif->type,
                ];
            })
        ]);
    });

    Route::get('/api/pwa/manifest', function (Request $request) {
        $theme = $request->cookie('theme', 'dark-blue');

        $themes = [
            'dark-blue' => [
                'background_color' => '#1e3a5f',
                'theme_color' => '#4a90e2',
            ],
            'dark' => [
                'background_color' => '#1a1a1a',
                'theme_color' => '#ffffff',
            ],
            'light' => [
                'background_color' => '#f0f8ff',
                'theme_color' => '#87ceeb',
            ],
        ];

        $colors = $themes[$theme] ?? $themes['dark-blue'];

        return response()->json([
            'name' => 'Traffic School',
            'short_name' => 'TrafficSchool',
            'description' => 'Online Traffic School Management System',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => $colors['background_color'],
            'theme_color' => $colors['theme_color'],
            'icons' => [
                [
                    'src' => '/favicon.ico',
                    'sizes' => '64x64',
                    'type' => 'image/x-icon',
                ],
            ],
        ]);
    });
    Route::get('/web/data-export/download', function (Request $request) {
        $type = $request->query('type');
        $format = $request->query('format', 'html');
        $user = auth()->user();

        $data = [
            'user' => $user,
            'type' => $type,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];

        // Generate HTML content
        $html = view('exports.data-export', $data)->render();

        if ($format === 'pdf') {
            // For PDF, return HTML with print-friendly CSS
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="export-'.$type.'-'.time().'.html"');
        }

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="export-'.$type.'-'.time().'.html"');
    });
    Route::get('/api/chapters', [App\Http\Controllers\ChapterController::class, 'getAllChapters']);

    // Timer API routes
    Route::get('/api/timer/list', [App\Http\Controllers\TimerController::class, 'list']);
    Route::post('/api/timer/configure', [App\Http\Controllers\TimerController::class, 'configure']);
    Route::post('/api/timer/toggle/{id}', [App\Http\Controllers\TimerController::class, 'toggle']);
    Route::delete('/api/timer/delete/{id}', [App\Http\Controllers\TimerController::class, 'delete']);
    Route::get('/api/timer/chapter/{id}', [App\Http\Controllers\TimerController::class, 'getForChapter']);

    // State Stamps API routes
    Route::get('/api/state-stamps/{stateCode}', [App\Http\Controllers\StateStampController::class, 'getByStateCode']);
    
    // Security verification routes (authenticated)
    Route::get('/api/security/questions', [App\Http\Controllers\SecurityVerificationController::class, 'getRandomQuestions']);
    Route::post('/api/security/questions', [App\Http\Controllers\SecurityVerificationController::class, 'getRandomQuestions']);
    Route::post('/api/security/verify', [App\Http\Controllers\SecurityVerificationController::class, 'verifyAnswers']);
    
    // Debug route to test user loading
    Route::get('/debug/user', function() {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated']);
            }
            return response()->json([
                'user_id' => $user->id,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'has_role' => $user->role ? true : false,
                'role_name' => $user->role ? $user->role->name : null
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    })->middleware('auth');

    Route::get('/api/florida-courses/{id}/chapters', [App\Http\Controllers\ChapterController::class, 'indexWeb']);
    Route::post('/api/florida-courses/{id}/chapters', [App\Http\Controllers\ChapterController::class, 'storeWeb']);
    Route::get('/api/chapters/{id}', [App\Http\Controllers\ChapterController::class, 'show']);
    Route::put('/api/chapters/{id}', [App\Http\Controllers\ChapterController::class, 'updateWeb']);
    Route::delete('/api/chapters/{id}', [App\Http\Controllers\ChapterController::class, 'destroyWeb']);

    // Old quiz import routes (deprecated)
    Route::get('/api/chapters/{id}/questions', [App\Http\Controllers\QuestionController::class, 'index']);
    Route::post('/api/chapters/{id}/questions', [App\Http\Controllers\QuestionController::class, 'store']);
    Route::post('/api/chapters/{id}/questions/import', [App\Http\Controllers\QuestionController::class, 'import']); // Deprecated
    Route::get('/api/questions/{id}', [App\Http\Controllers\QuestionController::class, 'show']);
    Route::put('/api/questions/{id}', [App\Http\Controllers\QuestionController::class, 'update']);
    Route::delete('/api/questions/{id}', [App\Http\Controllers\QuestionController::class, 'destroy']);

    // Chapter Quiz Results
    Route::post('/api/chapter-quiz-results', [App\Http\Controllers\ChapterController::class, 'saveQuizResults']);
    Route::get('/api/chapters/{chapterId}/quiz-result', [App\Http\Controllers\ChapterController::class, 'getQuizResult']);

    // Quiz Maintenance Tool
    Route::get('/admin/quiz-maintenance', [App\Http\Controllers\Admin\QuizMaintenanceController::class, 'index'])
        ->name('admin.quiz-maintenance.index');
    Route::post('/admin/quiz-maintenance/diagnose', [App\Http\Controllers\Admin\QuizMaintenanceController::class, 'diagnose'])
        ->name('admin.quiz-maintenance.diagnose');
    Route::post('/admin/quiz-maintenance/fix', [App\Http\Controllers\Admin\QuizMaintenanceController::class, 'fix'])
        ->name('admin.quiz-maintenance.fix');

    // Certificate Lookup
    Route::post('/web/certificate-lookup', [App\Http\Controllers\CertificateLookupController::class, 'search']);
    Route::post('/web/certificate-lookup/{id}/reprint', [App\Http\Controllers\CertificateLookupController::class, 'reprint']);

    // School Activity Reports
    Route::post('/web/school-activity-reports/generate', [App\Http\Controllers\SchoolActivityController::class, 'generate']);
    Route::get('/web/school-activity-reports', [App\Http\Controllers\SchoolActivityController::class, 'index']);

    // Web Service Info
    Route::get('/web/dicds-web-service-info', function () {
        return response()->json(App\Models\DicdsWebServiceInfo::with('school')->get());
    });

    // Legal Documents
    Route::get('/web/legal-documents', [App\Http\Controllers\LegalDocumentController::class, 'index']);
    Route::post('/web/legal-documents', [App\Http\Controllers\LegalDocumentController::class, 'store']);

    // Copyright Protection
    Route::get('/web/copyright-protection/stats', [App\Http\Controllers\CopyrightProtectionController::class, 'stats']);

    // User Consents
    Route::get('/web/user-consents', function () {
        return response()->json(App\Models\UserLegalConsent::with(['user', 'document'])->get());
    });
});

// Hidden Admin Panel (Secret Routes) - Outside middleware group, uses token authentication
Route::get('/system-control-panel', [App\Http\Controllers\HiddenAdminController::class, 'index']);
Route::post('/system-control-panel/toggle-module', [App\Http\Controllers\HiddenAdminController::class, 'toggleModule']);
Route::post('/system-control-panel/set-license-expiry', [App\Http\Controllers\HiddenAdminController::class, 'setLicenseExpiry']);
Route::post('/system-control-panel/emergency-disable', [App\Http\Controllers\HiddenAdminController::class, 'emergencyDisable']);
Route::get('/system-control-panel/system-info', [App\Http\Controllers\HiddenAdminController::class, 'systemInfo']);
Route::post('/system-control-panel/clear-cache', [App\Http\Controllers\HiddenAdminController::class, 'clearCache']);

Route::get('/certificate-verification', function () {
    return view('certificate-verification');
});

// Admin routes
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('dashboard');
    });
    
    // Simple Quiz Import System (Working Version)
    Route::prefix('admin/simple-quiz-import')->name('admin.simple-quiz-import.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SimpleQuizImportController::class, 'index'])->name('index');
        Route::post('/text', [App\Http\Controllers\Admin\SimpleQuizImportController::class, 'importText'])->name('text');
        Route::post('/file', [App\Http\Controllers\Admin\SimpleQuizImportController::class, 'importFile'])->name('file');
    });

    // Quiz Import System - Advanced multi-format import
    Route::prefix('admin/quiz-import')->name('admin.quiz-import.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\QuizImportController::class, 'index'])->name('index');
        Route::post('/single', [App\Http\Controllers\Admin\QuizImportController::class, 'importSingle'])->name('single');
        Route::post('/bulk', [App\Http\Controllers\Admin\QuizImportController::class, 'importBulk'])->name('bulk');
        Route::post('/text', [App\Http\Controllers\Admin\QuizImportController::class, 'importText'])->name('text');
        Route::post('/preview', [App\Http\Controllers\Admin\QuizImportController::class, 'previewFile'])->name('preview');
        Route::get('/chapters/{courseId}', [App\Http\Controllers\Admin\QuizImportController::class, 'getChapters'])->name('chapters');
    });

    // Quick Quiz Import - For course management interface
    Route::prefix('admin/quick-quiz-import')->name('admin.quick-quiz-import.')->group(function () {
        Route::post('/import', [App\Http\Controllers\Admin\QuickQuizImportController::class, 'quickImport'])->name('import');
        Route::post('/auto-import', [App\Http\Controllers\Admin\QuickQuizImportController::class, 'autoImportFromChapter'])->name('auto-import');
    });
    
    Route::get('/admin/enrollments', function () {
        return view('admin.enrollments');
    });
    Route::get('/admin/enrollments/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'show'])->name('admin.enrollments.show');
    Route::post('/admin/enrollments/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'update'])->name('admin.enrollments.update');

    // Enrollment API actions
    Route::post('/api/resend-certificate/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'resendCertificate']);
    Route::post('/api/resend-transmission/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'resendTransmission']);
    Route::post('/api/email-receipt/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'emailReceipt']);

    // Course Content Management
    Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/course-content', [App\Http\Controllers\Admin\CourseContentController::class, 'index'])->name('admin.course-content.index');
        Route::get('/course-content/{course}', [App\Http\Controllers\Admin\CourseContentController::class, 'show'])->name('admin.course-content.show');
        Route::get('/course-content/{course}/chapters/create', [App\Http\Controllers\Admin\CourseContentController::class, 'createChapter'])->name('admin.course-content.create-chapter');
        Route::post('/course-content/{course}/chapters', [App\Http\Controllers\Admin\CourseContentController::class, 'storeChapter'])->name('admin.course-content.store-chapter');
        Route::get('/course-content/{course}/chapters/{chapter}/edit', [App\Http\Controllers\Admin\CourseContentController::class, 'editChapter'])->name('admin.course-content.edit-chapter');
        Route::put('/course-content/{course}/chapters/{chapter}', [App\Http\Controllers\Admin\CourseContentController::class, 'updateChapter'])->name('admin.course-content.update-chapter');
        Route::delete('/course-content/{course}/chapters/{chapter}', [App\Http\Controllers\Admin\CourseContentController::class, 'destroyChapter'])->name('admin.course-content.destroy-chapter');
        Route::post('/course-content/{course}/reorder-chapters', [App\Http\Controllers\Admin\CourseContentController::class, 'reorderChapters'])->name('admin.course-content.reorder-chapters');
        Route::post('/course-content/upload-image', [App\Http\Controllers\Admin\CourseContentController::class, 'uploadImage'])->name('admin.course-content.upload-image');
        
        // Bulk Upload Routes
        Route::get('/bulk-upload', [App\Http\Controllers\Admin\BulkUploadController::class, 'index'])->name('admin.bulk-upload.index');
        Route::post('/bulk-upload/course-content', [App\Http\Controllers\Admin\BulkUploadController::class, 'uploadCourseContent'])->name('admin.bulk-upload.course-content');
        Route::post('/bulk-upload/quiz-content', [App\Http\Controllers\Admin\BulkUploadController::class, 'uploadQuizContent'])->name('admin.bulk-upload.quiz-content');
        Route::get('/bulk-upload/stats', [App\Http\Controllers\Admin\BulkUploadController::class, 'getStats'])->name('admin.bulk-upload.stats');
        Route::post('/bulk-upload/validate', [App\Http\Controllers\Admin\BulkUploadController::class, 'validateContent'])->name('admin.bulk-upload.validate');
        Route::post('/bulk-upload/optimize-images', [App\Http\Controllers\Admin\BulkUploadController::class, 'optimizeImages'])->name('admin.bulk-upload.optimize-images');
        Route::get('/bulk-upload/export', [App\Http\Controllers\Admin\BulkUploadController::class, 'exportContent'])->name('admin.bulk-upload.export');
        
        // Bulk Upload API Routes
        Route::get('/api/courses/{courseType}', [App\Http\Controllers\Admin\BulkUploadApiController::class, 'getCourses'])->name('admin.api.courses');
        Route::get('/api/courses/{courseType}/{courseId}/chapters', [App\Http\Controllers\Admin\BulkUploadApiController::class, 'getChapters'])->name('admin.api.chapters');
        
        // Enhanced Course Player Routes
        Route::get('/enhanced-course-player/{enrollmentId}', [App\Http\Controllers\Admin\EnhancedCoursePlayerController::class, 'show'])->name('admin.enhanced-course-player');
        Route::get('/enhanced-course-player/{enrollmentId}/chapters/{chapterId}/content', [App\Http\Controllers\Admin\EnhancedCoursePlayerController::class, 'getChapterContent'])->name('admin.enhanced-course-player.content');
        Route::get('/enhanced-course-player/{enrollmentId}/chapters/{chapterId}/chunk/{chunkIndex}', [App\Http\Controllers\Admin\EnhancedCoursePlayerController::class, 'getContentChunk'])->name('admin.enhanced-course-player.chunk');
        Route::post('/enhanced-course-player/{enrollmentId}/chapters/{chapterId}/quiz', [App\Http\Controllers\Admin\EnhancedCoursePlayerController::class, 'submitQuiz'])->name('admin.enhanced-course-player.quiz');
        Route::post('/enhanced-course-player/{enrollmentId}/chapters/{chapterId}/complete', [App\Http\Controllers\Admin\EnhancedCoursePlayerController::class, 'completeChapter'])->name('admin.enhanced-course-player.complete');
        Route::post('/enhanced-course-player/{enrollmentId}/chapters/{chapterId}/progress', [App\Http\Controllers\Admin\EnhancedCoursePlayerController::class, 'saveProgress'])->name('admin.enhanced-course-player.progress');
    });

    // Announcements
    Route::resource('announcements', App\Http\Controllers\AnnouncementController::class);

    Route::get('/admin/users', function () {
        return view('admin.users');
    });
    Route::get('/admin/reports', function () {
        return view('admin.reports');
    });
    Route::get('/admin/payments', function () {
        return view('admin.payments');
    });
    Route::get('/admin/invoices', function () {
        return view('admin.invoices');
    });
    Route::get('/admin/certificates', function () {
        return view('admin.certificates');
    });
    Route::get('/admin/state-integration', function () {
        return view('admin.state-integration');
    });
    Route::get('/admin/manage-counties', function () {
        return view('admin.manage-counties');
    });
    Route::get('/admin/email-templates', function () {
        return view('admin.email-templates');
    });
    Route::get('/admin/notifications', function () {
        return view('admin.notifications');
    });
    Route::get('/admin/accessibility-settings', function () {
        return view('admin.accessibility-settings');
    });
    Route::get('/admin/mobile-optimization', function () {
        return view('admin.mobile-optimization');
    });
    Route::get('/admin/pwa-management', function () {
        return view('admin.pwa-management');
    });
    Route::get('/admin/security-dashboard', function () {
        return view('admin.security-dashboard');
    });
    Route::get('/admin/account-security', function () {
        return view('admin.account-security');
    });
    Route::get('/admin/data-export', function () {
        return view('admin.data-export');
    });

    // Florida Security & Audit Module Routes
    Route::get('/admin/florida-security', [App\Http\Controllers\FloridaSecurityWebController::class, 'securityDashboard']);
    Route::get('/admin/florida-audit', [App\Http\Controllers\Admin\FloridaAuditController::class, 'index'])->name('admin.florida-audit.index');
    Route::get('/admin/florida-audit/export', [App\Http\Controllers\Admin\FloridaAuditController::class, 'export'])->name('admin.florida-audit.export');
    Route::get('/admin/florida-compliance', [App\Http\Controllers\FloridaSecurityWebController::class, 'complianceManager']);
    Route::get('/admin/florida-data-export', [App\Http\Controllers\FloridaSecurityWebController::class, 'dataExportTool']);

    // Florida Mobile & Accessibility Module Routes
    Route::get('/admin/florida-mobile', function () {
        return view('admin.florida-mobile');
    });
    Route::get('/admin/florida-accessibility', function () {
        return view('admin.florida-accessibility');
    });

    Route::get('/admin/dicds-user-management', function () {
        return view('admin.dicds-user-management');
    });
    Route::get('/admin/dicds-access-requests', function () {
        return view('admin.dicds-access-requests');
    });

    Route::get('/admin/florida-courses/{courseId}/chapters', function () {
        return view('admin.chapter-builder');
    });
    Route::get('/admin/chapters/{chapterId}/questions', function ($chapterId) {
        // Handle special final-exam case
        if ($chapterId === 'final-exam') {
            $courseId = request('course_id', 1); // Get course_id from query parameter
            
            // Get course state code
            $course = DB::table('florida_courses')->where('id', $courseId)->first();
            $courseStateCode = $course ? $course->state_code : '';
            
            \Log::info("Final exam - Course ID: {$courseId}, State Code: {$courseStateCode}");
            
            return view('admin.question-manager', [
                'chapterId' => 'final-exam',
                'courseId' => $courseId,
                'courseStateCode' => $courseStateCode,
                'isFinalExam' => true
            ]);
        }
        
        // Get course state code from chapter - check both tables
        $chapter = DB::table('chapters')->where('id', $chapterId)->first();
        $courseStateCode = '';
        $courseId = null;
        
        if ($chapter) {
            // Found in chapters table
            $course = DB::table('florida_courses')->where('id', $chapter->course_id)->first();
            $courseStateCode = $course ? $course->state_code : '';
            $courseId = $chapter->course_id;
            
            \Log::info("Chapter {$chapterId} - Course ID: {$chapter->course_id}, State Code: {$courseStateCode} (from chapters)");
        } else {
            // Check legacy chapters table
            $legacyChapter = DB::table('chapters')->where('id', $chapterId)->first();
            
            if ($legacyChapter) {
                $course = DB::table('florida_courses')->where('id', $legacyChapter->course_id)->first();
                $courseStateCode = $course ? $course->state_code : '';
                $courseId = $legacyChapter->course_id;
                
                \Log::info("Chapter {$chapterId} - Course ID: {$legacyChapter->course_id}, State Code: {$courseStateCode} (from legacy chapters)");
            } else {
                \Log::warning("Chapter {$chapterId} not found in either table");
            }
        }
        
        return view('admin.question-manager', [
            'chapterId' => $chapterId,
            'courseId' => $courseId,
            'courseStateCode' => $courseStateCode
        ]);
    });
    Route::get('/admin/courses/{courseId}/final-exam', function () {
        return view('admin.question-manager');
    });
    Route::get('/admin/courses/{courseId}/preview', function () {
        return view('admin.course-preview');
    });
    Route::get('/admin/final-exam-attempts', function () {
        return view('admin.final-exam-attempts');
    });

    // Mobile-specific routes
    Route::get('/mobile/course/{id}', function ($id) {
        return view('mobile.course-player', ['course' => (object) ['id' => $id, 'title' => 'Sample Course']]);
    });

    // Web routes for accessibility system
    Route::get('/web/accessibility/preferences', [App\Http\Controllers\AccessibilityController::class, 'getPreferences']);
    Route::put('/web/accessibility/preferences', [App\Http\Controllers\AccessibilityController::class, 'updatePreferences']);
    Route::post('/web/accessibility/reset-preferences', [App\Http\Controllers\AccessibilityController::class, 'resetPreferences']);

    Route::get('/web/device-info', [App\Http\Controllers\MobileOptimizationController::class, 'getDeviceInfo']);
    Route::get('/web/mobile-optimized/{component}', [App\Http\Controllers\MobileOptimizationController::class, 'getMobileOptimizedComponent']);

    // Web routes for certificates
    Route::get('/web/certificates/{certificate}/download', [App\Http\Controllers\CertificateController::class, 'downloadWeb']);

    // Florida State Transmission Management Routes
    Route::prefix('admin/fl-transmissions')->name('admin.fl-transmissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\FlTransmissionController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\FlTransmissionController::class, 'show'])->name('show');
        Route::post('/{id}/send', [App\Http\Controllers\Admin\FlTransmissionController::class, 'sendSingle'])->name('send');
        Route::post('/send-all', [App\Http\Controllers\Admin\FlTransmissionController::class, 'sendAll'])->name('send-all');
        Route::post('/{id}/retry', [App\Http\Controllers\Admin\FlTransmissionController::class, 'retry'])->name('retry');
        Route::delete('/{id}', [App\Http\Controllers\Admin\FlTransmissionController::class, 'destroy'])->name('destroy');
        
        // New enhanced routes
        Route::get('/test-connection', [App\Http\Controllers\Admin\FlTransmissionController::class, 'testConnection'])->name('test-connection');
        Route::post('/create-manual', [App\Http\Controllers\Admin\FlTransmissionController::class, 'createManual'])->name('create-manual');
        Route::get('/error-stats', [App\Http\Controllers\Admin\FlTransmissionController::class, 'errorStats'])->name('error-stats');
        Route::get('/export', [App\Http\Controllers\Admin\FlTransmissionController::class, 'export'])->name('export');
    });

    // All State Transmissions Management (unified view)
    Route::prefix('admin/state-transmissions')->name('admin.state-transmissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\StateTransmissionController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\StateTransmissionController::class, 'show'])->name('show');
        Route::post('/{id}/send', [App\Http\Controllers\Admin\StateTransmissionController::class, 'send'])->name('send');
        Route::post('/{id}/retry', [App\Http\Controllers\Admin\StateTransmissionController::class, 'retry'])->name('retry');
        Route::delete('/{id}', [App\Http\Controllers\Admin\StateTransmissionController::class, 'destroy'])->name('destroy');
    });

    // California State Transmission Management Routes
    Route::prefix('admin/ca-transmissions')->name('admin.ca-transmissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CaTransmissionController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\CaTransmissionController::class, 'show'])->name('show');
        Route::post('/send-all', [App\Http\Controllers\Admin\CaTransmissionController::class, 'sendAll'])->name('send-all');
        Route::post('/retry-all', [App\Http\Controllers\Admin\CaTransmissionController::class, 'retryAll'])->name('retry-all');
        Route::post('/{id}/retry', [App\Http\Controllers\Admin\CaTransmissionController::class, 'retry'])->name('retry');
        Route::delete('/{id}', [App\Http\Controllers\Admin\CaTransmissionController::class, 'destroy'])->name('destroy');
    });

    // California CTSI Results Management Routes
    Route::prefix('admin/ctsi-results')->name('admin.ctsi-results.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CtsiResultController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\CtsiResultController::class, 'show'])->name('show');
        Route::delete('/{id}', [App\Http\Controllers\Admin\CtsiResultController::class, 'destroy'])->name('destroy');
    });
});
// Certificate Management Routes
Route::middleware('auth')->group(function () {
    Route::get('/web/admin/certificates', function (Request $request) {
        $certificates = \App\Models\FloridaCertificate::query()
            ->when($request->state_code, fn ($q) => $q->where('state', $request->state_code))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($certificates);
    });

    Route::post('/web/admin/certificates', function (Request $request) {
        try {
            $validated = $request->validate([
                'enrollment_id' => 'nullable|integer',
                'student_name' => 'required|string',
                'course_name' => 'required|string',
                'state_code' => 'required|string',
                'completion_date' => 'required|date',
                'status' => 'nullable|string',
            ]);

            $certificate = \App\Models\FloridaCertificate::create([
                'enrollment_id' => $validated['enrollment_id'] ?? null,
                'student_name' => $validated['student_name'],
                'course_name' => $validated['course_name'],
                'state' => $validated['state_code'],
                'completion_date' => $validated['completion_date'],
                'final_exam_score' => 80.00,
                'driver_license_number' => 'A000000000000',
                'citation_number' => '0000000',
                'citation_county' => 'UNKNOWN',
                'traffic_school_due_date' => now()->addDays(30),
                'student_address' => 'N/A',
                'student_date_of_birth' => now()->subYears(25),
                'court_name' => 'N/A',
                'dicds_certificate_number' => 'CERT-'.time(),
                'verification_hash' => bin2hex(random_bytes(16)),
                'generated_at' => now(),
            ]);

            return response()->json($certificate, 201);
        } catch (\Exception $e) {
            \Log::error('Certificate creation error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('/api/enrollments', function () {
        $enrollments = \App\Models\UserCourseEnrollment::where('user_id', auth()->id())
            ->get()
            ->map(function ($enrollment) {
                $course = \App\Models\FloridaCourse::find($enrollment->course_id);

                $enrollmentArray = $enrollment->toArray();
                $enrollmentArray['course'] = $course ? $course->toArray() : null;

                return $enrollmentArray;
            });

        return response()->json($enrollments);
    });

    Route::get('/api/dicds-submissions', function () {
        $submissions = \App\Models\FloridaCertificate::where('is_sent_to_student', true)
            ->orderBy('sent_at', 'desc')
            ->get()
            ->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'student_name' => $cert->student_name,
                    'course_name' => $cert->course_name,
                    'certificate_number' => $cert->dicds_certificate_number,
                    'submitted_at' => $cert->sent_at,
                    'status' => 'success',
                ];
            });

        return response()->json($submissions);
    });
});

// Public Pages
Route::get('/register/{step?}', [App\Http\Controllers\RegistrationController::class, 'showStep'])->name('register.step');
Route::post('/register/{step}', [App\Http\Controllers\RegistrationController::class, 'processStep'])->name('register.process');

// Payment Routes - Course Enrollment
Route::middleware('auth')->group(function () {
    Route::get('/payment', [App\Http\Controllers\PaymentController::class, 'showPayment'])->name('payment.show');
});

Route::get('/certificate', [App\Http\Controllers\CertificateController::class, 'generate']);
Route::get('/certificate/download', [App\Http\Controllers\CertificateController::class, 'downloadPdf'])->middleware('auth');

// Web routes for security system - accessible to all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/web/security/logs', [App\Http\Controllers\SecurityLogController::class, 'index']);
    Route::get('/web/account/security-settings', [App\Http\Controllers\AccountSecurityController::class, 'getSecuritySettings']);
    Route::put('/web/account/password', [App\Http\Controllers\AccountSecurityController::class, 'changePassword']);
    Route::get('/web/account/login-history', [App\Http\Controllers\AccountSecurityController::class, 'getLoginHistory']);
});

Route::post('/web/data-export/request', [App\Http\Controllers\DataExportController::class, 'requestExport'])->middleware('auth');
Route::get('/web/audit/dashboard', [App\Http\Controllers\AuditController::class, 'getDashboard'])->middleware('auth');

// Two-Factor Authentication routes - accessible to all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::post('/two-factor/enable', [App\Http\Controllers\TwoFactorController::class, 'enable']);
    Route::post('/two-factor/disable', [App\Http\Controllers\TwoFactorController::class, 'disable']);
});

// 2FA routes that work during login (no auth middleware)
Route::post('/two-factor/send', [App\Http\Controllers\TwoFactorController::class, 'sendCode']);
Route::post('/two-factor/verify', [App\Http\Controllers\TwoFactorController::class, 'verifyCode']);
Route::get('/two-factor/status', [App\Http\Controllers\TwoFactorController::class, 'getStatus']);
Route::get('/two-factor/verify', function () {
    return view('auth.two-factor-verify');
})->name('two-factor.verify');

Route::get('/faq', function () {
    return view('faq');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/privacy-policy', function () {
    return view('legal.privacy-policy');
});

Route::get('/terms-conditions', function () {
    return view('legal.terms-conditions');
});

Route::get('/refund-policy', function () {
    return view('legal.refund-policy');
});

// Survey Routes - User-facing
Route::middleware('auth')->group(function () {
    Route::get('/survey/{enrollment}', [App\Http\Controllers\SurveyController::class, 'show'])->name('survey.show');
    Route::post('/survey/{enrollment}', [App\Http\Controllers\SurveyController::class, 'submit'])->name('survey.submit');
    Route::get('/survey/{enrollment}/thank-you', [App\Http\Controllers\SurveyController::class, 'thankYou'])->name('survey.thank-you');
});

// Survey Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin')->name('admin.')->group(function () {
    // Survey CRUD
    Route::resource('surveys', App\Http\Controllers\Admin\SurveyController::class);
    Route::post('surveys/{survey}/duplicate', [App\Http\Controllers\Admin\SurveyController::class, 'duplicate'])->name('surveys.duplicate');
    Route::patch('surveys/{survey}/toggle-active', [App\Http\Controllers\Admin\SurveyController::class, 'toggleActive'])->name('surveys.toggle-active');
    Route::get('surveys/{survey}/responses', [App\Http\Controllers\Admin\SurveyController::class, 'responses'])->name('surveys.responses');
    Route::get('surveys/{survey}/export', [App\Http\Controllers\Admin\SurveyController::class, 'export'])->name('surveys.export');

    // Survey Questions
    Route::get('surveys/{survey}/questions', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'index'])->name('surveys.questions.index');
    Route::post('surveys/{survey}/questions', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'store'])->name('surveys.questions.store');
    Route::put('surveys/{survey}/questions/{question}', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'update'])->name('surveys.questions.update');
    Route::delete('surveys/{survey}/questions/{question}', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'destroy'])->name('surveys.questions.destroy');
    Route::post('surveys/{survey}/questions/reorder', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'reorder'])->name('surveys.questions.reorder');

    // Survey Reports
    Route::get('survey-reports', [App\Http\Controllers\Admin\SurveyReportController::class, 'index'])->name('survey-reports.index');
    Route::get('survey-reports/by-survey/{survey}', [App\Http\Controllers\Admin\SurveyReportController::class, 'bySurvey'])->name('survey-reports.by-survey');
    Route::get('survey-reports/by-state/{stateCode}', [App\Http\Controllers\Admin\SurveyReportController::class, 'byState'])->name('survey-reports.by-state');
    Route::get('survey-reports/by-course/{course}', [App\Http\Controllers\Admin\SurveyReportController::class, 'byCourse'])->name('survey-reports.by-course');
    Route::get('survey-reports/by-date-range', [App\Http\Controllers\Admin\SurveyReportController::class, 'byDateRange'])->name('survey-reports.by-date-range');
    Route::get('survey-reports/print/{survey}', [App\Http\Controllers\Admin\SurveyReportController::class, 'print'])->name('survey-reports.print');
    Route::get('survey-reports/delaware', [App\Http\Controllers\Admin\SurveyReportController::class, 'delaware'])->name('survey-reports.delaware');
    Route::get('survey-reports/export/{survey}/{type}', [App\Http\Controllers\Admin\SurveyReportController::class, 'export'])->name('survey-reports.export');
});

// Newsletter Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/newsletter')->name('admin.newsletter.')->group(function () {
    // Subscribers
    Route::get('subscribers', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'index'])->name('subscribers.index');
    Route::get('subscribers/create', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'create'])->name('subscribers.create');
    Route::post('subscribers', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'store'])->name('subscribers.store');
    Route::get('subscribers/{subscriber}/edit', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'edit'])->name('subscribers.edit');
    Route::put('subscribers/{subscriber}', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'update'])->name('subscribers.update');
    Route::delete('subscribers/{subscriber}', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'destroy'])->name('subscribers.destroy');

    // Import/Export
    Route::get('subscribers/import', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'import'])->name('subscribers.import');
    Route::post('subscribers/import', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'import']);
    Route::get('subscribers/export', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'export'])->name('subscribers.export');

    // Bulk Actions
    Route::post('subscribers/bulk-action', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'bulkAction'])->name('subscribers.bulk-action');

    // Statistics
    Route::get('subscribers/statistics', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'statistics'])->name('subscribers.statistics');
});

// Revenue Reporting Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/revenue')->name('admin.revenue.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\RevenueReportController::class, 'dashboard'])->name('index');
    Route::get('/dashboard', [App\Http\Controllers\Admin\RevenueReportController::class, 'dashboard'])->name('dashboard');
    Route::get('/by-state', [App\Http\Controllers\Admin\RevenueReportController::class, 'byState'])->name('by-state');
    Route::get('/by-course', [App\Http\Controllers\Admin\RevenueReportController::class, 'byCourse'])->name('by-course');
    Route::get('/export', [App\Http\Controllers\Admin\RevenueReportController::class, 'export'])->name('export');
});

// Customer Segmentation Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/customers')->name('admin.customers.')->group(function () {
    // Segment Dashboard
    Route::get('/segments', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'index'])->name('segments');

    // Pre-defined Segments (Legacy equivalents)
    Route::get('/completed-monthly', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'completedMonthly'])->name('completed-monthly');
    Route::get('/paid-incomplete', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'paidIncomplete'])->name('paid-incomplete');

    // Additional Segments
    Route::get('/in-progress', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'inProgress'])->name('in-progress');
    Route::get('/abandoned', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'abandoned'])->name('abandoned');
    Route::get('/expiring-soon', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'expiringSoon'])->name('expiring-soon');
    Route::get('/expired', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'expired'])->name('expired');
    Route::get('/never-started', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'neverStarted'])->name('never-started');
    Route::get('/struggling', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'struggling'])->name('struggling');

    // Custom Segments
    Route::get('/custom/{segment}', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'customSegment'])->name('custom-segment');
    Route::post('/segments', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'saveSegment'])->name('segments.save');
    Route::delete('/segments/{segment}', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'deleteSegment'])->name('segments.delete');

    // Bulk Actions
    Route::post('/bulk-remind', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'bulkRemind'])->name('bulk-remind');
    Route::post('/bulk-extend', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'bulkExtend'])->name('bulk-extend');
    Route::post('/bulk-export', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'bulkExport'])->name('bulk-export');
});

// Booklet System Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/booklets')->name('admin.booklets.')->group(function () {
    // Booklet Management
    Route::get('/', [App\Http\Controllers\Admin\BookletController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admin\BookletController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admin\BookletController::class, 'store'])->name('store');
    Route::get('/{booklet}', [App\Http\Controllers\Admin\BookletController::class, 'show'])->name('show');
    Route::get('/{booklet}/edit', [App\Http\Controllers\Admin\BookletController::class, 'edit'])->name('edit');
    Route::put('/{booklet}', [App\Http\Controllers\Admin\BookletController::class, 'update'])->name('update');
    Route::delete('/{booklet}', [App\Http\Controllers\Admin\BookletController::class, 'destroy'])->name('destroy');
    Route::get('/{booklet}/preview', [App\Http\Controllers\Admin\BookletController::class, 'preview'])->name('preview');
    Route::get('/{booklet}/download', [App\Http\Controllers\Admin\BookletController::class, 'download'])->name('download');
    Route::post('/{booklet}/regenerate', [App\Http\Controllers\Admin\BookletController::class, 'regenerate'])->name('regenerate');

    // Orders
    Route::get('/orders', [App\Http\Controllers\Admin\BookletController::class, 'orders'])->name('orders');
    Route::get('/orders/all', [App\Http\Controllers\Admin\BookletController::class, 'orders'])->name('orders');
    Route::get('/orders/pending', [App\Http\Controllers\Admin\BookletController::class, 'pendingOrders'])->name('orders.pending');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\BookletController::class, 'viewOrder'])->name('orders.view');
    Route::post('/orders/{order}/generate', [App\Http\Controllers\Admin\BookletController::class, 'generateOrder'])->name('orders.generate');
    Route::post('/orders/{order}/mark-printed', [App\Http\Controllers\Admin\BookletController::class, 'markPrinted'])->name('orders.mark-printed');
    Route::post('/orders/{order}/mark-shipped', [App\Http\Controllers\Admin\BookletController::class, 'markShipped'])->name('orders.mark-shipped');
    Route::post('/orders/bulk-generate', [App\Http\Controllers\Admin\BookletController::class, 'bulkGenerate'])->name('orders.bulk-generate');
    Route::post('/orders/bulk-print', [App\Http\Controllers\Admin\BookletController::class, 'bulkPrint'])->name('orders.bulk-print');

    // Templates
    Route::get('/templates/all', [App\Http\Controllers\Admin\BookletController::class, 'templates'])->name('templates');
    Route::get('/templates/{template}/edit', [App\Http\Controllers\Admin\BookletController::class, 'editTemplate'])->name('templates.edit');
    Route::put('/templates/{template}', [App\Http\Controllers\Admin\BookletController::class, 'updateTemplate'])->name('templates.update');
    Route::post('/templates/{template}/preview', [App\Http\Controllers\Admin\BookletController::class, 'previewTemplate'])->name('templates.preview');
});

// Court Mailing System Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/mail-court')->name('admin.mail-court.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\CourtMailingController::class, 'index'])->name('index');
    Route::get('/pending', [App\Http\Controllers\Admin\CourtMailingController::class, 'pending'])->name('pending');
    Route::get('/printed', [App\Http\Controllers\Admin\CourtMailingController::class, 'printed'])->name('printed');
    Route::get('/mailed', [App\Http\Controllers\Admin\CourtMailingController::class, 'mailed'])->name('mailed');
    Route::get('/completed', [App\Http\Controllers\Admin\CourtMailingController::class, 'completed'])->name('completed');
    Route::get('/returned', [App\Http\Controllers\Admin\CourtMailingController::class, 'returned'])->name('returned');
    Route::get('/{mailing}', [App\Http\Controllers\Admin\CourtMailingController::class, 'show'])->name('show');
    Route::post('/{mailing}/print', [App\Http\Controllers\Admin\CourtMailingController::class, 'markPrinted'])->name('mark-printed');
    Route::post('/{mailing}/mail', [App\Http\Controllers\Admin\CourtMailingController::class, 'markMailed'])->name('mark-mailed');
    Route::post('/{mailing}/deliver', [App\Http\Controllers\Admin\CourtMailingController::class, 'markDelivered'])->name('mark-delivered');
    Route::post('/{mailing}/return', [App\Http\Controllers\Admin\CourtMailingController::class, 'markReturned'])->name('mark-returned');
    Route::get('/batches/list', [App\Http\Controllers\Admin\CourtMailingController::class, 'batches'])->name('batches');
    Route::post('/batches/create', [App\Http\Controllers\Admin\CourtMailingController::class, 'createBatch'])->name('batches.create');
    Route::get('/batches/{batch}', [App\Http\Controllers\Admin\CourtMailingController::class, 'viewBatch'])->name('batches.show');
    Route::post('/batches/{batch}/add', [App\Http\Controllers\Admin\CourtMailingController::class, 'addToBatch'])->name('batches.add');
    Route::post('/batches/{batch}/print', [App\Http\Controllers\Admin\CourtMailingController::class, 'printBatch'])->name('batches.print');
    Route::post('/batches/{batch}/mail', [App\Http\Controllers\Admin\CourtMailingController::class, 'mailBatch'])->name('batches.mail');
    Route::post('/batches/{batch}/close', [App\Http\Controllers\Admin\CourtMailingController::class, 'closeBatch'])->name('batches.close');
    Route::post('/bulk-print', [App\Http\Controllers\Admin\CourtMailingController::class, 'bulkPrint'])->name('bulk-print');
    Route::get('/reports/dashboard', [App\Http\Controllers\Admin\CourtMailingController::class, 'reports'])->name('reports');
});

// Nevada State Integration Routes
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/nevada')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\NevadaController::class, 'dashboard'])->name('nevada.dashboard');

    // Courses
    Route::get('/courses', [App\Http\Controllers\Admin\NevadaController::class, 'courses'])->name('nevada.courses');
    Route::post('/courses', [App\Http\Controllers\Admin\NevadaController::class, 'storeCourse'])->name('nevada.courses.store');
    Route::put('/courses/{id}', [App\Http\Controllers\Admin\NevadaController::class, 'updateCourse'])->name('nevada.courses.update');

    // Students
    Route::get('/students', [App\Http\Controllers\Admin\NevadaController::class, 'students'])->name('nevada.students');
    Route::get('/students/{enrollment}', [App\Http\Controllers\Admin\NevadaController::class, 'studentDetail'])->name('nevada.students.detail');
    Route::get('/students/{enrollment}/activity-log', [App\Http\Controllers\Admin\NevadaController::class, 'activityLog'])->name('nevada.students.activity');

    // Certificates
    Route::get('/certificates', [App\Http\Controllers\Admin\NevadaController::class, 'certificates'])->name('nevada.certificates');
    Route::post('/certificates/{id}/submit', [App\Http\Controllers\Admin\NevadaController::class, 'submitCertificate'])->name('nevada.certificates.submit');

    // Compliance Logs (legacy: customer_search_log_nevada.jsp)
    Route::get('/compliance-logs', [App\Http\Controllers\Admin\NevadaController::class, 'complianceLogs'])->name('nevada.compliance.logs');
    Route::get('/compliance-logs/export', [App\Http\Controllers\Admin\NevadaController::class, 'exportLogs'])->name('nevada.compliance.export');

    // Submissions
    Route::get('/submissions', [App\Http\Controllers\Admin\NevadaController::class, 'submissions'])->name('nevada.submissions');
    Route::get('/submissions/{id}', [App\Http\Controllers\Admin\NevadaController::class, 'submissionDetail'])->name('nevada.submissions.detail');
    Route::post('/submissions/{id}/retry', [App\Http\Controllers\Admin\NevadaController::class, 'retrySubmission'])->name('nevada.submissions.retry');

    // Reports
    Route::get('/reports', [App\Http\Controllers\Admin\NevadaController::class, 'reports'])->name('nevada.reports');
    Route::get('/reports/compliance', [App\Http\Controllers\Admin\NevadaController::class, 'complianceReport'])->name('nevada.reports.compliance');
});

// Payment Gateway Management Routes - Admin
Route::middleware(['auth', 'role:super-admin'])->prefix('admin/payment-gateways')->name('admin.payment-gateways.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'index'])->name('index');
    Route::get('/{gateway}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'show'])->name('show');
    Route::get('/{gateway}/edit', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'edit'])->name('edit');
    Route::put('/{gateway}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'update'])->name('update');
    Route::put('/{gateway}/settings', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'updateSettings'])->name('update-settings');
    Route::post('/{gateway}/test', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'testConnection'])->name('test');
    Route::post('/{gateway}/activate', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'activate'])->name('activate');
    Route::post('/{gateway}/deactivate', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'deactivate'])->name('deactivate');
    Route::post('/{gateway}/toggle-mode', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'toggleMode'])->name('toggle-mode');
    Route::get('/{gateway}/logs', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'logs'])->name('logs');
    Route::post('/reorder', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'reorder'])->name('reorder');
});

// Merchant Account Management Routes - Admin
Route::middleware(['auth', 'role:super-admin'])->prefix('admin/merchants')->name('admin.merchants.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\MerchantController::class, 'index'])->name('index');
    Route::get('/{account}', [App\Http\Controllers\Admin\MerchantController::class, 'show'])->name('show');
    Route::get('/{account}/transactions', [App\Http\Controllers\Admin\MerchantController::class, 'transactions'])->name('transactions');
    Route::get('/{account}/payouts', [App\Http\Controllers\Admin\MerchantController::class, 'payouts'])->name('payouts');
    Route::get('/{account}/reconciliation', [App\Http\Controllers\Admin\MerchantController::class, 'reconciliationIndex'])->name('reconciliation');
    Route::post('/{account}/reconciliation', [App\Http\Controllers\Admin\MerchantController::class, 'createReconciliation'])->name('reconciliation.create');
    Route::get('/reconciliation/{reconciliation}', [App\Http\Controllers\Admin\MerchantController::class, 'showReconciliation'])->name('reconciliation.show');
    Route::post('/reconciliation/{reconciliation}/resolve', [App\Http\Controllers\Admin\MerchantController::class, 'resolveReconciliation'])->name('reconciliation.resolve');
    Route::post('/{account}/sync', [App\Http\Controllers\Admin\MerchantController::class, 'syncWithGateway'])->name('sync');
    Route::get('/reports/summary', [App\Http\Controllers\Admin\MerchantController::class, 'reportsSummary'])->name('reports.summary');
});

// Court Code Management Routes
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin')->group(function () {
    // Court Code CRUD
    Route::get('/court-codes', [App\Http\Controllers\Admin\CourtCodeController::class, 'index'])->name('admin.court-codes.index');
    Route::get('/court-codes/create', [App\Http\Controllers\Admin\CourtCodeController::class, 'create'])->name('admin.court-codes.create');
    Route::post('/court-codes', [App\Http\Controllers\Admin\CourtCodeController::class, 'store'])->name('admin.court-codes.store');
    Route::get('/court-codes/{code}', [App\Http\Controllers\Admin\CourtCodeController::class, 'show'])->name('admin.court-codes.show');
    Route::get('/court-codes/{code}/edit', [App\Http\Controllers\Admin\CourtCodeController::class, 'edit'])->name('admin.court-codes.edit');
    Route::put('/court-codes/{code}', [App\Http\Controllers\Admin\CourtCodeController::class, 'update'])->name('admin.court-codes.update');
    Route::delete('/court-codes/{code}', [App\Http\Controllers\Admin\CourtCodeController::class, 'destroy'])->name('admin.court-codes.destroy');

    // Court Code Status
    Route::post('/court-codes/{code}/deactivate', [App\Http\Controllers\Admin\CourtCodeController::class, 'deactivate'])->name('admin.court-codes.deactivate');
    Route::post('/court-codes/{code}/reactivate', [App\Http\Controllers\Admin\CourtCodeController::class, 'reactivate'])->name('admin.court-codes.reactivate');

    // Court Code Mappings
    Route::get('/court-codes/{code}/mappings', [App\Http\Controllers\Admin\CourtCodeController::class, 'mappings'])->name('admin.court-codes.mappings');
    Route::post('/court-codes/{code}/mappings', [App\Http\Controllers\Admin\CourtCodeController::class, 'addMapping'])->name('admin.court-codes.mappings.store');
    Route::delete('/court-codes/mappings/{mapping}', [App\Http\Controllers\Admin\CourtCodeController::class, 'removeMapping'])->name('admin.court-codes.mappings.destroy');
    Route::post('/court-codes/mappings/{mapping}/verify', [App\Http\Controllers\Admin\CourtCodeController::class, 'verifyMapping'])->name('admin.court-codes.mappings.verify');

    // Court Code History
    Route::get('/court-codes/{code}/history', [App\Http\Controllers\Admin\CourtCodeController::class, 'history'])->name('admin.court-codes.history');

    // Court Code Search & Lookup
    Route::get('/court-codes/search', [App\Http\Controllers\Admin\CourtCodeController::class, 'search'])->name('admin.court-codes.search');
    Route::get('/court-codes/lookup/{codeValue}', [App\Http\Controllers\Admin\CourtCodeController::class, 'lookup'])->name('admin.court-codes.lookup');
    Route::post('/court-codes/translate', [App\Http\Controllers\Admin\CourtCodeController::class, 'translateCode'])->name('admin.court-codes.translate');

    // Court Code Import/Export
    Route::get('/court-codes/import', [App\Http\Controllers\Admin\CourtCodeController::class, 'importForm'])->name('admin.court-codes.import');
    Route::post('/court-codes/import', [App\Http\Controllers\Admin\CourtCodeController::class, 'import'])->name('admin.court-codes.import.process');
    Route::get('/court-codes/export', [App\Http\Controllers\Admin\CourtCodeController::class, 'export'])->name('admin.court-codes.export');

    // Court Code Reports
    Route::get('/court-codes/reports/expiring', [App\Http\Controllers\Admin\CourtCodeController::class, 'expiringCodes'])->name('admin.court-codes.reports.expiring');
    Route::get('/court-codes/reports/unmapped', [App\Http\Controllers\Admin\CourtCodeController::class, 'unmappedCodes'])->name('admin.court-codes.reports.unmapped');
    Route::get('/court-codes/reports/statistics', [App\Http\Controllers\Admin\CourtCodeController::class, 'statistics'])->name('admin.court-codes.reports.statistics');

    // Court Code by Court
    Route::get('/court-codes/court/{court}', [App\Http\Controllers\Admin\CourtCodeController::class, 'byCourtIndex'])->name('admin.court-codes.court.index');
    Route::post('/court-codes/court/{court}/codes', [App\Http\Controllers\Admin\CourtCodeController::class, 'addCodeToCourt'])->name('admin.court-codes.court.store');
});

// Court Code API Routes
Route::prefix('api/court-codes')->middleware('auth')->group(function () {
    Route::get('/search', [App\Http\Controllers\CourtCodeApiController::class, 'search']);
    Route::get('/lookup/{code}', [App\Http\Controllers\CourtCodeApiController::class, 'lookup']);
    Route::post('/validate', [App\Http\Controllers\CourtCodeApiController::class, 'validate']);
    Route::get('/for-court/{court}', [App\Http\Controllers\CourtCodeApiController::class, 'forCourt']);
    Route::post('/translate', [App\Http\Controllers\CourtCodeApiController::class, 'translate']);
});

// Test route for form controls
Route::get('/test-forms', function () {
    return view('test-forms');
});
// Admin Manual PDF Routes
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/manual/pdf', [App\Http\Controllers\Admin\ManualController::class, 'generatePdf'])->name('admin.manual.pdf');
    Route::get('/admin/manual/word', [App\Http\Controllers\Admin\ManualController::class, 'generateWord'])->name('admin.manual.word');
    Route::get('/admin/manual/preview', [App\Http\Controllers\Admin\ManualController::class, 'preview'])->name('admin.manual.preview');
    Route::get('/admin/manual/test', function () {
        return view('admin.manual-test');
    })->name('admin.manual.test');
});

// Admin Settings Routes
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/load', [App\Http\Controllers\SettingsController::class, 'load'])->name('settings.load');
    Route::post('/settings/save', [App\Http\Controllers\SettingsController::class, 'save'])->name('settings.save');
    Route::post('/settings/clear-cache/{type}', [App\Http\Controllers\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('/settings/optimize-database', [App\Http\Controllers\SettingsController::class, 'optimizeDatabase'])->name('settings.optimize-database');
    Route::get('/settings/backup-database', [App\Http\Controllers\SettingsController::class, 'backupDatabase'])->name('settings.backup-database');
    
    // Database Export with Progress Tracking
    Route::post('/settings/export-database', [App\Http\Controllers\SettingsController::class, 'exportDatabase'])->name('settings.export-database');
    Route::get('/settings/export-progress/{jobId}', [App\Http\Controllers\SettingsController::class, 'getExportProgress'])->name('settings.export-progress');
    Route::post('/settings/cancel-export/{jobId}', [App\Http\Controllers\SettingsController::class, 'cancelExport'])->name('settings.cancel-export');
    Route::get('/settings/download-export/{filename}', [App\Http\Controllers\SettingsController::class, 'downloadExport'])->name('settings.download-export');
    
    Route::get('/settings/system-info', [App\Http\Controllers\SettingsController::class, 'systemInfo'])->name('settings.system-info');
    Route::post('/settings/maintenance/enable', [App\Http\Controllers\SettingsController::class, 'enableMaintenanceMode'])->name('settings.maintenance.enable');
    Route::post('/settings/maintenance/disable', [App\Http\Controllers\SettingsController::class, 'disableMaintenanceMode'])->name('settings.maintenance.disable');
    Route::get('/settings/maintenance/status', [App\Http\Controllers\SettingsController::class, 'getMaintenanceStatus'])->name('settings.maintenance.status');
});
// Admin route for viewing free response submissions
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/free-response-quiz-submissions', [App\Http\Controllers\Admin\FreeResponseQuizController::class, 'submissions'])->name('admin.free-response-quiz.submissions');
});
// Debug route to check free response tables
Route::get('/debug/free-response', function() {
    try {
        // Check free_response_answers table structure
        $answerColumns = Schema::getColumnListing('free_response_answers');
        
        $info = [
            'answer_columns' => $answerColumns,
            'sample_answer' => DB::table('free_response_answers')->first(),
        ];
        
        return response()->json($info);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->middleware(['auth', 'role:super-admin,admin']);
// Grade free response answer
Route::post('/admin/free-response-quiz-submissions/{id}/grade', [App\Http\Controllers\Admin\FreeResponseQuizController::class, 'gradeAnswer'])->name('admin.free-response-quiz.grade')->middleware(['auth', 'role:super-admin,admin']);
// Debug route to check student feedback data
Route::get('/debug/student-feedback', function() {
    try {
        $info = [];
        
        // Check if student_feedback table exists
        $feedbackTableExists = Schema::hasTable('student_feedback');
        $info['feedback_table_exists'] = $feedbackTableExists;
        
        if ($feedbackTableExists) {
            $info['feedback_count'] = DB::table('student_feedback')->count();
            $info['sample_feedback'] = DB::table('student_feedback')->first();
            $info['feedback_columns'] = Schema::getColumnListing('student_feedback');
            $info['all_feedback'] = DB::table('student_feedback')->get();
        }
        
        // Check other possible feedback tables
        $possibleTables = ['feedbacks', 'course_feedback', 'user_feedback', 'student_reviews'];
        foreach ($possibleTables as $table) {
            if (Schema::hasTable($table)) {
                $info[$table . '_exists'] = true;
                $info[$table . '_count'] = DB::table($table)->count();
                $info[$table . '_sample'] = DB::table($table)->first();
                $info[$table . '_columns'] = Schema::getColumnListing($table);
            }
        }
        
        // Check current user info
        if (auth()->check()) {
            $info['current_user_id'] = auth()->id();
        }
        
        return response()->json($info);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->middleware(['auth']);



Route::post('/api/security/questions', [SecurityVerificationController::class, 'getRandomQuestions']);
Route::post('/api/security/verify', [SecurityVerificationController::class, 'verifyAnswers']);




// Missouri Form 4444 Routes
Route::prefix('missouri')->middleware('auth')->group(function () {
    
    // Form 4444 Management
    Route::post('/form4444/generate', [App\Http\Controllers\MissouriController::class, 'generateForm4444'])
        ->name('missouri.form4444.generate');
    
    Route::get('/form4444/{formId}/download', [App\Http\Controllers\MissouriController::class, 'downloadForm4444'])
        ->name('missouri.form4444.download');
    
    Route::post('/form4444/{formId}/email', [App\Http\Controllers\MissouriController::class, 'emailForm4444'])
        ->name('missouri.form4444.email');
    
    Route::get('/user/{userId}/forms', [App\Http\Controllers\MissouriController::class, 'getUserForms'])
        ->name('missouri.user.forms');
    
    // Submission Management
    Route::get('/submission-status/{userId}', [App\Http\Controllers\MissouriController::class, 'getSubmissionStatus'])
        ->name('missouri.submission.status');
    
    Route::post('/form4444/{formId}/submit-dor', [App\Http\Controllers\MissouriController::class, 'submitToDOR'])
        ->name('missouri.form4444.submit-dor');
});

// Test PDF certificate download - TEMPORARY ROUTE
Route::get('/test-pdf-download/{id?}', function ($id = null) {
    try {
        // Use first certificate if no ID provided
        if (!$id) {
            $certificate = \App\Models\FloridaCertificate::with(['enrollment.user', 'enrollment.course'])->first();
        } else {
            $certificate = \App\Models\FloridaCertificate::with(['enrollment.user', 'enrollment.course'])->findOrFail($id);
        }
        
        if (!$certificate) {
            return response()->json(['error' => 'No certificates found'], 404);
        }
        
        // Get user data from enrollment
        $user = $certificate->enrollment->user;
        $course = $certificate->enrollment->course;
        
        // Build student address
        $addressParts = array_filter([
            $user->mailing_address,
            $user->city,
            $user->state,
            $user->zip,
        ]);
        $student_address = implode(', ', $addressParts);

        // Build birth date
        $birth_date = null;
        if ($user->birth_month && $user->birth_day && $user->birth_year) {
            $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
        }

        // Get state stamp if available
        $stateStamp = null;
        if ($course) {
            $stateCode = $course->state ?? $course->state_code ?? null;
            if ($stateCode) {
                $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                    ->where('is_active', true)
                    ->first();
            }
        }
        
        $templateData = [
            'student_name' => $certificate->student_name,
            'student_address' => $student_address ?: $certificate->student_address,
            'completion_date' => $certificate->completion_date->format('m/d/Y'),
            'course_type' => $certificate->course_name,
            'score' => number_format($certificate->final_exam_score, 1) . '%',
            'license_number' => $certificate->driver_license_number ?: $user->driver_license,
            'birth_date' => $birth_date ?: ($certificate->student_date_of_birth ? 
                \Carbon\Carbon::parse($certificate->student_date_of_birth)->format('m/d/Y') : null),
            'citation_number' => $certificate->citation_number ?: $user->citation_number,
            'court' => $certificate->court_name ?: $user->court_selected,
            'county' => $certificate->citation_county ?: $user->state,
            'certificate_number' => $certificate->dicds_certificate_number,
            'phone' => null,
            'city' => $user->city,
            'state' => $user->state,
            'zip' => $user->zip,
            'state_stamp' => $stateStamp,
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificate-pdf', $templateData);
        
        return $pdf->download('test-certificate-pdf-'.$certificate->dicds_certificate_number.'.pdf');

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to generate test certificate PDF: ' . $e->getMessage()
        ], 500);
    }
});

// Admin Routes - Include the complete admin system
Route::prefix('admin')->group(base_path('routes/admin.php'));

// ========================================
// STATE-SEPARATED ROUTING SYSTEM - PHASE 1
// ========================================

// Florida Routes
Route::prefix('florida')->group(function() {
    Route::get('/', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Florida\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return '<h1>Florida Traffic School</h1><p>Controller Error: ' . $e->getMessage() . '</p><p><a href="/florida/courses">Try Courses Page</a></p>';
        }
    })->name('florida.dashboard');
    
    Route::get('/courses', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Florida\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return '<h1>Florida Courses</h1><p>Loading courses...</p><p>Error: ' . $e->getMessage() . '</p>';
        }
    })->name('florida.courses');
    
    Route::get('/course-player/{id}', function($id) {
        try {
            $controller = new \App\Http\Controllers\Student\Florida\CoursePlayerController();
            return $controller->show($id);
        } catch (Exception $e) {
            return '<h1>Florida Course Player</h1><p>Course ID: ' . $id . '</p><p>Error: ' . $e->getMessage() . '</p>';
        }
    })->name('florida.course-player');
    
    Route::get('/certificates', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Florida\CertificateController();
            return $controller->index();
        } catch (Exception $e) {
            return '<h1>Florida Certificates</h1><p>Error: ' . $e->getMessage() . '</p>';
        }
    })->name('florida.certificates');
    
    Route::get('/test-controller', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Florida\CoursePlayerController();
            return '<h1>✅ Florida Controller Test</h1><p>Controller loaded successfully</p><p>Class: ' . get_class($controller) . '</p>';
        } catch (Exception $e) {
            return '<h1>❌ Controller Error</h1><p>' . $e->getMessage() . '</p>';
        }
    })->name('florida.test-controller');
});

// Missouri Routes
Route::prefix('missouri')->group(function() {
    Route::get('/', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Missouri\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return '<h1>Missouri Traffic School</h1><p>Controller Error: ' . $e->getMessage() . '</p>';
        }
    })->name('missouri.dashboard');
    
    Route::get('/courses', function() {
        return '<h1>Missouri Courses</h1><p>Course listing page</p>';
    })->name('missouri.courses');
});

// Texas Routes
Route::prefix('texas')->group(function() {
    Route::get('/', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Texas\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return '<h1>Texas Traffic School</h1><p>Controller Error: ' . $e->getMessage() . '</p>';
        }
    })->name('texas.dashboard');
});

// Delaware Routes
Route::prefix('delaware')->group(function() {
    Route::get('/', function() {
        try {
            $controller = new \App\Http\Controllers\Student\Delaware\CoursePlayerController();
            return $controller->index();
        } catch (Exception $e) {
            return '<h1>Delaware Traffic School</h1><p>Controller Error: ' . $e->getMessage() . '</p>';
        }
    })->name('delaware.dashboard');
});

// Admin Routes for State Management
Route::prefix('admin')->group(function() {
    Route::get('/', function() {
        try {
            $controller = new \App\Http\Controllers\Admin\DashboardController();
            return $controller->index();
        } catch (Exception $e) {
            return '<h1>Admin Dashboard</h1><p>Controller Error: ' . $e->getMessage() . '</p>';
        }
    })->name('admin.dashboard');
    
    // Florida Admin Routes
    Route::prefix('florida')->name('admin.florida.')->group(function() {
        Route::get('/courses', function() {
            try {
                $controller = new \App\Http\Controllers\Admin\Florida\CourseController();
                return $controller->index();
            } catch (Exception $e) {
                return '<h1>Florida Course Admin</h1><p>Error: ' . $e->getMessage() . '</p>';
            }
        })->name('courses.index');
    });
    
    // Missouri Admin Routes
    Route::prefix('missouri')->name('admin.missouri.')->group(function() {
        Route::get('/courses', function() {
            try {
                $controller = new \App\Http\Controllers\Admin\Missouri\CourseController();
                return $controller->index();
            } catch (Exception $e) {
                return '<h1>Missouri Course Admin</h1><p>Error: ' . $e->getMessage() . '</p>';
            }
        })->name('courses.index');
    });
    
    // Texas Admin Routes
    Route::prefix('texas')->name('admin.texas.')->group(function() {
        Route::get('/courses', function() {
            try {
                $controller = new \App\Http\Controllers\Admin\Texas\CourseController();
                return $controller->index();
            } catch (Exception $e) {
                return '<h1>Texas Course Admin</h1><p>Error: ' . $e->getMessage() . '</p>';
            }
        })->name('courses.index');
    });
    
    // Delaware Admin Routes
    Route::prefix('delaware')->name('admin.delaware.')->group(function() {
        Route::get('/courses', function() {
            try {
                $controller = new \App\Http\Controllers\Admin\Delaware\CourseController();
                return $controller->index();
            } catch (Exception $e) {
                return '<h1>Delaware Course Admin</h1><p>Error: ' . $e->getMessage() . '</p>';
            }
        })->name('courses.index');
    });
});

// ========================================
// END STATE-SEPARATED ROUTING SYSTEM
// ========================================

// ========================================
// STATE-SEPARATED ROUTING SYSTEM - PHASE 3
// ========================================

// Florida Routes - With State Middleware
Route::prefix('florida')->name('florida.')->middleware(['state:florida', 'auth'])->group(function() {
    Route::get('/', [App\Http\Controllers\Student\Florida\CoursePlayerController::class, 'index'])->name('dashboard');
    Route::get('/courses', [App\Http\Controllers\Student\Florida\CoursePlayerController::class, 'index'])->name('courses');
    Route::get('/course-player/{id}', [App\Http\Controllers\Student\Florida\CoursePlayerController::class, 'show'])->name('course-player');
    Route::get('/certificates', [App\Http\Controllers\Student\Florida\CertificateController::class, 'index'])->name('certificates');
});

// Florida Test Route (With State Middleware, No Auth)
Route::get('/florida/test', function() {
    $state = request()->attributes->get('current_state');
    $config = config('app.current_state');
    return '<h1>✅ ' . ($config['name'] ?? 'Florida Traffic School') . '</h1>' .
           '<p>State Middleware Active: ' . $state . '</p>' .
           '<p>Authority: ' . ($config['compliance_authority'] ?? 'N/A') . '</p>' .
           '<p>Required Hours: ' . ($config['required_hours'] ?? 'N/A') . '</p>';
})->middleware('state:florida')->name('florida.test');

// Missouri Routes - With State Middleware
Route::prefix('missouri')->name('missouri.')->middleware(['state:missouri', 'auth'])->group(function() {
    Route::get('/', [App\Http\Controllers\Student\Missouri\CoursePlayerController::class, 'index'])->name('dashboard');
    Route::get('/courses', [App\Http\Controllers\Student\Missouri\CoursePlayerController::class, 'index'])->name('courses');
});

// Missouri Test Route (With State Middleware, No Auth)
Route::get('/missouri/test', function() {
    $state = request()->attributes->get('current_state');
    $config = config('app.current_state');
    return '<h1>✅ ' . ($config['name'] ?? 'Missouri Traffic School') . '</h1>' .
           '<p>State Middleware Active: ' . $state . '</p>' .
           '<p>Authority: ' . ($config['compliance_authority'] ?? 'N/A') . '</p>' .
           '<p>Required Hours: ' . ($config['required_hours'] ?? 'N/A') . '</p>';
})->middleware('state:missouri')->name('missouri.test');

// Texas Routes - With State Middleware
Route::prefix('texas')->name('texas.')->middleware(['state:texas', 'auth'])->group(function() {
    Route::get('/', [App\Http\Controllers\Student\Texas\CoursePlayerController::class, 'index'])->name('dashboard');
});

// Texas Test Route (With State Middleware, No Auth)
Route::get('/texas/test', function() {
    $state = request()->attributes->get('current_state');
    $config = config('app.current_state');
    return '<h1>✅ ' . ($config['name'] ?? 'Texas Traffic School') . '</h1>' .
           '<p>State Middleware Active: ' . $state . '</p>' .
           '<p>Authority: ' . ($config['compliance_authority'] ?? 'N/A') . '</p>' .
           '<p>Required Hours: ' . ($config['required_hours'] ?? 'N/A') . '</p>';
})->middleware('state:texas')->name('texas.test');

// Delaware Routes - With State Middleware
Route::prefix('delaware')->name('delaware.')->middleware(['state:delaware', 'auth'])->group(function() {
    Route::get('/', [App\Http\Controllers\Student\Delaware\CoursePlayerController::class, 'index'])->name('dashboard');
});

// Delaware Test Route (With State Middleware, No Auth)
Route::get('/delaware/test', function() {
    $state = request()->attributes->get('current_state');
    $config = config('app.current_state');
    return '<h1>✅ ' . ($config['name'] ?? 'Delaware Traffic School') . '</h1>' .
           '<p>State Middleware Active: ' . $state . '</p>' .
           '<p>Authority: ' . ($config['compliance_authority'] ?? 'N/A') . '</p>' .
           '<p>Required Hours: ' . ($config['required_hours'] ?? 'N/A') . '</p>';
})->middleware('state:delaware')->name('delaware.test');

// Admin Routes - Real Controller Integration
Route::prefix('admin')->name('admin.')->group(function() {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Florida Admin
    Route::prefix('florida')->name('florida.')->group(function() {
        Route::get('/courses', [App\Http\Controllers\Admin\Florida\CourseController::class, 'index'])->name('courses.index');
    });
    
    // Missouri Admin  
    Route::prefix('missouri')->name('missouri.')->group(function() {
        Route::get('/courses', [App\Http\Controllers\Admin\Missouri\CourseController::class, 'index'])->name('courses.index');
    });
});

// Admin Test Route (No Auth Required)
Route::get('/admin/test', function() {
    return '<h1>✅ Admin Laravel Route Working!</h1><p>Controller integration ready</p>';
})->name('admin.test');

// ========================================
// END STATE-SEPARATED ROUTING SYSTEM
// ========================================

// ========================================
// SIMPLE WORKING STATE ROUTES - FINAL
// ========================================

// Simple Test Routes (No Auth, No Middleware)
Route::get('/florida-simple', function() {
    return '<h1>✅ Florida Simple Route Working!</h1><p>Time: ' . now() . '</p>';
});

Route::get('/missouri-simple', function() {
    return '<h1>✅ Missouri Simple Route Working!</h1><p>Time: ' . now() . '</p>';
});

Route::get('/texas-simple', function() {
    return '<h1>✅ Texas Simple Route Working!</h1><p>Time: ' . now() . '</p>';
});

Route::get('/delaware-simple', function() {
    return '<h1>✅ Delaware Simple Route Working!</h1><p>Time: ' . now() . '</p>';
});

Route::get('/admin-simple', function() {
    return '<h1>✅ Admin Simple Route Working!</h1><p>Time: ' . now() . '</p>';
});

// Working Controller Routes (No Auth for Testing)
Route::get('/florida-controller', [App\Http\Controllers\Student\Florida\CoursePlayerController::class, 'index']);
Route::get('/missouri-controller', [App\Http\Controllers\Student\Missouri\CoursePlayerController::class, 'index']);
Route::get('/texas-controller', [App\Http\Controllers\Student\Texas\CoursePlayerController::class, 'index']);
Route::get('/delaware-controller', [App\Http\Controllers\Student\Delaware\CoursePlayerController::class, 'index']);
Route::get('/admin-controller', [App\Http\Controllers\Admin\DashboardController::class, 'index']);

// ========================================
// END SIMPLE WORKING STATE ROUTES
// ========================================

// ========================================
// STATE-SPECIFIC AUTHENTICATION ROUTES
// ========================================

use App\Http\Controllers\Auth\StateAuthController;

// State Authentication Routes
Route::prefix('{state}')->where(['state' => 'florida|missouri|texas|delaware'])->group(function () {
    // Login Routes
    Route::get('/login', [StateAuthController::class, 'showLoginForm'])->name('auth.login.form');
    Route::post('/login', [StateAuthController::class, 'login'])->name('auth.login');
    
    // Registration Routes
    Route::get('/register', [StateAuthController::class, 'showRegistrationForm'])->name('auth.register.form');
    Route::post('/register', [StateAuthController::class, 'register'])->name('auth.register');
});

// Logout Route (Global)
Route::post('/logout', [StateAuthController::class, 'logout'])->name('auth.logout');

// State Dashboard Routes (Protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/florida/dashboard', function() {
        return view('student.florida.dashboard');
    })->name('florida.dashboard')->middleware('state.access:florida');
    
    Route::get('/missouri/dashboard', function() {
        return view('student.missouri.dashboard');
    })->name('missouri.dashboard')->middleware('state.access:missouri');
    
    Route::get('/texas/dashboard', function() {
        return view('student.texas.dashboard');
    })->name('texas.dashboard')->middleware('state.access:texas');
    
    Route::get('/delaware/dashboard', function() {
        return view('student.delaware.dashboard');
    })->name('delaware.dashboard')->middleware('state.access:delaware');
});

// Emergency Login Bypass (for testing UI/UX)
Route::get('/emergency-login', function() {
    // Create or find test user
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'test@example.com'],
        [
            'name' => 'Test User',
            'password' => bcrypt('password'),
            'state_code' => 'florida',
            'email_verified_at' => now()
        ]
    );
    
    // Login the user
    auth()->login($user);
    
    // Redirect to unified dashboard (not state-specific)
    return redirect('/dashboard')->with('success', 'Emergency login successful!');
});

// State-aware course player API routes
Route::middleware(['auth'])->group(function () {
    // Get chapters for specific state table
    Route::get('/api/{stateTable}/courses/{courseId}/chapters', function($stateTable, $courseId) {
        try {
            $chapters = DB::table('chapters')
                ->where('course_id', $courseId)
                ->where('course_table', $stateTable)
                ->where('is_active', true)
                ->orderBy('order_index')
                ->get();
            
            return response()->json($chapters);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to load chapters'], 500);
        }
    });
    
    // Get enrollment details with state info
    Route::get('/api/enrollments/{enrollmentId}/state-info', function($enrollmentId) {
        try {
            $enrollment = DB::table('user_course_enrollments')
                ->where('id', $enrollmentId)
                ->where('user_id', auth()->id())
                ->first();
                
            if (!$enrollment) {
                return response()->json(['error' => 'Enrollment not found'], 404);
            }
            
            $stateInfo = [
                'enrollment_id' => $enrollmentId,
                'course_id' => $enrollment->course_id,
                'course_table' => $enrollment->course_table ?? 'florida_courses',
                'user_state' => auth()->user()->state_code ?? 'florida',
                'payment_status' => $enrollment->payment_status,
                'status' => $enrollment->status,
                'progress_percentage' => $enrollment->progress_percentage ?? 0
            ];
            
            return response()->json($stateInfo);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to get state info'], 500);
        }
    });
    
    // State distribution analytics
    Route::get('/api/admin/analytics/state-distribution', function() {
        try {
            $distribution = [];
            
            $states = [
                'florida' => 'florida_courses',
                'missouri' => 'missouri_courses',
                'texas' => 'texas_courses',
                'delaware' => 'delaware_courses',
                'nevada' => 'nevada_courses'
            ];
            
            foreach ($states as $stateName => $table) {
                try {
                    if (Schema::hasTable($table)) {
                        if ($table === 'florida_courses') {
                            $count = DB::table($table)->count();
                        } else {
                            $count = DB::table($table)->count();
                        }
                        
                        $enrollments = DB::table('user_course_enrollments')
                            ->where('course_table', $table)
                            ->count();
                        
                        $distribution[] = [
                            'state' => ucfirst($stateName),
                            'courses' => $count,
                            'enrollments' => $enrollments,
                            'table' => $table,
                            'status' => 'active'
                        ];
                    } else {
                        $distribution[] = [
                            'state' => ucfirst($stateName),
                            'courses' => 0,
                            'enrollments' => 0,
                            'table' => $table,
                            'status' => 'table_missing'
                        ];
                    }
                } catch (Exception $e) {
                    $distribution[] = [
                        'state' => ucfirst($stateName),
                        'courses' => 0,
                        'enrollments' => 0,
                        'table' => $table,
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return response()->json($distribution);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
});

// ========================================
// END STATE AUTHENTICATION ROUTES
// ========================================

// Multi-state certificate routes
Route::middleware(['auth'])->group(function () {
    Route::get('/certificates', [App\Http\Controllers\MultiStateCertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{enrollment}/generate', [App\Http\Controllers\MultiStateCertificateController::class, 'generate'])->name('certificates.generate');
    Route::get('/certificates/{enrollment}/view', [App\Http\Controllers\MultiStateCertificateController::class, 'view'])->name('certificates.view');
    Route::get('/certificates/{enrollment}/download', [App\Http\Controllers\MultiStateCertificateController::class, 'download'])->name('certificates.download');
    Route::post('/certificates/{enrollment}/email', [App\Http\Controllers\MultiStateCertificateController::class, 'email'])->name('certificates.email');
});

// Certificate verification (public)
Route::get('/verify-certificate', function () {
    return view('certificates.verify');
})->name('certificates.verify');

Route::post('/api/certificates/verify', [App\Http\Controllers\MultiStateCertificateController::class, 'verify'])->name('api.certificates.verify');

// Admin certificate management
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/certificates', [App\Http\Controllers\MultiStateCertificateController::class, 'dashboard'])->name('admin.certificates.dashboard');
    Route::post('/certificates/bulk-action', [App\Http\Controllers\MultiStateCertificateController::class, 'bulkAction'])->name('admin.certificates.bulk-action');
    Route::get('/certificates/{id}/view', [App\Http\Controllers\MultiStateCertificateController::class, 'view'])->name('admin.certificates.view');
    Route::post('/certificates/{id}/email', [App\Http\Controllers\MultiStateCertificateController::class, 'email'])->name('admin.certificates.email');
    Route::get('/certificates/{id}/download', [App\Http\Controllers\MultiStateCertificateController::class, 'download'])->name('admin.certificates.download');
    Route::delete('/certificates/{id}', [App\Http\Controllers\MultiStateCertificateController::class, 'destroy'])->name('admin.certificates.destroy');
    Route::get('/certificates/export', [App\Http\Controllers\MultiStateCertificateController::class, 'export'])->name('admin.certificates.export');
});
// State transmission management routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/state-transmissions', [App\Http\Controllers\StateTransmissionController::class, 'dashboard'])->name('admin.state-transmissions.dashboard');
    Route::get('/state-transmissions/{transmission}', [App\Http\Controllers\StateTransmissionController::class, 'show'])->name('admin.state-transmissions.show');
    Route::post('/state-transmissions/{transmission}/retry', [App\Http\Controllers\StateTransmissionController::class, 'retry'])->name('admin.state-transmissions.retry');
    Route::post('/state-transmissions/bulk-retry', [App\Http\Controllers\StateTransmissionController::class, 'bulkRetry'])->name('admin.state-transmissions.bulk-retry');
    Route::post('/state-transmissions/bulk-submit', [App\Http\Controllers\StateTransmissionController::class, 'bulkSubmitByState'])->name('admin.state-transmissions.bulk-submit');
    Route::post('/state-transmissions/test-connection', [App\Http\Controllers\StateTransmissionController::class, 'testConnection'])->name('admin.state-transmissions.test-connection');
    Route::get('/state-transmissions/export', [App\Http\Controllers\StateTransmissionController::class, 'export'])->name('admin.state-transmissions.export');
    Route::get('/api/state-transmissions/statistics', [App\Http\Controllers\StateTransmissionController::class, 'statistics'])->name('admin.state-transmissions.statistics');
    
    // Manual certificate submission
    Route::post('/certificates/{certificate}/submit-to-state', [App\Http\Controllers\StateTransmissionController::class, 'submitCertificate'])->name('admin.certificates.submit-to-state');
});

// Emergency test route for chapters
Route::get('/test-chapters/{courseId}', function($courseId) {
    try {
        $chapters = \App\Models\Chapter::where('course_id', $courseId)->get();
        return response()->json([
            'success' => true,
            'course_id' => $courseId,
            'chapters_count' => $chapters->count(),
            'chapters' => $chapters
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
// CSRF-free test page
Route::get('/test-no-csrf', function() { 
    return view('test-no-csrf'); 
});
// Fixed course management page
Route::get('/create-course-fixed', function() { 
    return view('create-course-fixed'); 
});

// CSRF-free routes for course management (completely bypass CSRF)
Route::group(['middleware' => []], function () {
    Route::post('/api/no-csrf/import-docx', [App\Http\Controllers\ChapterController::class, 'importDocx']);
    Route::get('/api/no-csrf/courses', [App\Http\Controllers\CourseController::class, 'indexWeb']);
    Route::get('/api/no-csrf/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'indexWeb']);
    Route::post('/api/no-csrf/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'storeWeb']);
});

// ULTIMATE CSRF BYPASS - Direct route without any middleware
Route::post('/api/docx-import-bypass', [App\Http\Controllers\ChapterController::class, 'importDocx'])->withoutMiddleware(['web', 'csrf']);

// Bulk import route - multiple DOCX files at once
Route::post('/api/bulk-import-docx', [App\Http\Controllers\BulkImportController::class, 'bulkImportDocx'])->withoutMiddleware(['web', 'csrf']);
Route::get('/api/bulk-import-progress', [App\Http\Controllers\BulkImportController::class, 'getBulkImportProgress'])->withoutMiddleware(['web', 'csrf']);

// Chapter save bypass route
Route::post('/api/chapter-save-bypass/{courseId}', [App\Http\Controllers\ChapterController::class, 'storeWeb'])->withoutMiddleware(['web', 'csrf']);
Route::put('/api/chapter-update-bypass/{id}', [App\Http\Controllers\ChapterController::class, 'updateWeb'])->withoutMiddleware(['web', 'csrf']);
Route::delete('/api/chapter-delete-bypass/{chapter}', [App\Http\Controllers\ChapterController::class, 'destroyWeb'])->withoutMiddleware(['web', 'csrf']);
// DOCX import test pages
Route::get('/test-docx-only', function() { 
    return view('test-docx-only'); 
});

Route::get('/docx-import-working', function() { 
    return view('docx-import-working'); 
});

Route::get('/chapter-save-test', function() { 
    return view('chapter-save-test'); 
});

Route::get('/chapter-management-complete', function() { 
    return view('chapter-management-complete'); 
});
// CSRF disabled test page
Route::get('/test-csrf-disabled', function() { 
    return view('test-csrf-disabled'); 
});
// Ultimate test page
Route::get('/ultimate-test', function() { 
    return view('ultimate-test'); 
});