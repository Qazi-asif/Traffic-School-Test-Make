<?php

use App\Http\Controllers\DicdsOrderAmendmentController;
use App\Http\Controllers\DicdsReceiptController;
use App\Http\Controllers\FloridaApprovalController;
use App\Http\Controllers\FloridaAuditController;
use App\Http\Controllers\FloridaComplianceController;
use App\Http\Controllers\FloridaDataExportController;
use App\Http\Controllers\FloridaSecurityLogController;
use App\Http\Controllers\MissouriController;
use App\Http\Controllers\SecurityVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Public API routes (no CSRF required)
Route::get('/security/all-questions', [SecurityVerificationController::class, 'getAllQuestions']);
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working',
        'timestamp' => now()->toISOString()
    ]);
});

Route::middleware('web')->group(function () {
    // Order Amendment Routes
    Route::put('/dicds-orders/{id}/amend', [DicdsOrderAmendmentController::class, 'amend']);
    Route::get('/dicds-orders/{id}/amendment-history', [DicdsOrderAmendmentController::class, 'history']);

    // Receipt Routes
    Route::post('/dicds-orders/{id}/generate-receipt', [DicdsReceiptController::class, 'generate']);
    Route::get('/dicds-orders/{id}/receipt', [DicdsReceiptController::class, 'show']);
    Route::post('/dicds-orders/{id}/mark-printed', [DicdsReceiptController::class, 'markPrinted']);

    // Florida Approval Routes
    Route::put('/dicds-orders/{id}/update-approval', [FloridaApprovalController::class, 'updateApproval']);
    Route::get('/dicds-orders/pending-approval', [FloridaApprovalController::class, 'pendingApproval']);

    // Florida Security Routes
    Route::get('/florida-security/logs', [FloridaSecurityLogController::class, 'index']);
    Route::post('/florida-security/force-logout', [FloridaSecurityLogController::class, 'forceLogout']);
    Route::get('/florida-security/my-sessions', [FloridaSecurityLogController::class, 'mySessions']);
    Route::post('/florida-security/revoke-session/{id}', [FloridaSecurityLogController::class, 'revokeSession']);

    // Florida Audit Routes
    Route::get('/florida-audit/trails', [FloridaAuditController::class, 'trails']);
    Route::post('/florida-audit/generate-report', [FloridaAuditController::class, 'generateReport']);
    Route::get('/florida-audit/compliance-status', [FloridaAuditController::class, 'complianceStatus']);

    // Florida Compliance Routes
    Route::get('/florida-compliance/checks', [FloridaComplianceController::class, 'index']);
    Route::post('/florida-compliance/checks/{checkType}/run', [FloridaComplianceController::class, 'runCheck']);
    Route::get('/florida-compliance/upcoming-due', [FloridaComplianceController::class, 'upcomingDue']);

    // Florida Data Export Routes
    Route::post('/florida-data-exports/request', [FloridaDataExportController::class, 'request']);
    Route::get('/florida-data-exports/status/{id}', [FloridaDataExportController::class, 'status']);
    Route::get('/florida-data-exports/download/{id}', [FloridaDataExportController::class, 'download']);

    // California CTSI Callback Route (Public - receives XML from CTSI)
    Route::post('/ctsi/result', [App\Http\Controllers\Api\CtsiCallbackController::class, 'handleCallback'])->name('api.ctsi.result');

    // Florida Accessibility Routes
    Route::get('/florida-accessibility/preferences', [App\Http\Controllers\FloridaAccessibilityController::class, 'getPreferences']);
    Route::put('/florida-accessibility/preferences', [App\Http\Controllers\FloridaAccessibilityController::class, 'updatePreferences']);
    Route::post('/florida-accessibility/reset-preferences', [App\Http\Controllers\FloridaAccessibilityController::class, 'resetPreferences']);

    // Florida Mobile Optimization Routes
    Route::get('/florida-device/info', [App\Http\Controllers\FloridaMobileOptimizationController::class, 'getDeviceInfo']);
    Route::get('/florida-mobile/course/{courseId}', [App\Http\Controllers\FloridaMobileOptimizationController::class, 'getMobileCourse']);
    Route::post('/florida-mobile/track-activity', [App\Http\Controllers\FloridaMobileOptimizationController::class, 'trackActivity']);

    // Florida Analytics Routes
    Route::get('/florida-analytics/mobile-performance', [App\Http\Controllers\FloridaAnalyticsController::class, 'mobilePerformance']);

    // DICDS System Routes
    Route::get('/dicds/welcome', [App\Http\Controllers\DicdsWelcomeController::class, 'welcome']);
    Route::post('/dicds/welcome/continue', [App\Http\Controllers\DicdsWelcomeController::class, 'continue']);

    Route::get('/dicds/main-menu', [App\Http\Controllers\DicdsNavigationController::class, 'mainMenu']);
    Route::post('/dicds/navigation/{action}', [App\Http\Controllers\DicdsNavigationController::class, 'navigate']);

    Route::get('/dicds/user-management/users', [App\Http\Controllers\DicdsUserManagementController::class, 'getUsers']);
    Route::put('/dicds/user-management/users/{id}/status', [App\Http\Controllers\DicdsUserManagementController::class, 'updateStatus']);
    Route::post('/dicds/user-management/users/{id}/reset-password', [App\Http\Controllers\DicdsUserManagementController::class, 'resetPassword']);
    Route::put('/dicds/user-management/users/{id}/access-role', [App\Http\Controllers\DicdsUserManagementController::class, 'updateAccessRole']);

    Route::get('/dicds/access-requests', [App\Http\Controllers\DicdsAccessController::class, 'index']);
    Route::post('/dicds/access-requests', [App\Http\Controllers\DicdsAccessController::class, 'store']);
    Route::put('/dicds/access-requests/{id}/approve', [App\Http\Controllers\DicdsAccessController::class, 'approve']);

    Route::post('/dicds/help/tickets', [App\Http\Controllers\DicdsHelpController::class, 'submitTicket']);
    Route::get('/dicds/help/tickets', [App\Http\Controllers\DicdsHelpController::class, 'getTickets']);
    Route::put('/dicds/help/tickets/{id}/respond', [App\Http\Controllers\DicdsHelpController::class, 'respond']);
});

// Florida PWA Routes (public)
Route::get('/florida-pwa/manifest', [App\Http\Controllers\FloridaPWAController::class, 'manifest']);
Route::get('/florida-pwa/service-worker', [App\Http\Controllers\FloridaPWAController::class, 'serviceWorker']);

// Florida Analytics (public for testing)
Route::get('/florida-analytics/mobile-performance', function () {
    return response()->json([
        'analytics' => [],
        'device_sessions' => [],
        'total_mobile_users' => 0,
    ]);
});

// DICDS Routes (public for testing)
Route::get('/dicds/user-management/users', [App\Http\Controllers\DicdsUserManagementController::class, 'getUsers']);
Route::put('/dicds/user-management/users/{id}/status', [App\Http\Controllers\DicdsUserManagementController::class, 'updateStatus']);
Route::post('/dicds/user-management/users/{id}/reset-password', [App\Http\Controllers\DicdsUserManagementController::class, 'resetPassword']);

// Florida DICDS Integration
Route::post('/enrollments/{enrollmentId}/submit-to-dicds', [App\Http\Controllers\DicdsIntegrationController::class, 'submitToDicds']);
Route::get('/enrollments/{enrollmentId}/submission-status', [App\Http\Controllers\DicdsIntegrationController::class, 'getSubmissionStatus']);
Route::post('/dicds/test-connection', [App\Http\Controllers\DicdsIntegrationController::class, 'testConnection']);

// Certificate Generation & Verification
Route::middleware('web')->get('/certificates/{id}/download', function ($id) {
    try {
        $certificate = \App\Models\FloridaCertificate::with(['enrollment.user', 'enrollment.course'])->findOrFail($id);
        
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

        // Build due date
        $due_date = null;
        if ($user->due_month && $user->due_day && $user->due_year) {
            $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
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
            'due_date' => $due_date ?: ($certificate->traffic_school_due_date ? 
                \Carbon\Carbon::parse($certificate->traffic_school_due_date)->format('m/d/Y') : null),
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
        
        return $pdf->download('certificate-'.$certificate->dicds_certificate_number.'.pdf');

    } catch (\Exception $e) {
        \Log::error('Certificate download error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to generate certificate PDF'], 500);
    }
});
Route::middleware('web')->post('/certificates/{id}/download', function ($id, Request $request) {
    $certificate = \App\Models\FloridaCertificate::findOrFail($id);

    // Update certificate with additional info
    $certificate->update([
        'driver_license_number' => $request->driver_license_number,
        'citation_number' => $request->citation_number,
        'citation_county' => $request->citation_county,
        'traffic_school_due_date' => $request->traffic_school_due_date,
        'student_address' => $request->student_address,
        'student_date_of_birth' => $request->student_date_of_birth,
        'court_name' => $request->court_name,
    ]);

    $html = view('certificates.florida-certificate', compact('certificate'))->render();

    return response($html)
        ->header('Content-Type', 'text/html')
        ->header('Content-Disposition', 'attachment; filename="certificate-'.$certificate->dicds_certificate_number.'.html"');
});

// Email certificate route
Route::middleware('web')->post('/certificates/{id}/email', [App\Http\Controllers\CertificateController::class, 'emailCertificate']);

Route::get('/certificates/verify/{hash}', [App\Http\Controllers\CertificateVerificationController::class, 'verify']);

// My Certificates
// Push Notifications
Route::middleware('web')->post('/push-notification', function (Request $request) {
    $email = $request->email;
    $type = $request->type;
    $title = $request->title;
    $message = $request->message;

    \Log::info('Sending notification to: '.$email, [
        'type' => $type,
        'title' => $title,
        'message' => $message,
    ]);

    // Store notification in database for the specific user
    $notification = \App\Models\PushNotification::create([
        'user_email' => $email,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'is_read' => false,
    ]);

    \Log::info('Notification stored in database with ID: '.$notification->id);

    return response()->json(['success' => true, 'debug' => 'Notification stored for '.$email]);
});

Route::middleware('web')->get('/check-notifications', function () {
    try {
        if (!auth()->check()) {
            return response()->json(['debug' => 'Not authenticated'], 200);
        }

        $user = auth()->user();
        if (!$user || !$user->email) {
            return response()->json(['debug' => 'Invalid user'], 200);
        }

        // Check if push_notifications table exists
        if (!Schema::hasTable('push_notifications')) {
            return response()->json(['debug' => 'Notifications table not found'], 200);
        }

        // Check if PushNotification model exists
        if (!class_exists('App\\Models\\PushNotification')) {
            return response()->json(['debug' => 'PushNotification model not found'], 200);
        }

        // Get unread notifications for this user
        $notification = \App\Models\PushNotification::where('user_email', $user->email)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($notification) {
            \Log::info('Found notification for user: ' . $user->email, $notification->toArray());

            // Mark as read
            $notification->update(['is_read' => true]);

            return response()->json([
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
            ]);
        }

        return response()->json(['debug' => 'No notifications']);
        
    } catch (Exception $e) {
        \Log::error('Notification check error: ' . $e->getMessage());
        return response()->json(['debug' => 'Error: ' . $e->getMessage()], 200);
    }
});

// Florida Security Dashboard
Route::middleware('web')->get('/florida-security-data', function () {
    try {
        // Get security stats from actual data
        $events24h = \App\Models\SecurityLog::where('created_at', '>=', now()->subDay())->count();
        $failedLogins = \App\Models\SecurityLog::where('event_type', 'failed_login')
            ->where('created_at', '>=', now()->subDay())->count();
        $highRisk = \App\Models\SecurityLog::where('risk_level', 'high')
            ->where('created_at', '>=', now()->subDay())->count();

        // Get recent security events
        $recentEvents = \App\Models\SecurityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'event' => $log->event_type,
                    'user' => $log->user ? $log->user->email : $log->ip_address,
                    'risk_level' => $log->risk_level,
                    'time' => $log->created_at->format('M j, Y g:i A'),
                    'details' => $log->details,
                ];
            });

        return response()->json([
            'stats' => [
                'events24h' => $events24h,
                'failedLogins' => $failedLogins,
                'highRisk' => $highRisk,
            ],
            'recentEvents' => $recentEvents,
        ]);

    } catch (\Exception $e) {
        // Fallback to sample data if SecurityLog model doesn't exist
        return response()->json([
            'stats' => [
                'events24h' => rand(20, 50),
                'failedLogins' => rand(0, 10),
                'highRisk' => rand(0, 5),
            ],
            'recentEvents' => [
                [
                    'event' => 'Login Success',
                    'user' => 'admin@example.com',
                    'risk_level' => 'low',
                    'time' => now()->format('M j, Y g:i A'),
                    'details' => 'Successful login from trusted IP',
                ],
                [
                    'event' => 'Failed Login',
                    'user' => 'unknown@example.com',
                    'risk_level' => 'medium',
                    'time' => now()->subMinutes(15)->format('M j, Y g:i A'),
                    'details' => 'Multiple failed login attempts',
                ],
                [
                    'event' => 'Password Reset',
                    'user' => 'user@example.com',
                    'risk_level' => 'low',
                    'time' => now()->subHour()->format('M j, Y g:i A'),
                    'details' => 'Password reset requested',
                ],
            ],
        ]);
    }
});

Route::middleware('web')->get('/my-certificates', function () {
    try {
        if (! auth()->check()) {
            return response()->json(['debug' => 'Not authenticated']);
        }

        $userId = auth()->id();

        // Get certificates with course information
        $certificates = DB::table('florida_certificates as fc')
            ->join('user_course_enrollments as uce', 'fc.enrollment_id', '=', 'uce.id')
            ->leftJoin('florida_courses as fcourse', 'uce.course_id', '=', 'fcourse.id')
            ->where('uce.user_id', $userId)
            ->select(
                'fc.id',
                'fc.dicds_certificate_number',
                'fc.student_name',
                'fc.completion_date',
                'fc.final_exam_score',
                'fc.verification_hash',
                'fc.state',
                'fcourse.title as course_name'
            )
            ->orderBy('fc.completion_date', 'desc')
            ->get();

        return response()->json(['certificates' => $certificates]);
    } catch (\Exception $e) {
        \Log::error('My Certificates Error: '.$e->getMessage());

        return response()->json(['error' => $e->getMessage()]);
    }
});

// Enrollments API
Route::get('/enrollments', function () {
    try {
        $enrollments = \App\Models\UserCourseEnrollment::with(['user', 'floridaCourse'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($enrollments);
    } catch (\Exception $e) {
        \Log::error('Enrollments API error: '.$e->getMessage());

        return response()->json([]);
    }
});

// Get questions for a chapter
Route::get('/chapters/{chapterId}/questions', function ($chapterId) {
    \Log::info("API: Fetching questions for chapter {$chapterId}");

    try {
        // Handle special "final-exam" chapter
        if ($chapterId === 'final-exam') {
            // Get course_id from request (for admin) or enrollment (for students)
            $courseId = request('course_id');
            
            if (!$courseId && request('enrollment_id')) {
                $enrollment = \DB::table('user_course_enrollments')->where('id', request('enrollment_id'))->first();
                $courseId = $enrollment ? $enrollment->course_id : 1;
            }
            
            $courseId = $courseId ?: 1; // Default to 1 if still not found
            
            $questions = \DB::table('final_exam_questions')
                ->where('course_id', $courseId)
                ->orderBy('order_index')
                ->get();
                
            \Log::info("API: Found {$questions->count()} final exam questions");
            
            $processedQuestions = $questions->map(function ($question) {
                $options = json_decode($question->options, true) ?: [];
                
                return [
                    'id' => $question->id,
                    'chapter_id' => 'final-exam',
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'correct_answer' => $question->correct_answer,
                    'explanation' => $question->explanation,
                    'points' => $question->points,
                    'order_index' => $question->order_index,
                    'options' => $options,
                ];
            });
            
            \Log::info("API: Response created successfully");
            return response()->json($processedQuestions);
        }

        // Get course info for random selection settings
        $courseId = null;
        $courseTable = 'courses';
        
        if (request('enrollment_id')) {
            $enrollment = \DB::table('user_course_enrollments')->where('id', request('enrollment_id'))->first();
            if ($enrollment) {
                $courseId = $enrollment->course_id;
                $courseTable = $enrollment->course_table ?? 'courses';
            }
        }

        // First try to get questions directly for this chapter ID
        $questionsQuery = \App\Models\ChapterQuestion::where('chapter_id', $chapterId);
        
        // Add quiz_set filtering for Delaware courses
        if (request('quiz_set')) {
            $questionsQuery->where('quiz_set', request('quiz_set'));
        }
        
        $questions = $questionsQuery->orderBy('order_index')->get();

        \Log::info("API: Direct lookup found {$questions->count()} questions for chapter {$chapterId}");

        // If no questions found in chapter_questions, try the old questions table
        if ($questions->isEmpty()) {
            \Log::info("API: No questions in chapter_questions, checking old questions table");
            
            $oldQuestionsQuery = \DB::table('questions')->where('chapter_id', $chapterId);
            
            // For Delaware courses, if quiz_set is specified, we need to simulate it
            // Since old questions don't have quiz_set, we'll treat them as quiz_set 1
            if (request('quiz_set') && request('quiz_set') == 2) {
                \Log::info("API: Quiz set 2 requested for old questions - returning empty for rotation");
                $oldQuestions = collect(); // Return empty for quiz set 2 to trigger rotation
            } else {
                $oldQuestions = $oldQuestionsQuery->get();
            }
            
            if ($oldQuestions->count() > 0) {
                \Log::info("API: Found {$oldQuestions->count()} questions in old questions table");
                
                // Convert old questions to the expected format
                $questions = $oldQuestions->map(function ($question) {
                    return (object) [
                        'id' => $question->id,
                        'chapter_id' => $question->chapter_id,
                        'question_text' => $question->question_text ?? $question->question ?? '',
                        'question_type' => $question->question_type ?? 'multiple_choice',
                        'correct_answer' => $question->correct_answer ?? '',
                        'explanation' => $question->explanation ?? '',
                        'points' => $question->points ?? 1,
                        'order_index' => $question->order_index ?? 1,
                        'options' => $question->options ?? '[]',
                        'quiz_set' => 1, // Default to quiz set 1 for old questions
                    ];
                });
            }
        }

        // Always check old questions table as well and merge results
        if (!request('quiz_set') || request('quiz_set') == 1) {
            $oldQuestions = \DB::table('questions')->where('chapter_id', $chapterId)->get();
            
            if ($oldQuestions->count() > 0) {
                \Log::info("API: Also found {$oldQuestions->count()} questions in old questions table to merge");
                
                // Convert old questions to the expected format
                $oldQuestionsFormatted = $oldQuestions->map(function ($question) {
                    return (object) [
                        'id' => 'old_' . $question->id, // Prefix to avoid ID conflicts
                        'chapter_id' => $question->chapter_id,
                        'question_text' => $question->question_text ?? $question->question ?? '',
                        'question_type' => $question->question_type ?? 'multiple_choice',
                        'correct_answer' => $question->correct_answer ?? '',
                        'explanation' => $question->explanation ?? '',
                        'points' => $question->points ?? 1,
                        'order_index' => $question->order_index ?? 1,
                        'options' => $question->options ?? '[]',
                        'quiz_set' => 1, // Default to quiz set 1 for old questions
                    ];
                });
                
                // Convert to array and merge
                $questionsArray = $questions->toArray();
                $oldQuestionsArray = $oldQuestionsFormatted->toArray();
                $questions = collect(array_merge($questionsArray, $oldQuestionsArray));
            }
        }

        // If no questions found, try to find matching chapter in the other table
        if ($questions->isEmpty()) {
            \Log::info("API: No direct questions found, searching for matching chapter");
            
            // Check if this is a chapter, find matching legacy chapter
            $courseChapter = \DB::table('chapters')->where('id', $chapterId)->first();
            $legacyChapter = null;
            $matchingChapterId = null;
            
            if ($courseChapter) {
                \Log::info("API: Found course_chapter {$chapterId}: {$courseChapter->title} (Course: {$courseChapter->course_id})");
                
                // Find matching legacy chapter
                $legacyChapter = \DB::table('chapters')
                    ->where('course_id', $courseChapter->course_id)
                    ->where(function($query) use ($courseChapter) {
                        // Try exact title match first
                        $query->where('title', $courseChapter->title)
                              // Or try matching common patterns
                              ->orWhere('title', 'LIKE', '%' . str_replace(' ', '%', $courseChapter->title) . '%')
                              // Or match by order_index if titles are different
                              ->orWhere('order_index', $courseChapter->order_index);
                    })
                    ->first();
                    
                if ($legacyChapter) {
                    $matchingChapterId = $legacyChapter->id;
                    \Log::info("API: Found matching legacy chapter {$legacyChapter->id}: {$legacyChapter->title}");
                }
            } else {
                // Check if this is a legacy chapter, find matching course_chapter
                $legacyChapter = \DB::table('chapters')->where('id', $chapterId)->first();
                
                if ($legacyChapter) {
                    \Log::info("API: Found legacy chapter {$chapterId}: {$legacyChapter->title} (Course: {$legacyChapter->course_id})");
                    
                    // Find matching chapter
                    $courseChapter = \DB::table('chapters')
                        ->where('course_id', $legacyChapter->course_id)
                        ->where(function($query) use ($legacyChapter) {
                            // Try exact title match first
                            $query->where('title', $legacyChapter->title)
                                  // Or try matching common patterns (legacy chapters often have ALL CAPS)
                                  ->orWhere('title', 'LIKE', '%' . str_replace(' ', '%', strtolower($legacyChapter->title)) . '%')
                                  // Or match by order_index
                                  ->orWhere('order_index', $legacyChapter->order_index);
                        })
                        ->first();
                        
                    if ($courseChapter) {
                        $matchingChapterId = $courseChapter->id;
                        \Log::info("API: Found matching course_chapter {$courseChapter->id}: {$courseChapter->title}");
                    }
                }
            }
            
            // If we found a matching chapter, get questions from it
            if ($matchingChapterId) {
                $questionsQuery = \App\Models\ChapterQuestion::where('chapter_id', $matchingChapterId);
                
                // Add quiz_set filtering for Delaware courses
                if (request('quiz_set')) {
                    $questionsQuery->where('quiz_set', request('quiz_set'));
                }
                
                $questions = $questionsQuery->orderBy('order_index')->get();
                    
                \Log::info("API: Found {$questions->count()} questions from matching chapter {$matchingChapterId}");
            }
            
            // If still no questions and this is a Texas course, try to find questions from equivalent chapter in other Texas courses
            if ($questions->isEmpty() && $legacyChapter && in_array($legacyChapter->course_id, [5, 21, 22])) {
                \Log::info("API: Searching for equivalent questions in other Texas courses");
                
                // Find equivalent chapters in other Texas courses by order_index
                $equivalentChapters = \DB::table('chapters')
                    ->whereIn('course_id', [5, 21, 22])
                    ->where('course_id', '!=', $legacyChapter->course_id)
                    ->where('order_index', $legacyChapter->order_index)
                    ->get();
                    
                foreach ($equivalentChapters as $equivChapter) {
                    $questionsQuery = \App\Models\ChapterQuestion::where('chapter_id', $equivChapter->id);
                    
                    // Add quiz_set filtering for Delaware courses
                    if (request('quiz_set')) {
                        $questionsQuery->where('quiz_set', request('quiz_set'));
                    }
                    
                    $questions = $questionsQuery->orderBy('order_index')->get();
                        
                    if ($questions->isNotEmpty()) {
                        \Log::info("API: Found {$questions->count()} questions from equivalent chapter {$equivChapter->id} in course {$equivChapter->course_id}");
                        break;
                    }
                }
            }
        }

        \Log::info("API: Final result: {$questions->count()} questions for chapter {$chapterId}");

        $processedQuestions = $questions->map(function ($question) {
            // Handle different object types
            if (is_array($question)) {
                $data = $question;
            } elseif ($question instanceof \Illuminate\Database\Eloquent\Model) {
                $data = $question->toArray();
            } else {
                // Handle stdClass objects from DB queries
                $data = (array) $question;
            }

            // Handle options field safely
            if (isset($data['options'])) {
                if (is_string($data['options'])) {
                    $cleaned = trim($data['options']);

                    if (empty($cleaned) || $cleaned === 'null') {
                        $data['options'] = [];
                    } else {
                        $decoded = json_decode($cleaned, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            \Log::error('API: JSON decode error: '.json_last_error_msg()." for string: '{$cleaned}'");
                            $data['options'] = [];
                        } else {
                            $data['options'] = $decoded !== null ? $decoded : [];
                        }
                    }
                } elseif (! is_array($data['options'])) {
                    $data['options'] = [];
                }
            } else {
                $data['options'] = [];
            }

            return $data;
        });

        // Apply random selection if configured and this is a student request (has enrollment_id)
        if ($courseId && request('enrollment_id') && !request('course_id')) {
            $settings = \DB::table('chapter_quiz_settings')
                ->where('course_id', $courseId)
                ->where('chapter_id', $chapterId)
                ->where('course_table', $courseTable)
                ->first();
            
            if ($settings && $settings->use_random_selection && $settings->questions_to_select > 0) {
                \Log::info("API: Applying random selection - {$settings->questions_to_select} from {$processedQuestions->count()} questions");
                
                // Shuffle and take the specified number of questions
                $processedQuestions = $processedQuestions->shuffle()->take($settings->questions_to_select);
                
                \Log::info("API: Random selection applied - now have {$processedQuestions->count()} questions");
            }
        }

        return response()->json($processedQuestions);
    } catch (\Exception $e) {
        \Log::error('API: Chapter questions error: '.$e->getMessage());
        \Log::error('API: Stack trace: '.$e->getTraceAsString());

        return response()->json([]);
    }
});

// Get quiz progress for Delaware courses
Route::get('/chapters/{chapterId}/quiz-progress', function ($chapterId) {
    try {
        $enrollmentId = request('enrollment_id');
        
        if (!$enrollmentId) {
            return response()->json(['current_quiz_set' => 1]);
        }
        
        $progress = \DB::table('user_course_progress')
            ->where('enrollment_id', $enrollmentId)
            ->where('chapter_id', $chapterId)
            ->first();
            
        return response()->json([
            'current_quiz_set' => $progress->current_quiz_set ?? 1,
            'quiz_set_1_attempts' => $progress->quiz_set_1_attempts ?? 0,
            'quiz_set_2_attempts' => $progress->quiz_set_2_attempts ?? 0,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('API: Quiz progress error: ' . $e->getMessage());
        return response()->json(['current_quiz_set' => 1]);
    }
});

// Export sample DOCX
Route::get('/chapters/{chapterId}/questions/export-sample', function ($chapterId) {
    try {
        $phpWord = new \PhpOffice\PhpWord\PhpWord;
        $section = $phpWord->addSection();

        $section->addTitle('Question Import Template', 1);
        $section->addText('Instructions: Fill in the table below with your questions. Each row represents one question.');
        $section->addTextBreak();

        $table = $section->addTable();
        $table->addRow();
        $table->addCell(1500)->addText('Question Text');
        $table->addCell(1500)->addText('Type');
        $table->addCell(2000)->addText('Options');
        $table->addCell(1500)->addText('Correct Answer');
        $table->addCell(1500)->addText('Explanation');
        $table->addCell(1000)->addText('Points');
        $table->addCell(1000)->addText('Order');

        // Add sample row
        $table->addRow();
        $table->addCell(1500)->addText('What is 2+2?');
        $table->addCell(1500)->addText('multiple_choice');
        $table->addCell(2000)->addText('3|4|5|6');
        $table->addCell(1500)->addText('4');
        $table->addCell(1500)->addText('Basic arithmetic');
        $table->addCell(1000)->addText('1');
        $table->addCell(1000)->addText('1');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $filename = 'questions_sample_chapter_'.$chapterId.'.docx';
        $path = storage_path('app/temp/'.$filename);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $objWriter->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    } catch (\Exception $e) {
        \Log::error('Export error: '.$e->getMessage());

        return response()->json(['error' => 'Export failed'], 500);
    }
});

// Import DOCX
Route::post('/chapters/{chapterId}/questions/import', function ($chapterId) {
    try {
        if (! request()->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $file = request()->file('file');

        // Extract text from DOCX using ZipArchive
        $zip = new \ZipArchive();
        $text = '';
        
        if ($zip->open($file->getPathname()) === true) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml) {
                $text = strip_tags(str_replace('>', ">\n", $xml));
            }
            $zip->close();
        }

        // Log lines that start with numbers
        $lines = explode("\n", $text);
        foreach ($lines as $i => $line) {
            if (preg_match('/^\d+\./', trim($line))) {
                \Log::info("Line {$i} matches number pattern: " . trim($line));
            }
        }

        // Parse questions with *** format
        $questions = [];
        $lines = explode("\n", $text);
        $currentQuestion = null;
        $currentOptions = [];
        $correctAnswer = null;

        \Log::info('Starting to parse lines, total: ' . count($lines));

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Debug all lines between questions 1-3 to see what's there
            if ($lineNum >= 66 && $lineNum <= 210) {
                \Log::info("DEBUG Q1 area Line {$lineNum}: '{$line}'");
            }
            if ($lineNum >= 212 && $lineNum <= 356) {
                \Log::info("DEBUG Q2 area Line {$lineNum}: '{$line}'");
            }
            if ($lineNum >= 358 && $lineNum <= 524) {
                \Log::info("DEBUG Q3 area Line {$lineNum}: '{$line}'");
            }

            // Debug specific lines
            if ($lineNum == 137 || $lineNum == 255 || $lineNum == 426) {
                \Log::info("DEBUG Line {$lineNum}: '" . $line . "' (hex: " . bin2hex($line) . ")");
                if (preg_match('/^(\d+)\s*\.\s+(.+)$/', $line, $matches)) {
                    \Log::info("DEBUG Line {$lineNum}: MATCHES regex");
                } else {
                    \Log::info("DEBUG Line {$lineNum}: DOES NOT match regex");
                }
            }

            // Question line (starts with number, optional space/nbsp before period, then space and text)
            if (preg_match('/^(\d+)[\s\x{00A0}]*\.[\s\x{00A0}]+(.+)$/u', $line, $matches)) {
                \Log::info("Line {$lineNum}: Found question - " . $matches[2]);
                if ($currentQuestion && !empty($currentOptions)) {
                    \Log::info("Saving previous question with " . count($currentOptions) . " options");
                    $questions[] = [
                        'question' => $currentQuestion,
                        'options' => $currentOptions,
                        'correct_answer' => $correctAnswer
                    ];
                }
                $currentQuestion = $matches[2];
                $currentOptions = [];
                $correctAnswer = null;
            }
            // Option line (starts with letter, may have ***) - only if we have a question
            elseif ($currentQuestion && preg_match('/^([A-E])\.[\s\t]+(.+)$/u', $line, $matches)) {
                $letter = $matches[1];
                $optionText = trim($matches[2]);
                
                // Check if *** is at the end of the option text
                $isCorrect = false;
                if (str_ends_with($optionText, '***')) {
                    $isCorrect = true;
                    $optionText = trim(str_replace('***', '', $optionText));
                }
                
                \Log::info("Line {$lineNum}: Found option {$letter} - {$optionText}" . ($isCorrect ? ' (CORRECT)' : ''));
                
                $currentOptions[$letter] = $optionText;
                if ($isCorrect) {
                    $correctAnswer = $letter;
                }
            }
            // If we have a question but line doesn't match option pattern, append to question text
            elseif ($currentQuestion && empty($currentOptions)) {
                \Log::info("Line {$lineNum}: Appending to question - {$line}");
                $currentQuestion .= ' ' . $line;
            }
        }

        // Save last question
        if ($currentQuestion && !empty($currentOptions)) {
            \Log::info("Saving last question with " . count($currentOptions) . " options");
            $questions[] = [
                'question' => $currentQuestion,
                'options' => $currentOptions,
                'correct_answer' => $correctAnswer
            ];
        }

        \Log::info('Total questions parsed: ' . count($questions));

        // Get course_id from chapter
        $chapter = \App\Models\Chapter::find($chapterId);
        $courseId = $chapter ? $chapter->course_id : null;

        if (!$courseId) {
            return response()->json(['message' => 'Chapter not found'], 404);
        }

        // Import questions
        $count = 0;
        foreach ($questions as $index => $questionData) {
            \App\Models\Question::create([
                'chapter_id' => $chapterId,
                'course_id' => $courseId,
                'question_text' => $questionData['question'],
                'question_type' => 'multiple_choice',
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'order_index' => $index + 1,
            ]);
            $count++;
        }

        return response()->json([
            'count' => $count,
            'message' => 'Import successful',
            'debug' => [
                'text_length' => strlen($text),
                'questions_parsed' => count($questions)
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Import error: '.$e->getMessage());
        return response()->json(['message' => 'Import failed: '.$e->getMessage()], 500);
    }
});

// Final Exam TXT Import (specific route - must come before generic route)
Route::middleware('web')->post('/chapters/final-exam/questions/import-txt', function () {
    \Log::info('Final Exam TXT Import: Route accessed');
    
    try {
        \Log::info('Final Exam TXT Import: Checking for file upload');
        
        if (!request()->hasFile('file')) {
            \Log::error('Final Exam TXT Import: No file uploaded');
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $file = request()->file('file');
        $courseId = request('course_id', 1);
        
        \Log::info('Final Exam TXT Import: Processing file', [
            'filename' => $file->getClientOriginalName(),
            'course_id' => $courseId,
            'file_size' => $file->getSize()
        ]);
        
        $text = file_get_contents($file->getPathname());
        
        \Log::info('Final Exam TXT Import: File content loaded', [
            'content_length' => strlen($text),
            'first_100_chars' => substr($text, 0, 100)
        ]);
        
        // Parse questions from txt format
        $questions = [];
        $lines = explode("\n", $text);
        $currentQuestion = null;
        $currentOptions = [];
        $correctAnswer = null;

        \Log::info('Final Exam TXT Import: Starting to parse lines', ['total_lines' => count($lines)]);

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if it's a question (starts with number and parenthesis)
            if (preg_match('/^(\d+)\)\s*(.+)/', $line, $matches)) {
                // Save previous question if exists
                if ($currentQuestion && !empty($currentOptions)) {
                    $questions[] = [
                        'question' => $currentQuestion,
                        'options' => $currentOptions,
                        'correct_answer' => $correctAnswer
                    ];
                    \Log::info('Final Exam TXT Import: Saved question', ['question_count' => count($questions)]);
                }
                
                // Start new question
                $currentQuestion = $matches[2];
                $currentOptions = [];
                $correctAnswer = null;
                \Log::info('Final Exam TXT Import: Found new question', ['line' => $lineNum, 'question' => substr($currentQuestion, 0, 50)]);
            }
            // Check if it's an option (starts with A), B), C), D))
            elseif (preg_match('/^([A-Z])\)\s*(.+?)(\s*\*\*\*)?$/', $line, $matches)) {
                $optionLetter = $matches[1];
                $optionText = trim($matches[2]);
                $isCorrect = isset($matches[3]) && !empty($matches[3]);
                
                $currentOptions[$optionLetter] = $optionText;
                
                if ($isCorrect) {
                    $correctAnswer = $optionLetter;
                    \Log::info('Final Exam TXT Import: Found correct answer', ['option' => $optionLetter, 'text' => substr($optionText, 0, 30)]);
                }
            }
        }
        
        // Save last question
        if ($currentQuestion && !empty($currentOptions)) {
            $questions[] = [
                'question' => $currentQuestion,
                'options' => $currentOptions,
                'correct_answer' => $correctAnswer
            ];
        }

        \Log::info('Final Exam TXT Import: Parsing complete', ['total_questions' => count($questions)]);

        // Check if course exists in either table (foreign key constraint removed)
        $floridaCourse = \DB::table('florida_courses')->where('id', $courseId)->first();
        $regularCourse = \DB::table('courses')->where('id', $courseId)->first();
        
        if (!$floridaCourse && !$regularCourse) {
            return response()->json([
                'success' => false, 
                'message' => "Course ID {$courseId} not found in either courses or florida_courses tables."
            ], 404);
        }
        
        $courseTitle = $floridaCourse ? $floridaCourse->title : ($regularCourse->course_type . ' - ' . $regularCourse->description);
        $courseTable = $floridaCourse ? 'florida_courses' : 'courses';
        
        \Log::info('Final Exam TXT Import: Course validated', [
            'course_title' => $courseTitle,
            'course_table' => $courseTable
        ]);

        // Import to final_exam_questions table
        $count = 0;
        foreach ($questions as $index => $questionData) {
            \DB::table('final_exam_questions')->insert([
                'course_id' => $courseId,
                'question_text' => $questionData['question'],
                'question_type' => 'multiple_choice',
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'order_index' => $index + 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $count++;
        }

        \Log::info('Final Exam TXT Import: Import complete', ['imported_count' => $count]);

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => 'Final exam questions imported successfully'
        ]);
    } catch (\Exception $e) {
        \Log::error('Final Exam TXT Import error: '.$e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['success' => false, 'message' => 'Import failed: '.$e->getMessage()], 500);
    }
});

// Import TXT (generic route for regular chapters)
Route::post('/chapters/{chapterId}/questions/import-txt', function ($chapterId) {
    try {
        if (!request()->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $file = request()->file('file');
        $text = file_get_contents($file->getPathname());
        
        // Parse questions from txt format like readme.txt
        $questions = [];
        $lines = explode("\n", $text);
        $currentQuestion = null;
        $currentOptions = [];
        $correctAnswer = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if it's a question (starts with number and parenthesis)
            if (preg_match('/^(\d+)\)\s*(.+)/', $line, $matches)) {
                // Save previous question if exists
                if ($currentQuestion && !empty($currentOptions)) {
                    $questions[] = [
                        'question' => $currentQuestion,
                        'options' => $currentOptions,
                        'correct_answer' => $correctAnswer
                    ];
                }
                
                // Start new question
                $currentQuestion = $matches[2];
                $currentOptions = [];
                $correctAnswer = null;
            }
            // Check if it's an option (starts with A), B), C), D))
            elseif (preg_match('/^([A-Z])\)\s*(.+?)(\s*\*\*\*)?$/', $line, $matches)) {
                $optionLetter = $matches[1];
                $optionText = trim($matches[2]);
                $isCorrect = isset($matches[3]) && !empty($matches[3]);
                
                $currentOptions[$optionLetter] = $optionText;
                
                if ($isCorrect) {
                    $correctAnswer = $optionLetter;
                }
            }
        }
        
        // Save last question
        if ($currentQuestion && !empty($currentOptions)) {
            $questions[] = [
                'question' => $currentQuestion,
                'options' => $currentOptions,
                'correct_answer' => $correctAnswer
            ];
        }

        // Get course_id from chapter
        $chapter = \App\Models\Chapter::find($chapterId);
        $courseId = $chapter ? $chapter->course_id : null;

        if (!$courseId) {
            return response()->json(['message' => 'Chapter not found'], 404);
        }

        // Import questions
        $count = 0;
        foreach ($questions as $index => $questionData) {
            \App\Models\Question::create([
                'chapter_id' => $chapterId,
                'course_id' => $courseId,
                'question_text' => $questionData['question'],
                'question_type' => 'multiple_choice',
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'order_index' => $index + 1,
            ]);
            $count++;
        }

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => 'Import successful'
        ]);
    } catch (\Exception $e) {
        \Log::error('TXT Import error: '.$e->getMessage());
        return response()->json(['success' => false, 'message' => 'Import failed: '.$e->getMessage()], 500);
    }
});



Route::delete('/questions/{id}', function ($id) {
    try {
        // First try regular questions table
        $question = \App\Models\Question::find($id);
        if ($question) {
            $question->delete();
            return response()->json(['message' => 'Question deleted successfully']);
        }
        
        // If not found, try final exam questions table
        $deleted = \DB::table('final_exam_questions')->where('id', $id)->delete();
        if ($deleted) {
            return response()->json(['message' => 'Final exam question deleted successfully']);
        }
        
        return response()->json(['message' => 'Question not found'], 404);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Delete failed: '.$e->getMessage()], 500);
    }
});

Route::post('/final-exam/import', function () {
    try {
        if (!request()->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $file = request()->file('file');
        $courseId = request('course_id', 1); // Get course_id from request, default to 1
        $content = file_get_contents($file->getPathname());
        
        // Parse final exam questions
        $questions = [];
        $lines = explode("\n", $content);
        $currentQuestion = null;
        $currentQuestionText = '';
        $currentOptions = [];
        $correctAnswer = null;
        $questionNumber = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Question number line (e.g., "1.)", "2.)")
            if (preg_match('/^(\d+)\.\)/', $line, $matches)) {
                // Save previous question
                if ($currentQuestion && !empty($currentOptions)) {
                    $questions[] = [
                        'question_number' => $questionNumber,
                        'question' => trim($currentQuestionText),
                        'options' => $currentOptions,
                        'correct_answer' => $correctAnswer
                    ];
                }
                
                // Start new question
                $questionNumber = $matches[1];
                $currentQuestionText = trim(str_replace($matches[0], '', $line));
                $currentOptions = [];
                $correctAnswer = null;
                $currentQuestion = true;
            }
            // Option line (e.g., "A)", "*B)", "C)" OR "TRUE", "*FALSE")
            elseif (preg_match('/^(\*?)([A-E])\)\s*(.*)$/', $line, $matches)) {
                $isCorrect = !empty($matches[1]);
                $letter = $matches[2];
                $optionText = trim($matches[3]);
                
                // Also check if * is at the beginning of option text
                if (!$isCorrect && str_starts_with($optionText, '*')) {
                    $isCorrect = true;
                    $optionText = trim(substr($optionText, 1)); // Remove the *
                }
                
                $currentOptions[$letter] = $optionText;
                if ($isCorrect) {
                    $correctAnswer = $letter;
                }
            }
            // Handle TRUE/FALSE format
            elseif (preg_match('/^(\*?)(TRUE|FALSE|True|False)$/i', $line, $matches)) {
                $isCorrect = !empty($matches[1]);
                $option = strtoupper($matches[2]); // Convert to uppercase
                
                // Use proper labels for true/false
                $currentOptions[$option] = $option === 'TRUE' ? 'True' : 'False';
                if ($isCorrect) {
                    $correctAnswer = $option;
                }
            }
            // Continue question text
            elseif ($currentQuestion && empty($currentOptions)) {
                $currentQuestionText .= ' ' . $line;
            }
        }

        // Save last question
        if ($currentQuestion && !empty($currentOptions)) {
            $questions[] = [
                'question_number' => $questionNumber,
                'question' => trim($currentQuestionText),
                'options' => $currentOptions,
                'correct_answer' => $correctAnswer
            ];
        }

        // Import to final_exam_questions table
        $count = 0;
        foreach ($questions as $questionData) {
            \DB::table('final_exam_questions')->insert([
                'course_id' => $courseId, // Use dynamic course ID
                'question_text' => $questionData['question'],
                'question_type' => count($questionData['options']) == 2 ? 'true_false' : 'multiple_choice',
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => null,
                'points' => 1,
                'order_index' => $questionData['question_number'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "Imported {$count} final exam questions"
        ]);

    } catch (\Exception $e) {
        \Log::error('Final exam import error: ' . $e->getMessage());
        return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
    }
});

// Final Exam API Routes
Route::get('/final-exam/count', function () {
    try {
        // Get course_id from enrollment
        $enrollmentId = request('enrollment_id');
        $enrollment = \DB::table('user_course_enrollments')->where('id', $enrollmentId)->first();
        $courseId = $enrollment ? $enrollment->course_id : 1;
        
        $count = \DB::table('final_exam_questions')
            ->where('course_id', $courseId)
            ->count();
            
        return response()->json(['count' => $count, 'course_id' => $courseId]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/final-exam/random/{count}', function ($count) {
    try {
        // Get course_id from enrollment
        $enrollmentId = request('enrollment_id');
        $enrollment = \DB::table('user_course_enrollments')->where('id', $enrollmentId)->first();
        $courseId = $enrollment ? $enrollment->course_id : 1;
        $courseTable = $enrollment ? $enrollment->course_table : 'courses';
        
        // Check if there are custom settings for this course
        $settings = \DB::table('final_exam_settings')
            ->where('course_id', $courseId)
            ->where('course_table', $courseTable)
            ->first();
        
        // Use settings if available, otherwise use the requested count
        $questionsToSelect = $settings ? $settings->questions_to_select : $count;
        
        $questions = \DB::table('final_exam_questions')
            ->where('course_id', $courseId)
            ->inRandomOrder()
            ->limit($questionsToSelect)
            ->get();
            
        $processedQuestions = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'options' => json_decode($question->options, true),
                'correct_answer' => $question->correct_answer,
            ];
        });
        
        return response()->json($processedQuestions);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/final-exam/attempts/{enrollmentId}', function ($enrollmentId) {
    try {
        $attempts = \DB::table('final_exam_results')
            ->where('enrollment_id', $enrollmentId)
            ->count();
            
        // Get custom max attempts if set
        $maxAttempts = \DB::table('final_exam_attempt_limits')
            ->where('enrollment_id', $enrollmentId)
            ->value('max_attempts') ?: 2;
            
        return response()->json([
            'attempts' => $attempts,
            'max_attempts' => $maxAttempts
        ]);
    } catch (\Exception $e) {
        return response()->json(['attempts' => 0, 'max_attempts' => 2]);
    }
});

Route::get('/final-exam/result/{enrollmentId}', function ($enrollmentId) {
    try {
        \Log::info("API: Getting final exam result", ['enrollment_id' => $enrollmentId]);
        
        // Get the latest final exam result for this enrollment
        $result = \DB::table('final_exam_results')
            ->where('enrollment_id', $enrollmentId)
            ->orderBy('created_at', 'desc')
            ->first();
        
        \Log::info("API: Result found", ['has_result' => $result ? 'yes' : 'no']);
            
        if (!$result) {
            return response()->json(['result' => null]);
        }
        
        // Use actual column names from your table (score, passed, not final_exam_score, is_passing)
        $passed = $result->passed ?? false;
        $score = $result->score ?? 0;
        
        return response()->json([
            'result' => [
                'id' => $result->id,
                'score' => $score,
                'passed' => $passed,
                'status' => $result->status ?? 'pending',
                'created_at' => $result->created_at,
                'overall_score' => $result->overall_score ?? $score,
                'grade_letter' => $result->grade_letter ?? null
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error("API: Final exam result error", [
            'enrollment_id' => $enrollmentId,
            'error' => $e->getMessage()
        ]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::post('/final-exam/submit', function () {
    try {
        $data = request()->all();
        
        \Log::info("Fallback: Final exam submit", ['enrollment_id' => $data['enrollment_id'], 'passed' => $data['passed']]);
        
        // Save result
        \DB::table('final_exam_results')->insert([
            'enrollment_id' => $data['enrollment_id'],
            'score' => $data['score'],
            'passed' => $data['passed'],
            'attempt' => $data['attempt'],
            'answers' => json_encode($data['answers']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $resultId = \DB::getPdo()->lastInsertId();
        
        // Update enrollment with final exam flags
        \DB::table('user_course_enrollments')
            ->where('id', $data['enrollment_id'])
            ->update([
                'final_exam_completed' => true,
                'final_exam_result_id' => $resultId,
                'updated_at' => now()
            ]);
        
        // If passed, update progress to 100%
        if ($data['passed']) {
            $enrollment = \App\Models\UserCourseEnrollment::find($data['enrollment_id']);
            if ($enrollment) {
                $progressController = new \App\Http\Controllers\ProgressController();
                $progressController->updateEnrollmentProgressPublic($enrollment);
                
                \Log::info("Fallback: Progress updated", [
                    'enrollment_id' => $data['enrollment_id'],
                    'progress' => $enrollment->fresh()->progress_percentage
                ]);
            }
        }
        
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        \Log::error("Fallback: Submit error", ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Admin Final Exam Attempts Management
Route::get('/admin/final-exam-attempts', function () {
    try {
        $search = request('search', '');
        $status = request('status', '');
        
        // Get enrollments with their course table info first
        $enrollments = \DB::table('user_course_enrollments as uce')
            ->join('users as u', 'uce.user_id', '=', 'u.id')
            ->leftJoin('final_exam_results as fer', 'uce.id', '=', 'fer.enrollment_id')
            ->leftJoin('final_exam_attempt_limits as feal', 'uce.id', '=', 'feal.enrollment_id')
            ->select([
                'uce.id as enrollment_id',
                'uce.course_id',
                'uce.course_table',
                \DB::raw('CONCAT(COALESCE(u.first_name, ""), " ", COALESCE(u.last_name, "")) as user_name'),
                'u.email as user_email',
                \DB::raw('COALESCE(COUNT(fer.id), 0) as attempts_used'),
                \DB::raw('COALESCE(feal.max_attempts, 2) as max_attempts'),
                \DB::raw('MAX(fer.score) as best_score'),
                \DB::raw('MAX(fer.passed) as passed'),
                \DB::raw('MAX(fer.created_at) as last_attempt')
            ])
            ->groupBy('uce.id', 'uce.course_id', 'uce.course_table', 'u.first_name', 'u.last_name', 'u.email', 'feal.max_attempts');
            
        if ($search) {
            $enrollments->where(function($q) use ($search) {
                $q->where('u.first_name', 'LIKE', "%{$search}%")
                  ->orWhere('u.last_name', 'LIKE', "%{$search}%")
                  ->orWhere('u.email', 'LIKE', "%{$search}%");
            });
        }
        
        $results = $enrollments->get();
        
        // Now get course titles from appropriate tables
        $finalResults = $results->map(function($item) {
            $courseTable = $item->course_table ?: 'courses';
            $course = \DB::table($courseTable)->where('id', $item->course_id)->first();
            $item->course_title = $course ? $course->title : 'Unknown Course';
            return $item;
        });
        
        // Filter by status if specified
        if ($status) {
            $finalResults = $finalResults->filter(function($item) use ($status) {
                switch ($status) {
                    case 'passed':
                        return $item->passed;
                    case 'failed':
                        return $item->attempts_used > 0 && !$item->passed;
                    case 'exhausted':
                        return $item->attempts_used >= $item->max_attempts && !$item->passed;
                    default:
                        return true;
                }
            });
        }
        
        return response()->json($finalResults->values());
    } catch (\Exception $e) {
        \Log::error('Final Exam Attempts API Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::post('/admin/final-exam-attempts/increase', function () {
    try {
        $data = request()->all();
        
        // Create attempt limits table if not exists
        \DB::statement('CREATE TABLE IF NOT EXISTS final_exam_attempt_limits (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            enrollment_id BIGINT UNSIGNED NOT NULL UNIQUE,
            max_attempts INT NOT NULL DEFAULT 2,
            reason TEXT,
            granted_by BIGINT UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (enrollment_id) REFERENCES user_course_enrollments(id) ON DELETE CASCADE
        )');
        
        // Get current max attempts
        $current = \DB::table('final_exam_attempt_limits')
            ->where('enrollment_id', $data['enrollment_id'])
            ->first();
            
        $newMaxAttempts = ($current ? $current->max_attempts : 2) + $data['additional_attempts'];
        
        // Insert or update
        \DB::table('final_exam_attempt_limits')->updateOrInsert(
            ['enrollment_id' => $data['enrollment_id']],
            [
                'max_attempts' => $newMaxAttempts,
                'reason' => $data['reason'],
                'granted_by' => auth()->id(),
                'updated_at' => now()
            ]
        );
        
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/admin/final-exam-attempts/{enrollmentId}/details', function ($enrollmentId) {
    try {
        // Get user and course info with dynamic course table
        $enrollment = \DB::table('user_course_enrollments as uce')
            ->join('users as u', 'uce.user_id', '=', 'u.id')
            ->where('uce.id', $enrollmentId)
            ->select([
                'uce.course_id',
                'uce.course_table',
                \DB::raw('CONCAT(COALESCE(u.first_name, ""), " ", COALESCE(u.last_name, "")) as name'),
                'u.email'
            ])
            ->first();
            
        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
        
        // Get course title from appropriate table
        $courseTable = $enrollment->course_table ?: 'courses';
        $course = \DB::table($courseTable)->where('id', $enrollment->course_id)->first();
        $courseTitle = $course ? $course->title : 'Unknown Course';
            
        // Get max attempts
        $maxAttempts = \DB::table('final_exam_attempt_limits')
            ->where('enrollment_id', $enrollmentId)
            ->value('max_attempts') ?: 2;
            
        // Get all attempts
        $attempts = \DB::table('final_exam_results')
            ->where('enrollment_id', $enrollmentId)
            ->orderBy('attempt')
            ->get();
            
        $bestScore = $attempts->max('score');
        
        return response()->json([
            'user' => ['name' => $enrollment->name, 'email' => $enrollment->email],
            'course' => ['title' => $courseTitle],
            'max_attempts' => $maxAttempts,
            'attempts' => $attempts,
            'best_score' => $bestScore
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/enrollments/{enrollmentId}/progress', function ($enrollmentId) {
    try {
        \Log::info('=== COURSE PLAYER DEBUG START ===');
        \Log::info("Enrollment ID: {$enrollmentId}");

        $enrollment = \App\Models\UserCourseEnrollment::with('course')->findOrFail($enrollmentId);
        \Log::info("Enrollment found - User ID: {$enrollment->user_id}, Course ID: {$enrollment->course_id}, Course Table: {$enrollment->course_table}");

        if (! $enrollment->course) {
            \Log::error("❌ Course relationship returned NULL for course_id: {$enrollment->course_id}");

            // Check if course exists in florida_courses table
            $courseExists = DB::table('florida_courses')->where('id', $enrollment->course_id)->exists();
            \Log::info('Course exists in florida_courses table: '.($courseExists ? 'YES' : 'NO'));

            return response()->json(['error' => 'Course not found', 'chapters' => [], 'progress' => [], 'enrollment' => $enrollment]);
        }

        \Log::info("✓ Course loaded: {$enrollment->course->title}");

        // Check total chapters in database with course_table filter
        $totalChapters = \App\Models\Chapter::where('course_id', $enrollment->course_id)
            ->where('course_table', $enrollment->course_table)
            ->count();
        \Log::info("Total chapters in DB for course_id {$enrollment->course_id} and course_table {$enrollment->course_table}: {$totalChapters}");

        $chapters = \App\Models\Chapter::where('course_id', $enrollment->course_id)
            ->where('course_table', $enrollment->course_table)
            ->where('is_active', true)
            ->orderBy('order_index', 'asc')
            ->get();

        \Log::info("✓ Active chapters found: {$chapters->count()}");

        if ($chapters->isEmpty()) {
            \Log::error('❌ NO CHAPTERS FOUND - Checking database...');
            $allChapters = DB::table('chapters')
                ->where('course_id', $enrollment->course_id)
                ->where('course_table', $enrollment->course_table)
                ->get();
            \Log::info('Raw DB query result: '.json_encode($allChapters));
        } else {
            \Log::info('Chapter IDs: '.$chapters->pluck('id')->implode(', '));
        }

        $progress = \App\Models\ChapterProgress::where('enrollment_id', $enrollmentId)->get();
        \Log::info("Progress records: {$progress->count()}");
        
        // Add is_completed flag to each chapter
        $chaptersWithProgress = $chapters->map(function ($chapter) use ($progress) {
            $chapterProgress = $progress->firstWhere('chapter_id', $chapter->id);
            $chapter->is_completed = $chapterProgress && $chapterProgress->is_completed ? true : false;
            $chapter->progress_percentage = $chapterProgress ? $chapterProgress->progress_percentage : 0;
            return $chapter;
        });
        
        \Log::info('=== COURSE PLAYER DEBUG END ===');

        return response()->json([
            'enrollment' => $enrollment,
            'chapters' => $chaptersWithProgress,
            'progress' => $progress,
            'course' => $enrollment->course,
        ]);
    } catch (\Exception $e) {
        \Log::error('❌ Enrollment progress error: '.$e->getMessage());
        \Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json(['error' => $e->getMessage(), 'chapters' => [], 'progress' => [], 'enrollment' => null], 500);
    }
});

Route::post('/timer/start', function (\Illuminate\Http\Request $request) {
    try {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        return response()->json(['success' => true, 'started_at' => now()]);
    } catch (\Exception $e) {
        \Log::error('Timer start error: '.$e->getMessage());

        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Get reviews for a specific course
Route::get('/courses/{courseId}/reviews', function ($courseId) {
    try {
        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->where('reviews.course_name', 'LIKE', '%' . $courseId . '%')
            ->orWhereExists(function ($query) use ($courseId) {
                $query->select(DB::raw(1))
                    ->from('user_course_enrollments')
                    ->whereColumn('user_course_enrollments.id', 'reviews.enrollment_id')
                    ->where('user_course_enrollments.course_id', $courseId);
            })
            ->select(
                'reviews.id',
                'reviews.rating',
                'reviews.feedback',
                'reviews.course_name',
                'reviews.created_at',
                'users.first_name as user_name'
            )
            ->orderBy('reviews.created_at', 'desc')
            ->get();

        $averageRating = $reviews->avg('rating');
        $totalReviews = $reviews->count();

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'rating_breakdown' => [
                '5_star' => $reviews->where('rating', 5)->count(),
                '4_star' => $reviews->where('rating', 4)->count(),
                '3_star' => $reviews->where('rating', 3)->count(),
                '2_star' => $reviews->where('rating', 2)->count(),
                '1_star' => $reviews->where('rating', 1)->count(),
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Course reviews API error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load reviews'], 500);
    }
});

// Get all reviews (with pagination)
Route::get('/reviews', function () {
    try {
        $page = request('page', 1);
        $limit = request('limit', 20);
        $offset = ($page - 1) * $limit;

        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->select('reviews.id',
                'reviews.rating',
                'reviews.feedback',
                'reviews.course_name',
                'reviews.created_at',
                'users.first_name as user_name')
            ->orderBy('reviews.created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $total = DB::table('reviews')->count();

        return response()->json([
            'reviews' => $reviews,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'has_more' => ($offset + $limit) < $total,
        ]);
    } catch (\Exception $e) {
        \Log::error('Reviews API error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load reviews'], 500);
    }
});
// Update the public courses endpoint to include review stats
Route::get('/public/courses', function () {
    try {
        $floridaCourses = DB::table('florida_courses')
            ->where('is_active', true)
            ->select('id', 'title', 'description', 'state_code', 'duration', 'price','course_details')
            ->get();

        $regularCourses = DB::table('courses')
            ->where('is_active', true)
            ->select('id', 'title', 'description', 'state', 'duration', 'price','course_details')
            ->get();

        $allCourses = collect();

        foreach ($floridaCourses as $course) {
            // Get review stats for this course
            $reviews = DB::table('reviews')
                ->join('user_course_enrollments', 'reviews.enrollment_id', '=', 'user_course_enrollments.id')
                ->where('user_course_enrollments.course_id', $course->id)
                ->select('reviews.rating')
                ->get();

            $allCourses->push([
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description ?? '',
                'course_details' => $course->course_details ?? '',
                'state_code' => $course->state_code ?? 'FL',
                'price' => (float) $course->price,
                'duration' => (int) $course->duration,
                'average_rating' => $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : null,
                'total_reviews' => $reviews->count(),
            ]);
        }

        foreach ($regularCourses as $course) {
            // Get review stats for this course
            $reviews = DB::table('reviews')
                ->join('user_course_enrollments', 'reviews.enrollment_id', '=', 'user_course_enrollments.id')
                ->where('user_course_enrollments.course_id', $course->id)
                ->select('reviews.rating')
                ->get();

            $allCourses->push([
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description ?? '',
                'state_code' => $course->state ?? 'FL',
                'price' => (float) $course->price,
                'duration' => (int) $course->duration,
                'average_rating' => $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : null,
                'total_reviews' => $reviews->count(),
            ]);
        }

        return response()->json($allCourses);
    } catch (\Exception $e) {
        \Log::error('Public courses API error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load courses'], 500);
    }
});

// Court API Routes
Route::get('/courts/states', function () {
    return response()->json(
        \App\Models\Court::distinct()->pluck('state')->sort()->values()
    );
});

Route::get('/courts/by-state/{state}', function ($state) {
    return response()->json(
        \App\Models\Court::where('state', $state)
            ->distinct()
            ->pluck('county')
            ->sort()
            ->values()
    );
});

Route::get('/courts/by-county/{state}/{county}', function ($state, $county) {
    $page = request('page', 1);
    $limit = 100;
    $offset = ($page - 1) * $limit;

    $courts = \App\Models\Court::where('state', $state)
        ->where('county', $county)
        ->orderBy('court')
        ->offset($offset)
        ->limit($limit)
        ->pluck('court');

    $total = \App\Models\Court::where('state', $state)
        ->where('county', $county)
        ->count();

    return response()->json([
        'courts' => $courts,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'has_more' => ($offset + $limit) < $total,
    ]);
});

// Public FAQs API
Route::get('/public/faqs', function () {
    $faqs = \App\Models\Faq::where('is_active', true)
        ->orderBy('category')
        ->orderBy('order')
        ->get()
        ->groupBy('category')
        ->map(function ($items) {
            return $items->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'order' => $faq->order,
                ];
            })->values();
        });

    return response()->json($faqs);
});

// Include new modules API routes
require __DIR__.'/new-modules-api.php';




// State Integration Callbacks (no auth - external systems)
Route::post('/ctsi/result', [App\Http\Controllers\Api\CtsiCallbackController::class, 'handle']);
Route::post('/ntsa/result', [App\Http\Controllers\Api\NtsaCallbackController::class, 'handle']);
Route::post('/ccs/result', [App\Http\Controllers\Api\CcsCallbackController::class, 'handle']);
    // Missouri Form 4444 API Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/missouri/forms/all', [App\Http\Controllers\MissouriController::class, 'getAllForms']);
        Route::get('/missouri/expiring-forms', [App\Http\Controllers\MissouriController::class, 'getExpiringForms']);
    });